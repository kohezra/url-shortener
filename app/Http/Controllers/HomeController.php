<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Services\UrlShortenerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService
    ) {}

    /**
     * Show the homepage
     */
    public function index(): View
    {
        // Get some public statistics for the homepage
        $stats = [
            'total_urls' => Url::count(),
            'total_clicks' => \DB::table('clicks')->count(),
            'recent_urls' => Url::whereNull('user_id')
                ->where('is_active', true)
                ->latest()
                ->limit(5)
                ->get(['short_code', 'title', 'created_at'])
        ];

        return view('welcome', compact('stats'));
    }

    /**
     * Shorten a URL via POST request
     */
    public function shorten(Request $request): JsonResponse|View|RedirectResponse
    {
        try {
            $data = $request->validate([
                'original_url' => 'required|url|max:2048',
                'custom_code' => 'nullable|string|min:3|max:10|alpha_num',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'expires_at' => 'nullable|date|after:now',
                'password' => 'nullable|string|min:4|max:255'
            ]);

            // Add user ID if authenticated
            if (auth()->check()) {
                $data['user_id'] = auth()->id();
            }

            $url = $this->urlShortenerService->shortenUrl($data);

            $responseData = [
                'success' => true,
                'message' => 'URL shortened successfully!',
                'data' => [
                    'id' => $url->id,
                    'original_url' => $url->original_url,
                    'short_code' => $url->short_code,
                    'short_url' => url($url->short_code),
                    'title' => $url->title,
                    'description' => $url->description,
                    'created_at' => $url->created_at->toISOString(),
                    'expires_at' => $url->expires_at?->toISOString(),
                    'is_password_protected' => $url->isPasswordProtected()
                ]
            ];

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json($responseData);
            }

            // Return view with success message for regular form submissions
            return back()->with('success', $responseData);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \Log::error('URL shortening failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $errorMessage = 'Failed to shorten URL. Please try again.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Show URL details and QR code
     */
    public function show(string $shortCode): View
    {
        $url = $this->urlShortenerService->getUrlByShortCode($shortCode);

        if (!$url) {
            abort(404, 'Short URL not found');
        }

        // Check if user owns this URL or if it's public
        if ($url->user_id && (!auth()->check() || auth()->id() !== $url->user_id)) {
            abort(403, 'Access denied');
        }

        // Get basic click statistics
        $stats = [
            'total_clicks' => $url->clicks()->count(),
            'unique_visitors' => $url->clicks()->distinct('ip_address')->count(),
            'clicks_today' => $url->clicks()->whereDate('clicked_at', today())->count(),
            'clicks_this_week' => $url->clicks()->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'top_countries' => $url->clicks()
                ->selectRaw('country, COUNT(*) as count')
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
            'recent_clicks' => $url->clicks()
                ->latest('clicked_at')
                ->limit(10)
                ->get(['country', 'city', 'browser', 'os', 'device_type', 'clicked_at'])
        ];

        return view('url.show', [
            'url' => $url,
            'stats' => $stats,
            'qr_code_url' => $this->generateQrCodeUrl($url->short_code)
        ]);
    }

    /**
     * Generate QR code URL
     */
    private function generateQrCodeUrl(string $shortCode): string
    {
        $shortUrl = url($shortCode);
        // Using QR Server API as a simple solution
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($shortUrl);
    }

    /**
     * Bulk URL shortening (for authenticated users)
     */
    public function bulkShorten(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required for bulk operations'
            ], 401);
        }

        $request->validate([
            'urls' => 'required|array|min:1|max:100',
            'urls.*' => 'required|url|max:2048'
        ]);

        $results = [];
        $errors = [];

        foreach ($request->urls as $index => $originalUrl) {
            try {
                $url = $this->urlShortenerService->shortenUrl([
                    'original_url' => $originalUrl,
                    'user_id' => auth()->id()
                ]);

                $results[] = [
                    'original_url' => $originalUrl,
                    'short_code' => $url->short_code,
                    'short_url' => url($url->short_code),
                    'title' => $url->title
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'original_url' => $originalUrl,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk shortening completed',
            'data' => [
                'results' => $results,
                'errors' => $errors,
                'total_processed' => count($request->urls),
                'successful' => count($results),
                'failed' => count($errors)
            ]
        ]);
    }
}
