<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrlController extends Controller
{
    /**
     * Display a listing of the user's URLs.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->urls()->withCount('clicks');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_url', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('short_code', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'expired':
                    $query->where('expires_at', '<=', now());
                    break;
            }
        }

        $urls = $query->latest()->paginate(10);
        $urls->appends($request->query());

        return view('dashboard', compact('urls'));
    }

    /**
     * Show the form for creating a new URL.
     */
    public function create()
    {
        return redirect()->route('home');
    }

    /**
     * Store a newly created URL in storage.
     */
    public function store(Request $request)
    {
        // This is handled by HomeController::shorten
        return redirect()->route('home');
    }

    /**
     * Display the specified URL.
     */
    public function show(Url $url)
    {
        // Ensure user can only view their own URLs
        if ($url->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to URL.');
        }

        return redirect()->route('url.info', $url->short_code);
    }

    /**
     * Show the form for editing the specified URL.
     */
    public function edit(Url $url)
    {
        // Ensure user can only edit their own URLs
        if ($url->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to URL.');
        }

        return view('urls.edit', compact('url'));
    }

    /**
     * Update the specified URL in storage.
     */
    public function update(Request $request, Url $url)
    {
        // Ensure user can only update their own URLs
        if ($url->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to URL.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $url->update($request->only(['title', 'description', 'is_active', 'expires_at']));

        return response()->json([
            'success' => true,
            'message' => 'URL updated successfully.',
            'url' => $url
        ]);
    }

    /**
     * Update the status of the specified URL.
     */
    public function updateStatus(Request $request, Url $url)
    {
        // Ensure user can only update their own URLs
        if ($url->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $url->update(['is_active' => $request->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'URL status updated successfully.',
            'is_active' => $url->is_active
        ]);
    }

    /**
     * Remove the specified URL from storage.
     */
    public function destroy(Url $url)
    {
        // Ensure user can only delete their own URLs
        if ($url->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete associated clicks first (optional, could use cascade)
        $url->clicks()->delete();

        // Delete the URL
        $url->delete();

        return response()->json([
            'success' => true,
            'message' => 'URL deleted successfully.'
        ]);
    }

    /**
     * Bulk operations on URLs
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'url_ids' => 'required|array',
            'url_ids.*' => 'exists:urls,id'
        ]);

        $urls = Url::whereIn('id', $request->url_ids)
            ->where('user_id', Auth::id())
            ->get();

        if ($urls->isEmpty()) {
            return response()->json(['error' => 'No URLs found or unauthorized'], 404);
        }

        $affected = 0;

        switch ($request->action) {
            case 'activate':
                $affected = $urls->each(function ($url) {
                    $url->update(['is_active' => true]);
                })->count();
                break;

            case 'deactivate':
                $affected = $urls->each(function ($url) {
                    $url->update(['is_active' => false]);
                })->count();
                break;

            case 'delete':
                $affected = $urls->count();
                foreach ($urls as $url) {
                    $url->clicks()->delete();
                    $url->delete();
                }
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully {$request->action}d {$affected} URL(s).",
            'affected' => $affected
        ]);
    }

    /**
     * Get analytics data for a URL
     */
    public function analytics(Url $url)
    {
        // Ensure user can only view analytics for their own URLs
        if ($url->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $analytics = [
            'total_clicks' => $url->clicks()->count(),
            'unique_clicks' => $url->clicks()->distinct('ip_address')->count(),
            'clicks_today' => $url->clicks()->whereDate('clicked_at', today())->count(),
            'clicks_this_week' => $url->clicks()->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'clicks_this_month' => $url->clicks()->whereMonth('clicked_at', now()->month)->count(),
            'top_countries' => $url->clicks()
                ->selectRaw('country, COUNT(*) as count')
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
            'top_browsers' => $url->clicks()
                ->selectRaw('browser, COUNT(*) as count')
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
            'daily_clicks' => $url->clicks()
                ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                ->whereBetween('clicked_at', [now()->subDays(30), now()])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json($analytics);
    }
}