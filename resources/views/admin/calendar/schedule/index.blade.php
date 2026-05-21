@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto p-4">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-lg font-bold">
                {{ $employee->person->фамилия }} {{ $employee->person->имя }}
            </h1>
            <p class="text-xs text-gray-500">
                {{ $month }}/{{ $year }}
            </p>
        </div>

        <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
           class="px-3 py-1 bg-indigo-600 text-white rounded">
            ⚙ Настроить
        </a>
    </div>

    {{-- NAV --}}
    @php
        $prev = \Carbon\Carbon::create($year, $month)->subMonth();
        $next = \Carbon\Carbon::create($year, $month)->addMonth();
    @endphp

    <div class="flex items-center gap-2 mb-4 text-sm">
        <a href="?month={{ $prev->month }}&year={{ $prev->year }}" class="px-2 py-1 border rounded">←</a>

        <select onchange="go(this.value, {{ $year }})" class="border rounded px-2 py-1">
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" @selected($m==$month)>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select onchange="go({{ $month }}, this.value)" class="border rounded px-2 py-1">
            @foreach(range(now()->year-1, now()->year+2) as $y)
                <option value="{{ $y }}" @selected($y==$year)>{{ $y }}</option>
            @endforeach
        </select>

        <a href="?month={{ $next->month }}&year={{ $next->year }}" class="px-2 py-1 border rounded">→</a>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border rounded overflow-auto">
        <table class="w-full text-xs">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Дата</th>
                    <th>Тип</th>
                    <th>Время</th>
                    <th>Задачи</th>
                    <th>%</th>
                </tr>
            </thead>

            <tbody>
            @foreach($schedule['plan'] as $date => $day)

                @php $wd = $day['work_day']; @endphp

                <tr class="border-b {{ $wd && $wd->type!='рабочий_день' ? 'bg-gray-50' : '' }}">

                    <td class="p-2 font-medium">
                        {{ \Carbon\Carbon::parse($date)->format('d.m') }}
                    </td>

                    <td>
                        {{ $wd?->type === 'рабочий_день' ? 'Рабочий' : 'Выходной' }}
                    </td>

                    <td>
                        @if($wd && $wd->work_start)
                            {{ $wd->work_start }} - {{ $wd->work_end }}
                        @else —
                        @endif
                    </td>

                    <td>
                        @forelse($day['tasks'] as $taskId => $min)
                            <div class="text-[11px]">
                                #{{ $taskId }} — {{ $min }} мин
                            </div>
                        @empty
                            —
                        @endforelse
                    </td>

                    <td class="text-center">
                        @php
                            $total = $wd?->total_minutes ?? 0;
                            $assigned = array_sum($day['tasks']);
                            $pct = $total ? round($assigned/$total*100) : 0;
                        @endphp

                        <div class="w-full bg-gray-200 h-1.5 rounded">
                            <div class="h-1.5 bg-indigo-500 rounded"
                                 style="width:{{ min(100,$pct) }}%"></div>
                        </div>

                        <div class="text-[10px] text-gray-500">
                            {{ $pct }}%
                        </div>
                    </td>

                </tr>

            @endforeach
            </tbody>

        </table>
    </div>
</div>

<script>
function go(month, year) {
    window.location = `?month=${month}&year=${year}`;
}
</script>
@endsection