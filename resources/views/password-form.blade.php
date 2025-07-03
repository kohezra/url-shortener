<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Protected URL - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v5a6 6 0 006 6h6a6 6 0 006-6v-5m-12 0V9a6 6 0 0112 0v6m-12 0h12"/>
                </svg>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Password Protected
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    This short URL requires a password to access
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                @if($url->title)
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $url->title }}</h3>
                @endif
                
                @if($url->description)
                    <p class="text-gray-600 mb-4">{{ $url->description }}</p>
                @endif
                
                @if(isset($error))
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-3">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-red-800">{{ $error }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('url.password.submit', ['shortCode' => $url->short_code]) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Password">
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Access URL
                        </button>
                        <a href="{{ route('home') }}" 
                           class="flex-1 bg-gray-300 text-gray-700 text-center px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">
                            Go Back
                        </a>
                    </div>
                </form>
                
                <div class="mt-4 text-xs text-gray-500 text-center">
                    <p>Contact the link owner if you don't know the password.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 