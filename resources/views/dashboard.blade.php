<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My URLs') }}
                </h2>
            <a href="{{ route('home') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                Create New URL
            </a>
            </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total URLs</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ auth()->user()->urls()->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Clicks</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ auth()->user()->totalClicks() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Avg. Clicks</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ auth()->user()->averageClicks() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ auth()->user()->clicksThisMonth() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <!-- Search and Filters -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        placeholder="Search URLs, titles, or descriptions..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <select name="status"
                                        class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                                        Filter
                                    </button>
                                </div>
                                @if(request()->hasAny(['search', 'status']))
                                    <div>
                                        <a href="{{ route('dashboard') }}"
                                            class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">
                                            Clear
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                    
                    <!-- URLs Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            @if(isset($urls) && $urls->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL
                                                    Info</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short
                                                    URL</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Clicks</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Created</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($urls ?? [] as $url)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4">
                                                        <div class="max-w-xs">
                                                            @if($url->title)
                                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $url->title }}</div>
                                                            @endif
                                                            <div class="text-sm text-gray-500 truncate" title="{{ $url->original_url }}">{{ $url->original_url }}</div>
                                                            @if($url->description)
                                                                <div class="text-xs text-gray-400 truncate">{{ $url->description }}</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center space-x-2">
                                                            <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ url($url->short_code) }}</code>
                                                            <button onclick="copyToClipboard('{{ url($url->short_code) }}')" 
                                                                    class="text-blue-600 hover:text-blue-800 text-xs">
                                                                Copy
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="text-sm font-medium text-gray-900">{{ $url->clicks_count ?? 0 }}</span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($url->isExpired())
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Expired
                                                            </span>
                                                        @elseif(!$url->is_active)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                Inactive
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @endif
                                                        
                                                        @if($url->isPasswordProtected())
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-1">
                                                                🔒 Protected
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        {{ $url->created_at->format('M j, Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm font-medium space-x-2">
                                                        <a href="{{ url('info/' . $url->short_code) }}" 
                                                           class="text-blue-600 hover:text-blue-900">Analytics</a>
                                                        <a href="{{ url($url->short_code) }}" 
                                                           target="_blank"
                                                           class="text-green-600 hover:text-green-900">Visit</a>
                                                        <button onclick="toggleUrlStatus({{ $url->id }}, {{ $url->is_active ? 'false' : 'true' }})"
                                                                class="text-yellow-600 hover:text-yellow-900">
                                                            {{ $url->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                        <button onclick="deleteUrl({{ $url->id }})"
                                                                class="text-red-600 hover:text-red-900">Delete</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-6 py-12 text-center">
                                                        <div class="text-gray-500">
                                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                            </svg>
                                                            <h3 class="mt-4 text-sm font-medium text-gray-900">No URLs found</h3>
                                                            <p class="mt-2 text-sm text-gray-500">No URLs match your current filters.</p>
                                                            <div class="mt-6">
                                                                <a href="{{ route('home') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                                                                    Create your first URL
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if(isset($urls) && $urls->hasPages())
                                    <div class="mt-6">
                                        {{ $urls->links() }}
                                    </div>
                                @endif
                            @else
                                <!-- Empty State -->
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <h3 class="mt-4 text-lg font-medium text-gray-900">No URLs yet</h3>
                                    <p class="mt-2 text-sm text-gray-500">Get started by creating your first shortened URL.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700">
                                            Create Your First URL
                                        </a>
                                    </div>
                                </div>
                            @endif
                </div>
            </div>
        </div>
    </div>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            alert('URL copied to clipboard!');
        });
    }

    function toggleUrlStatus(urlId, newStatus) {
        if (confirm('Are you sure you want to ' + (newStatus === 'true' ? 'activate' : 'deactivate') + ' this URL?')) {
            // We'll implement this AJAX call later
            fetch(`/urls/${urlId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_active: newStatus === 'true' })
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to update URL status');
                }
            });
        }
    }

    function deleteUrl(urlId) {
        if (confirm('Are you sure you want to delete this URL? This action cannot be undone.')) {
            // We'll implement this AJAX call later
            fetch(`/urls/${urlId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to delete URL');
                }
            });
        }
    }
</script>
</x-app-layout>
