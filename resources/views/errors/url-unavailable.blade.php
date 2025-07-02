<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>URL {{ ucfirst($reason) }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <div class="mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                    @if($reason === 'expired')
                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                    @endif
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Link {{ ucfirst($reason) }}
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    This short URL is currently unavailable.
                </p>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            @if($reason === 'expired')
                                This link has expired
                            @else
                                This link has been disabled
                            @endif
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            @if($reason === 'expired')
                                <p>This short URL was set to expire on {{ $url->expires_at->format('M j, Y \a\t g:i A') }} and is no longer accessible.</p>
                            @else
                                <p>The owner of this link has temporarily disabled it. It may become available again in the future.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- URL Information (if available) -->
            @if($url->title || $url->description)
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                @if($url->title)
                    <h4 class="font-medium text-gray-900 mb-2">{{ $url->title }}</h4>
                @endif
                @if($url->description)
                    <p class="text-sm text-gray-600">{{ $url->description }}</p>
                @endif
                <div class="mt-3 text-xs text-gray-500">
                    Created: {{ $url->created_at->format('M j, Y') }}
                    @if($reason === 'expired')
                        | Expired: {{ $url->expires_at->format('M j, Y') }}
                    @endif
                </div>
            </div>
            @endif

            <div class="space-y-4">
                <div class="mt-8">
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create a New Short URL
                    </a>
                </div>

                @if($reason === 'expired')
                <div class="text-sm text-gray-500">
                    <p>Want to create a new short URL for the same destination?</p>
                    <a href="{{ route('home') }}?url={{ urlencode($url->original_url) }}" 
                       class="text-blue-600 hover:text-blue-500 font-medium">
                        Shorten this URL again
                    </a>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <p class="text-xs text-gray-400">
                    @if($reason === 'expired')
                        If you need access to the original URL, please contact the person who shared this link.
                    @else
                        This link may become available again. Please try again later or contact the link owner.
                    @endif
                </p>
            </div>
        </div>
    </div>
</body>
</html> 