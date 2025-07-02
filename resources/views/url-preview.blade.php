<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>URL Preview - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    URL Preview
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    You're about to visit a shortened URL
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                @if($url->title)
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $url->title }}</h3>
                @endif
                
                @if($url->description)
                    <p class="text-gray-600 mb-4">{{ $url->description }}</p>
                @endif
                
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Short URL:</label>
                        <div class="mt-1 text-sm font-mono bg-gray-50 p-2 rounded border">
                            {{ url($url->short_code) }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Destination:</label>
                        <div class="mt-1 text-sm bg-gray-50 p-2 rounded border break-all">
                            {{ $url->original_url }}
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-500">
                        Created: {{ $url->created_at->format('M j, Y g:i A') }}
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <a href="{{ $url->original_url }}" 
                       class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                        Continue to Destination
                    </a>
                    <a href="{{ route('home') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 text-center px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">
                        Go Back
                    </a>
                </div>
            </div>
            
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Always verify the destination URL before clicking links from unknown sources.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 