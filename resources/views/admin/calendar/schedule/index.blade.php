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

    {{-- Навигация по неделям --}}
    <div class="flex items-center gap-4 mb-4 text-sm">
        @php
            $prevWeek = $week - 1;
            $nextWeek = $week + 1;
        @endphp
        <a href="?week={{ $prevWeek }}&year={{ $year }}" class="text-gray-500 hover:text-indigo-600">← Пред. неделя</a>
        <span class="font-semibold text-gray-700">
            {{ $from->translatedFormat('d M') }} – {{ $to->translatedFormat('d M Y') }}
        </span>
        <a href="?week={{ $nextWeek }}&year={{ $year }}" class="text-gray-500 hover:text-indigo-600">След. неделя →</a>
    </div>

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
                @foreach($schedule['plan'] as $date => $day)
                    @php $wd = $day['work_day']; @endphp
                    <tr class="{{ $wd && $wd->type !== 'рабочий_день' ? 'bg-gray-50' : '' }}">
                        <td class="px-2 py-1.5 border-b font-medium">
                            {{ Carbon\Carbon::parse($date)->format('d') }}
                            <span class="text-gray-400 ml-1">{{ Carbon\Carbon::parse($date)->translatedFormat('D') }}</span>
                        </td>
                        <td class="px-2 py-1.5 border-b">
                            <span class="{{ $wd && $wd->type === 'рабочий_день' ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $wd && $wd->type === 'рабочий_день' ? 'Рабочий' : 'Выходной' }}
                            </span>
                        </td>
                        <td class="px-2 py-1.5 border-b">
                            @if($wd && $wd->work_start)
                                {{ Carbon\Carbon::parse($wd->work_start)->format('H:i') }} – {{ Carbon\Carbon::parse($wd->work_end)->format('H:i') }}
                                @php $b = is_string($wd->breaks) ? json_decode($wd->breaks, true) : $wd->breaks; @endphp
                                @if(!empty($b))
                                    <div class="text-gray-400">обед: @foreach($b as $br) {{ Carbon\Carbon::parse($br['start'])->format('H:i') }}–{{ Carbon\Carbon::parse($br['end'])->format('H:i') }} @endforeach</div>
                                @endif
                            @else — @endif
                        </td>
                        <td class="px-2 py-1.5 border-b">
                            @if(!empty($day['tasks']))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($day['tasks'] as $taskId => $minutes)
                                        @php
                                            $task = \App\Models\Task::find($taskId);
                                            $a = \App\Models\TaskAssignment::where('task_id', $taskId)->where('employee_id', $employee->id)->first();
                                        @endphp
                                        <div class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] border"
                                            style="border-color:{{ $task?->color ?? '#ccc' }}; background:{{ $task?->color ?? '#ccc' }}10">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $task?->color }}"></span>
                                            <span>{{ $task?->title ?? '—' }}</span>
                                            <span class="text-gray-400">{{ $minutes }}м</span>
                                            @if($a)
                                                <span class="text-[9px] {{ $a->completed_count>=$a->quota?'text-emerald-500':'text-amber-500' }}">
                                                    {{ $a->completed_count }}/{{ $a->quota }}
                                                </span>
                                                <form action="{{ route('calendar.schedule.complete', $a->id) }}" method="POST" class="inline-flex items-center ml-1">
                                                    @csrf
                                                    <input type="hidden" name="amount" value="1">
                                                    <button type="submit" class="text-[9px] bg-indigo-500 text-white px-1 rounded hover:bg-indigo-600">+1</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else — @endif
                        </td>
                        <td class="px-2 py-1.5 border-b text-center">
                            @if($wd && $wd->work_start)
                                @php
                                    $assigned = array_sum($day['tasks']);
                                    $total = $wd->total_minutes;
                                    $pct = $total ? round($assigned/$total*100) : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $pct>100?'bg-red-500':($pct>75?'bg-amber-500':'bg-emerald-500') }}"
                                        style="width:{{ min(100,$pct) }}%"></div>
                                </div>
                                <span class="text-[10px] {{ $pct>100?'text-red-600 font-medium':'text-gray-500' }}">{{ $pct }}%</span>
                            @else — @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection