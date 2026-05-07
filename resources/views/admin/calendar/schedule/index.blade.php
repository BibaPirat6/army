@extends('layouts.main')

@section('header-title', 'График — ' . $employee->person->фамилия)

@section('content')
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold">
                <a href="{{ route('employees.show', $employee->id) }}" class="hover:text-indigo-600">
                    {{ $employee->person->фамилия }} {{ $employee->person->имя }}
                </a>
            </h2>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('calendar.schedule.generate', $employee->id) }}" method="POST" class="flex gap-2 items-center text-xs">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <select name="template" class="border rounded px-2 py-1">
                    <option value="5/2">5/2 (Пн–Пт)</option>
                    <option value="2/2">2/2</option>
                    <option value="6/1">6/1</option>
                    <option value="1/3">1/3</option>
                    <option value="сменный">Сменный (2/1)</option>
                    <option value="неделя_2/2_5/2">Чередование 2/2 и 5/2</option>
                </select>
                <input type="time" name="work_start" value="09:00" class="border rounded px-1 py-1 w-20">
                <input type="time" name="work_end" value="18:00" class="border rounded px-1 py-1 w-20">
                <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">Сформировать</button>
            </form>
        </div>
    </div>

    <div class="flex items-center gap-3 mb-4 text-sm">
        <a href="?month={{ $month-1 < 1 ? 12 : $month-1 }}&year={{ $month-1 < 1 ? $year-1 : $year }}" class="text-gray-500 hover:text-indigo-600">← Пред</a>
        <span class="font-medium">{{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</span>
        <a href="?month={{ $month+1 > 12 ? 1 : $month+1 }}&year={{ $month+1 > 12 ? $year+1 : $year }}" class="text-gray-500 hover:text-indigo-600">След →</a>
    </div>

    <div class="overflow-auto bg-white rounded shadow border" style="max-height:75vh">
        <table class="min-w-max text-xs border-collapse">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-gray-50 px-2 py-1 border-r border-b min-w-[60px]">День</th>
                    <th class="px-2 py-1 border-b min-w-[80px]">Статус</th>
                    <th class="px-2 py-1 border-b min-w-[140px]">Часы работы</th>
                    <th class="px-2 py-1 border-b min-w-[300px]">Задачи</th>
                    <th class="px-2 py-1 border-b min-w-[80px]">Загрузка</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedule['plan'] as $date => $day)
                    @php $wd = $day['work_day']; @endphp
                    <tr class="{{ $wd && $wd->type === 'выходной' ? 'bg-gray-50' : '' }}">
                        {{-- День --}}
                        <td class="sticky left-0 bg-white px-2 py-1 border-r border-b font-medium">
                            {{ Carbon\Carbon::parse($date)->format('d') }}
                            <span class="text-gray-400 ml-1">{{ Carbon\Carbon::parse($date)->translatedFormat('D') }}</span>
                        </td>

                        {{-- Статус --}}
                        <td class="px-2 py-1 border-b">
                            <select onchange="updateWorkDay('{{ $wd->id ?? '' }}', '{{ $date }}', '{{ $employee->id }}', this.value)"
                                class="text-xs border rounded px-1 py-0.5 {{ $wd && $wd->type === 'рабочий_день' ? 'text-emerald-600' : 'text-gray-400' }}">
                                <option value="рабочий_день" {{ $wd && $wd->type === 'рабочий_день' ? 'selected' : '' }}>Рабочий</option>
                                <option value="выходной" {{ $wd && $wd->type === 'выходной' ? 'selected' : '' }}>Выходной</option>
                            </select>
                        </td>

                        {{-- Часы работы --}}
                        <td class="px-2 py-1 border-b">
                            @if($wd && $wd->type === 'рабочий_день' && $wd->work_start)
                                <span class="font-medium">{{ \Carbon\Carbon::parse($wd->work_start)->format('H:i') }} — {{ \Carbon\Carbon::parse($wd->work_end)->format('H:i') }}</span>
                                @php
                                    $breaks = $wd->breaks;
                                    if (is_string($breaks)) $breaks = json_decode($breaks, true);
                                @endphp
                                @if(!empty($breaks))
                                    <div class="text-gray-400">
                                        обед:
                                        @foreach($breaks as $b)
                                            {{ \Carbon\Carbon::parse($b['start'])->format('H:i') }}–{{ \Carbon\Carbon::parse($b['end'])->format('H:i') }}
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Задачи --}}
                        <td class="px-2 py-1 border-b">
                            @if(!empty($day['tasks']))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($day['tasks'] as $taskId => $minutes)
                                        @php $task = \App\Models\Task::find($taskId); @endphp
                                        <div class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] border"
                                            style="border-color:{{ $task?->color ?? '#ccc' }}; background:{{ $task?->color ?? '#ccc' }}10">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $task?->color }}"></span>
                                            <span>{{ $task?->title ?? '—' }}</span>
                                            <span class="text-gray-400">{{ $minutes }}м</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Загрузка --}}
                        <td class="px-2 py-1 border-b text-center">
                            @if($wd && $wd->type === 'рабочий_день')
                                @php
                                    $assigned = array_sum($day['tasks']);
                                    $total = $wd->total_minutes;
                                    $pct = $total > 0 ? round($assigned / $total * 100) : 0;
                                    $barColor = $pct > 100 ? 'bg-red-500' : ($pct > 75 ? 'bg-amber-500' : 'bg-emerald-500');
                                    $textColor = $pct > 100 ? 'text-red-600' : 'text-gray-500';
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $barColor }}" style="width:{{ min(100, $pct) }}%"></div>
                                </div>
                                <span class="text-[10px] {{ $textColor }} font-medium">{{ $pct }}%</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function updateWorkDay(workDayId, date, employeeId, type) {
    if (workDayId) {
        fetch(`/calendar/schedule/work-day/${workDayId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ type: type, _method: 'PUT' })
        }).then(() => location.reload());
    } else {
        fetch(`/calendar/schedule/work-day/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ employee_id: employeeId, date: date, type: type })
        }).then(() => location.reload());
    }
}
</script>
@endsection