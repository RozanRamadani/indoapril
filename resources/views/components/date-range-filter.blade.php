@props(['startDate' => date('Y-m-01'), 'endDate' => date('Y-m-d')])

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Filter Periode
    </h3>

    <form method="GET" action="" id="dateRangeForm">
        <!-- Quick Filter Buttons -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Filter:</label>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="setDateRange('today')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Hari Ini
                </button>
                <button type="button" onclick="setDateRange('yesterday')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Kemarin
                </button>
                <button type="button" onclick="setDateRange('this_week')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Minggu Ini
                </button>
                <button type="button" onclick="setDateRange('last_week')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Minggu Lalu
                </button>
                <button type="button" onclick="setDateRange('this_month')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Bulan Ini
                </button>
                <button type="button" onclick="setDateRange('last_month')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Bulan Lalu
                </button>
                <button type="button" onclick="setDateRange('this_year')"
                    class="quick-filter px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 hover:text-indigo-700 rounded-md text-sm font-medium transition">
                    Tahun Ini
                </button>
            </div>
        </div>

        <!-- Custom Date Range -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal:</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal:</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <button type="submit"
                    class="w-full px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function setDateRange(period) {
    const today = new Date();
    let startDate, endDate;

    switch(period) {
        case 'today':
            startDate = endDate = today;
            break;

        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = endDate = yesterday;
            break;

        case 'this_week':
            const firstDayOfWeek = new Date(today);
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Start from Monday
            firstDayOfWeek.setDate(today.getDate() + diff);
            startDate = firstDayOfWeek;
            endDate = today;
            break;

        case 'last_week':
            const lastWeekStart = new Date(today);
            const lastWeekEnd = new Date(today);
            const currentDay = today.getDay();
            const diffToLastMonday = currentDay === 0 ? -13 : 1 - currentDay - 7;
            lastWeekStart.setDate(today.getDate() + diffToLastMonday);
            lastWeekEnd.setDate(lastWeekStart.getDate() + 6);
            startDate = lastWeekStart;
            endDate = lastWeekEnd;
            break;

        case 'this_month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = today;
            break;

        case 'last_month':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;

        case 'this_year':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = today;
            break;
    }

    // Format dates to YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    document.getElementById('start_date').value = formatDate(startDate);
    document.getElementById('end_date').value = formatDate(endDate);

    // Highlight active button
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'hover:bg-indigo-700');
        btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-indigo-100');
    });
    event.target.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-indigo-100');
    event.target.classList.add('bg-indigo-600', 'text-white', 'hover:bg-indigo-700');
}
</script>
