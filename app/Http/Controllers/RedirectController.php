<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Url;
use App\Services\UrlShortenerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService
    ) {}

    /**
     * Handle short URL redirection
     */
    public function redirect(Request $request, string $shortCode): RedirectResponse|View|Response
    {
        // Find the URL by short code
        $url = $this->urlShortenerService->getUrlByShortCode($shortCode);

        if (!$url) {
            return $this->handleNotFound($shortCode);
        }

        // Check if URL is accessible (not expired, active)
        if (!$this->urlShortenerService->isUrlAccessible($url)) {
            return $this->handleExpiredOrInactive($url);
        }

        // Check if URL is password protected
        if ($url->isPasswordProtected()) {
            return $this->handlePasswordProtected($request, $url);
        }

        // Track the click
        $this->trackClick($request, $url);

        // Redirect to original URL
        return redirect()->away($url->original_url);
    }

    /**
     * Handle password protected URLs
     */
    private function handlePasswordProtected(Request $request, Url $url): RedirectResponse|View
    {
        // Check if password was provided
        if ($request->has('password')) {
            $password = $request->input('password');
            
            if ($this->urlShortenerService->validateUrlPassword($url, $password)) {
                // Track the click
                $this->trackClick($request, $url);
                return redirect()->away($url->original_url);
            } else {
                return view('password-form', [
                    'url' => $url,
                    'error' => 'Invalid password. Please try again.'
                ]);
            }
        }

        // Show password form
        return view('password-form', ['url' => $url]);
    }

    /**
     * Handle not found URLs
     */
    private function handleNotFound(string $shortCode): Response
    {
        return response()->view('errors.url-not-found', [
            'shortCode' => $shortCode
        ], 404);
    }

    /**
     * Handle expired or inactive URLs
     */
    private function handleExpiredOrInactive(Url $url): Response
    {
        $reason = $url->isExpired() ? 'expired' : 'inactive';
        
        return response()->view('errors.url-unavailable', [
            'url' => $url,
            'reason' => $reason
        ], 410); // Gone
    }

    /**
     * Track click analytics
     */
    private function trackClick(Request $request, Url $url): void
    {
        try {
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();
            $referer = $request->headers->get('referer');

            // Parse user agent information
            $deviceInfo = Click::parseUserAgent($userAgent);

            // Get geographic information (basic implementation)
            $geoInfo = $this->getGeographicInfo($ipAddress);

            Click::create([
                'url_id' => $url->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'country' => $geoInfo['country'] ?? null,
                'city' => $geoInfo['city'] ?? null,
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'device_type' => $deviceInfo['deviceType'],
                'clicked_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the redirect
            \Log::error('Failed to track click: ' . $e->getMessage(), [
                'url_id' => $url->id,
                'ip' => $request->ip()
            ]);
        }
    }

    /**
     * Get geographic information from IP address
     * This is a basic implementation - in production, you'd use a service like GeoIP
     */
    private function getGeographicInfo(string $ipAddress): array
    {
        // For localhost/private IPs, return default
        if (in_array($ipAddress, ['127.0.0.1', '::1']) || 
            preg_match('/^192\.168\./', $ipAddress) ||
            preg_match('/^10\./', $ipAddress) ||
            preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress)) {
            return [
                'country' => 'US',
                'city' => 'Local'
            ];
        }

        // Basic implementation - you can integrate with services like:
        // - GeoLite2 database
        // - IP-API.com
        // - ipapi.co
        // - MaxMind GeoIP2
        
        try {
            // Example using a free IP geolocation API (ip-api.com)
            // Note: This should be moved to a queue for production use
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'user_agent' => 'URL Shortener 1.0'
                ]
            ]);
            
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,countryCode,city", false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['countryCode'] ?? null,
                        'city' => $data['city'] ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return [
            'country' => null,
            'city' => null
        ];
    }

    /**
     * Preview URL information without redirecting
     */
    public function preview(string $shortCode): View|Response
    {
        $url = $this->urlShortenerService->getUrlByShortCode($shortCode);

        if (!$url) {
            return $this->handleNotFound($shortCode);
        }

        return view('url-preview', [
            'url' => $url
        ]);
    }
}
