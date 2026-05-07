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
        <button onclick="document.getElementById('settingsForm').classList.toggle('hidden')"
            class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            ⚙️ Настроить месяц
        </button>
    </div>

    {{-- Форма настройки месяца --}}
    <div id="settingsForm" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border">
        <form action="{{ route('calendar.schedule.generate', $employee->id) }}" method="POST">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">

            <p class="text-sm font-medium text-gray-700 mb-3">
                Настройка на {{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($weeks as $weekNum => $week)
                    @php $days = $week['days'] ?? []; @endphp
                    <div class="bg-white rounded-lg border p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">{{ $week['label'] }}</p>

                        {{-- Дни недели --}}
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach(['1' => 'Пн','2' => 'Вт','3' => 'Ср','4' => 'Чт','5' => 'Пт','6' => 'Сб','0' => 'Вс'] as $val => $label)
                                <label class="text-[10px] cursor-pointer select-none">
                                    <input type="checkbox" name="weeks[{{ $weekNum }}][days][]" value="{{ $val }}"
                                        {{ in_array((int)$val, array_map('intval', $days)) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <span class="px-1.5 py-0.5 rounded border peer-checked:bg-indigo-600 peer-checked:text-white text-gray-500">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Часы --}}
                        <div class="flex items-center gap-1 text-[10px]">
                            <input type="time" name="weeks[{{ $weekNum }}][work_start]" value="{{ $week['work_start'] ?? '09:00' }}"
                                class="border rounded px-1 py-0.5 w-[70px]">
                            <span>–</span>
                            <input type="time" name="weeks[{{ $weekNum }}][work_end]" value="{{ $week['work_end'] ?? '18:00' }}"
                                class="border rounded px-1 py-0.5 w-[70px]">
                            <span class="ml-1">Обед:</span>
                            <input type="time" name="weeks[{{ $weekNum }}][break_start]" value="{{ $week['break_start'] ?? '13:00' }}"
                                class="border rounded px-1 py-0.5 w-[70px]">
                            <span>–</span>
                            <input type="time" name="weeks[{{ $weekNum }}][break_end]" value="{{ $week['break_end'] ?? '14:00' }}"
                                class="border rounded px-1 py-0.5 w-[70px]">
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="mt-3 px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                Сохранить график
            </button>
        </form>
    </div>

    {{-- Навигация по месяцам --}}
    <div class="flex items-center gap-4 mb-4 text-sm">
        @php
            $prev = Carbon\Carbon::create($year, $month, 1)->subMonth();
            $next = Carbon\Carbon::create($year, $month, 1)->addMonth();
        @endphp
        <a href="?month={{ $prev->month }}&year={{ $prev->year }}" class="text-gray-500 hover:text-indigo-600">
            ← {{ $prev->translatedFormat('F') }}
        </a>
        <span class="font-semibold text-gray-700">
            {{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
        </span>
        <a href="?month={{ $next->month }}&year={{ $next->year }}" class="text-gray-500 hover:text-indigo-600">
            {{ $next->translatedFormat('F') }} →
        </a>
    </div>

    {{-- Таблица --}}
    <div class="overflow-auto bg-white rounded shadow border" style="max-height:70vh">
        <table class="min-w-max text-xs border-collapse">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-gray-50 px-2 py-1.5 border-r border-b min-w-[60px]">День</th>
                    <th class="px-2 py-1.5 border-b min-w-[80px]">Статус</th>
                    <th class="px-2 py-1.5 border-b min-w-[150px]">Часы</th>
                    <th class="px-2 py-1.5 border-b min-w-[300px]">Задачи</th>
                    <th class="px-2 py-1.5 border-b min-w-[80px]">Загрузка</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedule['plan'] as $date => $day)
                    @php $wd = $day['work_day']; @endphp
                    <tr class="{{ $wd && $wd->type !== 'рабочий_день' ? 'bg-gray-50' : '' }}">
                        <td class="sticky left-0 bg-white px-2 py-1.5 border-r border-b font-medium">
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
                                <span class="font-medium">{{ Carbon\Carbon::parse($wd->work_start)->format('H:i') }} – {{ Carbon\Carbon::parse($wd->work_end)->format('H:i') }}</span>
                                @php $b = is_string($wd->breaks) ? json_decode($wd->breaks, true) : $wd->breaks; @endphp
                                @if(!empty($b))
                                    <div class="text-gray-400">обед:
                                        @foreach($b as $br) {{ Carbon\Carbon::parse($br['start'])->format('H:i') }}–{{ Carbon\Carbon::parse($br['end'])->format('H:i') }} @endforeach
                                    </div>
                                @endif
                            @else — @endif
                        </td>
                        <td class="px-2 py-1.5 border-b">
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