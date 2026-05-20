@extends('layouts.main')

@section('header-title')
    Календарь
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Календарный график задач</h2>
            <button type="button" onclick="openStatsModal()"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition flex items-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Статистика по подразделениям
            </button>
        </div>
        <div id="calendar"></div>
    </div>

    @include('admin.calendar.stats-modal')
@endsection

@push('scripts')
<script>
let calendar = null;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [FullCalendar.dayGridPlugin, FullCalendar.multiMonthPlugin, FullCalendar.interactionPlugin],
        initialView: 'multiMonthYear',
        locale: 'ru',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'multiMonthYear,dayGridMonth'
        },
        buttonText: {
            today: 'Сегодня',
            month: 'Месяц',
            multiMonthYear: 'Год'
        },
        events: '/calendar/events',
        eventClick: function(info) {
            window.location.href = '/calendar/tasks/' + info.event.id;
        }
    });

    calendar.render();
});

window.openStatsModal = function() {
    document.getElementById('statsModal').classList.remove('hidden');
};

window.closeStatsModal = function() {
    document.getElementById('statsModal').classList.add('hidden');
};
</script>
@endpush