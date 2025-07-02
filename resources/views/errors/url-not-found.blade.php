<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Short URL Not Found - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <h1 class="text-9xl font-bold text-gray-300">404</h1>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Short URL Not Found
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    The short URL <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $shortCode }}</span> doesn't exist or has been removed.
                </p>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-600 font-medium">This link does not exist</span>
                </div>
                
                <p class="text-gray-500">
                    Possible reasons:
                </p>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• The URL was typed incorrectly</li>
                    <li>• The short link has expired</li>
                    <li>• The link was deleted by its creator</li>
                    <li>• The link never existed</li>
                </ul>
            </div>

            <div class="mt-8">
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create a New Short URL
                </a>
            </div>

            <div class="mt-6">
                <p class="text-xs text-gray-400">
                    If you believe this is an error, please contact the person who shared this link with you.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 