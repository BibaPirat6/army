@extends('layouts.main')

@section('header-title', 'Настройка графика — ' . $employee->person->фамилия)

@section('content')
<div class="max-w-lg mx-auto px-4 py-6">
    <h2 class="text-lg font-semibold mb-4">{{ $employee->person->фамилия }} {{ $employee->person->имя }}</h2>

    @php
        // Берём существующий график (первый попавшийся день, чтобы предзаполнить)
        $existing = \App\Models\WorkDay::where('employee_id', $employee->id)
            ->where('type', 'рабочий_день')
            ->whereYear('date', now()->year)
            ->first();
        $existingDays = \App\Models\WorkDay::where('employee_id', $employee->id)
            ->where('type', 'рабочий_день')
            ->whereYear('date', now()->year)
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->dayOfWeek)
            ->unique()
            ->values()
            ->toArray();
    @endphp

    <form id="scheduleForm" action="{{ route('calendar.schedule.generate', $employee->id) }}" method="POST"
        class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        <input type="hidden" name="year" value="{{ now()->year }}">

        {{-- Рабочие дни --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Рабочие дни</label>
            <div class="flex flex-wrap gap-2" id="workDaysContainer">
                @foreach(['1' => 'Пн','2' => 'Вт','3' => 'Ср','4' => 'Чт','5' => 'Пт','6' => 'Сб','0' => 'Вс'] as $val => $label)
                    @php
                        $checked = !empty($existingDays) ? in_array((int)$val, $existingDays) : in_array($val, ['1','2','3','4','5']);
                    @endphp
                    <label class="cursor-pointer select-none">
                        <input type="checkbox" name="work_days[]" value="{{ $val }}"
                            {{ $checked ? 'checked' : '' }}
                            class="sr-only peer work-day-checkbox">
                        <span class="px-3 py-1.5 rounded-lg border text-sm
                            peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                            text-gray-500 hover:border-indigo-300 transition">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Часы работы --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Начало работы</label>
                <input type="time" name="work_start" id="work_start"
                    value="{{ $existing?->work_start ? \Carbon\Carbon::parse($existing->work_start)->format('H:i') : '09:00' }}"
                    class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Окончание работы</label>
                <input type="time" name="work_end" id="work_end"
                    value="{{ $existing?->work_end ? \Carbon\Carbon::parse($existing->work_end)->format('H:i') : '18:00' }}"
                    class="w-full rounded-lg border-gray-300">
            </div>
        </div>

        {{-- Обеды --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">Обеды</label>
                <button type="button" onclick="addBreak()"
                    class="text-xs text-indigo-600 hover:text-indigo-800">+ Добавить обед</button>
            </div>
            <div id="breaksContainer" class="space-y-2">
                @php
                    $existingBreaks = $existing?->breaks;
                    if (is_string($existingBreaks)) $existingBreaks = json_decode($existingBreaks, true);
                    if (empty($existingBreaks)) $existingBreaks = [['start' => '13:00', 'end' => '14:00']];
                @endphp
                @foreach($existingBreaks as $i => $break)
                    <div class="flex items-center gap-2 break-row">
                        <input type="time" name="breaks[{{ $i }}][start]" value="{{ $break['start'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        <span class="text-gray-400">–</span>
                        <input type="time" name="breaks[{{ $i }}][end]" value="{{ $break['end'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @if($i > 0)
                            <button type="button" onclick="this.closest('.break-row').remove(); calculateHours()"
                                class="text-red-400 hover:text-red-600 text-lg">&times;</button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Часов в неделю --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Часов в неделю</label>
            <input type="number" name="weekly_hours" id="weekly_hours"
                value="{{ $existing?->weekly_hours ?? 40 }}" min="1" max="168"
                class="w-full rounded-lg border-gray-300">
        </div>

        {{-- Предупреждение о несовпадении часов --}}
        <div id="hoursWarning" class="hidden p-3 rounded-lg text-sm"></div>

        {{-- Кнопки --}}
        <div class="flex justify-end gap-3">
            <a href="{{ url()->previous() }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">Отмена</a>
            <button type="submit" id="submitBtn"
                class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                Сохранить график
            </button>
        </div>
    </form>
</div>

<script>
let breakIndex = {{ count($existingBreaks) }};

function addBreak() {
    breakIndex++;
    const container = document.getElementById('breaksContainer');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 break-row';
    div.innerHTML = `
        <input type="time" name="breaks[${breakIndex}][start]" value="13:00" class="w-full rounded-lg border-gray-300 text-sm">
        <span class="text-gray-400">–</span>
        <input type="time" name="breaks[${breakIndex}][end]" value="14:00" class="w-full rounded-lg border-gray-300 text-sm">
        <button type="button" onclick="this.closest('.break-row').remove(); calculateHours()" class="text-red-400 hover:text-red-600 text-lg">&times;</button>
    `;
    container.appendChild(div);
    calculateHours();
}

function calculateHours() {
    const workStart = document.getElementById('work_start').value;
    const workEnd = document.getElementById('work_end').value;
    const weeklyHours = parseInt(document.getElementById('weekly_hours').value) || 0;
    const checkedDays = document.querySelectorAll('.work-day-checkbox:checked').length;
    const warning = document.getElementById('hoursWarning');
    const submitBtn = document.getElementById('submitBtn');

    if (!workStart || !workEnd || checkedDays === 0) {
        submitBtn.disabled = true;
        warning.classList.add('hidden');
        return;
    }

    const [sh, sm] = workStart.split(':').map(Number);
    const [eh, em] = workEnd.split(':').map(Number);
    let dayMinutes = (eh * 60 + em) - (sh * 60 + sm);

    // Вычитаем обеды
    document.querySelectorAll('.break-row').forEach(row => {
        const [bs, be] = row.querySelectorAll('input');
        if (bs.value && be.value) {
            const [bsh, bsm] = bs.value.split(':').map(Number);
            const [beh, bem] = be.value.split(':').map(Number);
            dayMinutes -= (beh * 60 + bem) - (bsh * 60 + bsm);
        }
    });

    const dayHours = dayMinutes / 60;
    const totalWeekHours = dayHours * checkedDays;
    const diff = totalWeekHours - weeklyHours;

    if (Math.abs(diff) < 0.01) {
        warning.classList.add('hidden');
        submitBtn.disabled = false;
    } else if (diff < 0) {
        warning.className = 'p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
        warning.innerHTML = `⚠️ Недобор часов: ${Math.abs(diff).toFixed(1)} ч. Увеличьте рабочие дни или часы.`;
        warning.classList.remove('hidden');
        submitBtn.disabled = true;
    } else {
        warning.className = 'p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
        warning.innerHTML = `⚠️ Перебор часов: +${diff.toFixed(1)} ч. Уменьшите рабочие дни или часы.`;
        warning.classList.remove('hidden');
        submitBtn.disabled = true;
    }
}

document.getElementById('work_start').addEventListener('input', calculateHours);
document.getElementById('work_end').addEventListener('input', calculateHours);
document.getElementById('weekly_hours').addEventListener('input', calculateHours);
document.querySelectorAll('.work-day-checkbox').forEach(cb => cb.addEventListener('change', calculateHours));
document.querySelectorAll('#breaksContainer input').forEach(inp => inp.addEventListener('input', calculateHours));

calculateHours();
</script>
@endsection