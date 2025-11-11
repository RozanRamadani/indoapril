<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ $title ?? 'IndoApril' }}
        </h1>
        @isset($subtitle)
            <p class="mt-1 text-sm text-gray-600">
                {{ $subtitle }}
            </p>
        @endisset
    </div>
</header>
