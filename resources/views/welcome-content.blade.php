<!-- Hero Section -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @auth
        <!-- Show different hero for authenticated users -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-lg text-gray-600">
                Shorten URLs and track their performance.
            </p>
        </div>
    @else
        <!-- Show marketing hero for guests -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Shorten Your URLs,<br>
                <span class="text-blue-600">Track Your Success</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Create short, memorable links and get detailed analytics on how they perform.
                Perfect for social media, marketing campaigns, and more.
            </p>
        </div>
    @endauth

    <!-- URL Shortening Form -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">URL Shortened Successfully!</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Short URL: <a href="{{ session('success.data.short_url') }}" class="font-semibold underline"
                                    target="_blank">{{ session('success.data.short_url') }}</a></p>

                            @if(session('success.data.is_password_protected'))
                                <p class="mt-1 text-xs">🔒 This URL is password protected</p>
                            @endif

                            @if(session('success.data.expires_at'))
                                <p class="mt-1 text-xs">⏰ Expires:
                                    {{ \Carbon\Carbon::parse(session('success.data.expires_at'))->format('M j, Y g:i A') }}
                                </p>
                            @endif

                            <div class="mt-2 space-x-2">
                                <button onclick="copyToClipboard('{{ session('success.data.short_url') }}')"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">
                                    Copy Link
                                </button>
                                <a href="{{ url('info/' . session('success.data.short_code')) }}"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                                    View Analytics
                                </a>
                                @auth
                                    <a href="{{ route('dashboard') }}"
                                        class="bg-purple-600 text-white px-3 py-1 rounded text-xs hover:bg-purple-700">
                                        My URLs
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('url.shorten') }}" class="space-y-6">
            @csrf

            <!-- Main URL Input -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="original_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter your long URL
                    </label>
                    <input type="url" name="original_url" id="original_url" value="{{ old('original_url') }}"
                        placeholder="https://example.com/very-long-url"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div class="sm:flex-shrink-0 sm:self-end">
                    <button type="submit"
                        class="w-full sm:w-auto bg-blue-600 text-white px-8 py-3 rounded-md font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Shorten URL
                    </button>
                </div>
            </div>

            <!-- Advanced Options (Collapsible) -->
            <div class="border-t pt-6">
                <button type="button" onclick="toggleAdvancedOptions()"
                    class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                    <span>Advanced Options</span>
                    <svg id="advanced-chevron" class="ml-2 h-4 w-4 transform transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="advanced-options" class="hidden mt-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="custom_code" class="block text-sm font-medium text-gray-700 mb-1">
                                Custom Short Code (optional)
                            </label>
                            <input type="text" name="custom_code" id="custom_code" value="{{ old('custom_code') }}"
                                placeholder="my-custom-code"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">3-10 characters, letters and numbers only</p>
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Title (optional)
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                placeholder="My Campaign Link"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description (optional)
                        </label>
                        <textarea name="description" id="description" rows="2"
                            placeholder="Brief description of this link"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <!-- Security & Expiration Options -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Security & Expiration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Password Protection (optional)
                                </label>
                                <input type="password" name="password" id="password" placeholder="Enter password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Visitors will need this password to access the URL
                                </p>
                            </div>

                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
                                    Expiration Date (optional)
                                </label>
                                <input type="datetime-local" name="expires_at" id="expires_at"
                                    value="{{ old('expires_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">URL will stop working after this date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @auth
            <!-- Bulk shortening option for authenticated users -->
            <div class="border-t pt-6 mt-6">
                <div class="text-sm text-gray-600 mb-4">
                    <strong>Tip:</strong> As a logged-in user, you can also use
                    <a href="{{ route('dashboard') }}" class="text-blue-600 underline">bulk URL shortening</a>
                    and track all your links in your dashboard.
                </div>
            </div>
        @endauth
    </div>

    <!-- Statistics Section -->
    @if(isset($stats))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_urls']) }}</div>
                <div class="text-gray-600">URLs Shortened</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-600">{{ number_format($stats['total_clicks']) }}</div>
                <div class="text-gray-600">Total Clicks</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-purple-600">
                    {{ number_format($stats['total_clicks'] > 0 ? $stats['total_clicks'] / max($stats['total_urls'], 1) : 0, 1) }}
                </div>
                <div class="text-gray-600">Avg. Clicks per URL</div>
            </div>
        </div>
    @endif

    @guest
        <!-- Features Section (only show for guests) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full p-3 w-12 h-12 mx-auto mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Fast & Reliable</h3>
                <p class="text-gray-600">Lightning-fast redirects with 99.9% uptime guarantee.</p>
            </div>

            <div class="text-center">
                <div class="bg-green-100 rounded-full p-3 w-12 h-12 mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Detailed Analytics</h3>
                <p class="text-gray-600">Track clicks, locations, devices, and more with comprehensive analytics.</p>
            </div>

            <div class="text-center">
                <div class="bg-purple-100 rounded-full p-3 w-12 h-12 mx-auto mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Secure & Private</h3>
                <p class="text-gray-600">Your data is encrypted and protected. Optional password protection available.</p>
            </div>
        </div>

        <!-- Call to Action for guests -->
        <div class="bg-blue-50 rounded-lg p-8 text-center mb-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Get More Features with an Account</h3>
            <p class="text-gray-600 mb-6">
                Sign up for free to access URL management, bulk shortening, detailed analytics, and more!
            </p>
            <div class="space-x-4">
                <a href="{{ route('register') }}"
                    class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700">
                    Create Free Account
                </a>
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">
                    Already have an account? Sign in
                </a>
            </div>
        </div>
    @endguest
</div>

@guest
    <!-- Footer (only for guests, authenticated users have it in the app layout) -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} URL Shortener. Built with Laravel.</p>
            </div>
        </div>
    </footer>
@endguest

<script>
    function toggleAdvancedOptions() {
        const options = document.getElementById('advanced-options');
        const chevron = document.getElementById('advanced-chevron');

        options.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            // You could add a toast notification here
            alert('Link copied to clipboard!');
        });
    }
</script>