@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto p-4">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-lg font-bold">
                {{ $employee->person->фамилия }} {{ $employee->person->имя }}
            </h1>

            <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
                class="px-3 py-1 bg-indigo-600 text-white rounded">
                ⚙ Настроить график
            </a>
        </div>

        @if(!$hasSchedule)
            <div class="p-6 bg-yellow-50 border rounded">
                <p class="mb-3">График ещё не создан</p>

                <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Создать график
                </a>
            </div>
        @else

            <div class="overflow-auto bg-white border rounded">
                <table class="w-full text-xs">

                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2">Дата</th>
                            <th>Статус</th>
                            <th>Задачи</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($schedule as $date => $day)
                            <tr class="border-b">
                                <td class="p-2">
                                    {{ \Carbon\Carbon::parse($date)->format('d.m') }}
                                </td>

                                <td>
                                    @if($day['work_day'])
                                        {{ $day['work_day']->type === 'рабочий_день' ? 'Рабочий' : 'Выходной' }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if($day['tasks']->count())
                                        @foreach($day['tasks'] as $task)
                                            <div class="text-xs">
                                                Task #{{ $task->task_id }} — {{ $task->daily_quota }}
                                            </div>
                                        @endforeach
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        @endif

    </div>
@endsection