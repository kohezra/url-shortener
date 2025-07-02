<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $url->title ?? 'URL Analytics' }} - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900 hover:text-gray-700">
                        URL Shortener
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">Home</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- URL Info Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ $url->title ?? 'URL Analytics' }}
                    </h1>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-20">Short URL:</span>
                            <a href="{{ url($url->short_code) }}" 
                               class="text-blue-600 hover:text-blue-500 font-mono text-sm ml-2" 
                               target="_blank">
                                {{ url($url->short_code) }}
                            </a>
                            <button onclick="copyToClipboard('{{ url($url->short_code) }}')" 
                                    class="ml-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-20">Original:</span>
                            <a href="{{ $url->original_url }}" 
                               class="text-gray-600 hover:text-gray-800 text-sm ml-2 truncate max-w-md" 
                               target="_blank">
                                {{ $url->original_url }}
                            </a>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-20">Created:</span>
                            <span class="text-sm text-gray-600 ml-2">{{ $url->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                    @if($url->description)
                    <p class="mt-3 text-gray-600">{{ $url->description }}</p>
                    @endif
                </div>
                
                <!-- QR Code -->
                <div class="mt-6 lg:mt-0 lg:ml-6 flex-shrink-0">
                    <div class="text-center">
                        <img src="{{ $qr_code_url }}" 
                             alt="QR Code for {{ url($url->short_code) }}"
                             class="mx-auto border border-gray-200 rounded-lg">
                        <p class="text-xs text-gray-500 mt-2">QR Code</p>
                        <a href="{{ $qr_code_url }}" 
                           download="qr-{{ $url->short_code }}.png"
                           class="text-xs text-blue-600 hover:text-blue-500">
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_clicks']) }}</div>
                <div class="text-gray-600 text-sm">Total Clicks</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-3xl font-bold text-green-600">{{ number_format($stats['unique_visitors']) }}</div>
                <div class="text-gray-600 text-sm">Unique Visitors</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-3xl font-bold text-purple-600">{{ number_format($stats['clicks_today']) }}</div>
                <div class="text-gray-600 text-sm">Clicks Today</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['clicks_this_week']) }}</div>
                <div class="text-gray-600 text-sm">This Week</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Countries -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Countries</h3>
                @if($stats['top_countries']->count() > 0)
                    <div class="space-y-3">
                        @foreach($stats['top_countries'] as $country)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $country->country ?? 'Unknown' }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="bg-blue-200 rounded-full h-2 flex-1 max-w-20">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: {{ ($country->count / $stats['top_countries']->first()->count) * 100 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-8 text-right">{{ $country->count }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No geographic data available yet.</p>
                @endif
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                @if($stats['recent_clicks']->count() > 0)
                    <div class="space-y-3">
                        @foreach($stats['recent_clicks'] as $click)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                </div>
                                <div>
                                    <div class="text-gray-900">
                                        {{ $click->country ?? 'Unknown' }}
                                        @if($click->city), {{ $click->city }}@endif
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        {{ $click->browser ?? 'Unknown' }} • {{ $click->os ?? 'Unknown' }} • {{ $click->device_type ?? 'Unknown' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-gray-500 text-xs">
                                {{ $click->clicked_at->diffForHumans() }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No clicks recorded yet.</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="flex flex-wrap gap-3">
                <button onclick="copyToClipboard('{{ url($url->short_code) }}')" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                    Copy Short URL
                </button>
                <a href="{{ route('url.preview', $url->short_code) }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                    Preview Link
                </a>
                <a href="{{ $qr_code_url }}" 
                   download="qr-{{ $url->short_code }}.png"
                   class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                    Download QR Code
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = 'Copied!';
                button.classList.add('bg-green-600');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy to clipboard');
            });
        }
    </script>
</body>
</html> 