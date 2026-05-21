@extends('layouts.main')

@section('header-title', 'График — ' . $employee->person->фамилия)

@section('content')
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">
            <a href="{{ route('employees.show', $employee->id) }}" class="hover:text-indigo-600">
                {{ $employee->person->фамилия }} {{ $employee->person->имя }}
            </a>
        </h2>
        <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
            class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            ⚙️ Настроить
        </a>
    </div>

    {{-- Навигация --}}
    <div class="flex items-center gap-2 mb-4 text-sm">
        @php
            $prev = Carbon\Carbon::create($year, $month, 1)->subMonth();
            $next = Carbon\Carbon::create($year, $month, 1)->addMonth();
        @endphp
        <a href="?month={{ $prev->month }}&year={{ $prev->year }}" 
           class="px-2 py-1 text-gray-500 hover:text-indigo-600 border rounded">←</a>
        
        <select onchange="goToMonth(this.value, {{ $year }})" class="border rounded px-2 py-1 text-sm">
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
        
        <select onchange="goToMonth({{ $month }}, this.value)" class="border rounded px-2 py-1 text-sm">
            @foreach(range(now()->year - 2, now()->year + 2) as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        
        <a href="?month={{ $next->month }}&year={{ $next->year }}" 
           class="px-2 py-1 text-gray-500 hover:text-indigo-600 border rounded">→</a>
    </div>

    {{-- Таблица без изменений --}}
    <div class="overflow-auto bg-white rounded shadow border">
        <table class="min-w-full text-xs border-collapse">
            <thead>
                <tr>
                    <th class="px-2 py-1.5 border-b min-w-[60px]">День</th>
                    <th class="px-2 py-1.5 border-b min-w-[80px]">Статус</th>
                    <th class="px-2 py-1.5 border-b min-w-[140px]">Часы</th>
                    <th class="px-2 py-1.5 border-b min-w-[300px]">Задачи</th>
                    <th class="px-2 py-1.5 border-b min-w-[80px]">Загрузка</th>
                </tr>
            </thead>
            <tbody>
              <tbody>

    @foreach($days as $day)

        @php
            $date = $day['date'];
        @endphp

        <tr class="hover:bg-gray-50">

            <td class="px-2 py-2 border-b font-medium">
                {{ $date->format('d') }}

                <span class="text-gray-400 ml-1">
                    {{ $date->translatedFormat('D') }}
                </span>
            </td>

            <td class="px-2 py-2 border-b text-gray-400">
                —
            </td>

            <td class="px-2 py-2 border-b text-gray-400">
                —
            </td>

            <td class="px-2 py-2 border-b text-gray-400">
                Нет задач
            </td>

            <td class="px-2 py-2 border-b text-gray-400 text-center">
                —
            </td>

        </tr>

    @endforeach

</tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
function goToMonth(month, year) {
    window.location.href = '?month=' + month + '&year=' + year;
}
</script>
@endsection