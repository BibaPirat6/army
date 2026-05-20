@extends('layouts.main')

@section('header-title', 'График — ' . $employee->person->фамилия)

@section('content')
<div class="max-w-full mx-auto px-6 py-4">

    <form id="scheduleForm" action="{{ route('calendar.schedule.generate', $employee->id) }}" method="POST">
        @csrf

        <input type="hidden" name="year" value="{{ now()->year }}">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $employee->person->фамилия }} {{ $employee->person->имя }}
                </h2>

                <p class="text-sm text-gray-500">
                    Настройка графика на {{ now()->year }} год
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-white rounded-lg border px-4 py-2">
                    <span class="text-sm text-gray-600">Цель в неделю:</span>

                    <input
                        type="number"
                        id="weeklyHoursTotal"
                        name="weekly_hours"
                        value="{{ \App\Models\WorkDay::where('employee_id', $employee->id)->whereYear('date', now()->year)->first()?->weekly_hours ?? 40 }}"
                        min="1"
                        max="168"
                        class="w-16 text-center font-bold text-indigo-600 border-0 border-b-2 border-indigo-300 focus:border-indigo-500 focus:ring-0 outline-none"
                    >

                    <span class="text-sm text-gray-600">ч</span>
                </div>

                <span id="totalInfo" class="text-sm text-gray-500"></span>
            </div>
        </div>

        @php
            $existingByDay = \App\Models\WorkDay::where('employee_id', $employee->id)
                ->whereYear('date', now()->year)
                ->get()
                ->groupBy(fn($d) => \Carbon\Carbon::parse($d->date)->dayOfWeek);
        @endphp
            <input type="hidden" name="year" value="{{ now()->year }}">

            <div class="grid grid-cols-7 gap-3 mb-4">
                @foreach(['1' => 'Пн', '2' => 'Вт', '3' => 'Ср', '4' => 'Чт', '5' => 'Пт', '6' => 'Сб', '0' => 'Вс'] as $val => $label)
                    @php
                        $dayData = $existingByDay->get($val)?->first();
                        $isWorking = $dayData && $dayData->type === 'рабочий_день';
                        $breaks = $dayData?->breaks;
                        if (is_string($breaks))
                            $breaks = json_decode($breaks, true) ?: [];
                        if (!$isWorking)
                            $breaks = [];
                    @endphp
                    <div class="day-card bg-white rounded-lg border {{ $isWorking ? 'border-gray-200' : 'border-gray-100 bg-gray-50' }} overflow-hidden"
                        data-day="{{ $val }}">
                        {{-- Заголовок --}}
                        <div
                            class="flex items-center justify-between px-3 py-2 {{ $isWorking ? 'bg-indigo-50/50' : 'bg-gray-100/50' }}">
                            <span
                                class="text-sm font-semibold {{ $isWorking ? 'text-gray-800' : 'text-gray-400' }}">{{ $label }}</span>
                            <select name="days[{{ $val }}][type]"
                                class="day-type-select text-xs rounded border-0 {{ $isWorking ? 'bg-white text-indigo-600' : 'bg-transparent text-gray-400' }} font-medium cursor-pointer outline-none">
                                <option value="рабочий_день" {{ $isWorking ? 'selected' : '' }}>Рабочий</option>
                                <option value="выходной" {{ !$isWorking ? 'selected' : '' }}>Выходной</option>
                            </select>
                        </div>

                        <div class="day-details px-3 py-2 space-y-2 {{ $isWorking ? '' : 'hidden' }}">
                            {{-- Время --}}
                            <div class="flex items-center gap-1.5">
                                <input type="time" name="days[{{ $val }}][work_start]"
                                    value="{{ $dayData?->work_start ? \Carbon\Carbon::parse($dayData->work_start)->format('H:i') : '09:00' }}"
                                    class="day-time w-full text-xs rounded border-gray-200 py-1 px-2 focus:border-indigo-400 focus:ring-0 outline-none">
                                <span class="text-gray-300 text-xs">—</span>
                                <input type="time" name="days[{{ $val }}][work_end]"
                                    value="{{ $dayData?->work_end ? \Carbon\Carbon::parse($dayData->work_end)->format('H:i') : '18:00' }}"
                                    class="day-time w-full text-xs rounded border-gray-200 py-1 px-2 focus:border-indigo-400 focus:ring-0 outline-none">
                            </div>

                            {{-- Обеды --}}
                            <div class="breaks-container space-y-1.5" data-day="{{ $val }}">
                                @foreach($breaks as $i => $b)
                                    <div class="break-row flex items-center gap-1">
                                        <input type="time" name="days[{{ $val }}][breaks][{{ $i }}][start]"
                                            value="{{ $b['start'] }}"
                                            class="break-input w-full text-[11px] rounded border-gray-200 py-0.5 px-1.5 focus:border-indigo-400 focus:ring-0 outline-none">
                                        <span class="text-gray-300 text-[11px]">—</span>
                                        <input type="time" name="days[{{ $val }}][breaks][{{ $i }}][end]" value="{{ $b['end'] }}"
                                            class="break-input w-full text-[11px] rounded border-gray-200 py-0.5 px-1.5 focus:border-indigo-400 focus:ring-0 outline-none">
                                        <button type="button" onclick="removeBreak(this)"
                                            class="text-gray-300 hover:text-red-500 text-sm leading-none">&times;</button>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addBreak('{{ $val }}')"
                                class="text-[11px] text-indigo-500 hover:text-indigo-700 font-medium">
                                + обед
                            </button>
                        </div>

                        {{-- Итого --}}
                        <div class="px-3 py-1.5 border-t {{ $isWorking ? 'border-gray-100' : 'border-gray-200' }}">
                            <span
                                class="day-summary text-[11px] font-medium {{ $isWorking ? 'text-indigo-600' : 'text-gray-400' }}">
                                {{ $isWorking ? '—' : 'выходной' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Ошибка --}}
            <div id="globalError"
                class="hidden mb-3 p-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 text-center font-medium">
            </div>

            {{-- Нижняя панель --}}
            <div class="flex items-center justify-between bg-white rounded-lg border px-4 py-3">
                <div class="flex items-center gap-2">
                    <span id="statusBadge" class="hidden px-2.5 py-1 rounded-full text-xs font-bold"></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url()->previous() }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Отмена</a>
                    <button type="submit" id="submitBtn"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-30 disabled:cursor-not-allowed transition">
                        Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let breakCounter = {};

        function addBreak(day) {
            if (!breakCounter[day]) breakCounter[day] = 99;
            breakCounter[day]++;
            const c = document.querySelector(`.breaks-container[data-day="${day}"]`);
            const d = document.createElement('div');
            d.className = 'break-row flex items-center gap-1';
            d.innerHTML = `<input type="time" name="days[${day}][breaks][${breakCounter[day]}][start]" value="13:00" class="break-input w-full text-[11px] rounded border-gray-200 py-0.5 px-1.5 focus:border-indigo-400 focus:ring-0 outline-none"><span class="text-gray-300 text-[11px]">—</span><input type="time" name="days[${day}][breaks][${breakCounter[day]}][end]" value="14:00" class="break-input w-full text-[11px] rounded border-gray-200 py-0.5 px-1.5 focus:border-indigo-400 focus:ring-0 outline-none"><button type="button" onclick="removeBreak(this)" class="text-gray-300 hover:text-red-500 text-sm leading-none">&times;</button>`;
            c.appendChild(d);
            recalc();
        }

        function removeBreak(b) { b.closest('.break-row').remove(); recalc(); }

        function validateBreaks(row) {
            const ws = row.querySelector('input[name*="work_start"]')?.value;
            const we = row.querySelector('input[name*="work_end"]')?.value;
            if (!ws || !we) return true;
            const [wsh, wsm] = ws.split(':').map(Number), [weh, wem] = we.split(':').map(Number);
            const wsMin = wsh * 60 + wsm, weMin = weh * 60 + wem;
            const seen = new Set();
            for (const br of row.querySelectorAll('.break-row')) {
                const inp = br.querySelectorAll('input');
                if (inp.length < 2 || !inp[0].value || !inp[1].value) continue;
                const [sh, sm] = inp[0].value.split(':').map(Number), [eh, em] = inp[1].value.split(':').map(Number);
                const sMin = sh * 60 + sm, eMin = eh * 60 + em;
                if (sMin < wsMin || eMin > weMin) return 'Обед вне рабочего времени';
                if (sMin >= eMin) return 'Неверное время обеда';
                if (seen.has(sMin + '-' + eMin)) return 'Одинаковые обеды';
                seen.add(sMin + '-' + eMin);
                for (const other of row.querySelectorAll('.break-row')) {
                    if (other === br) continue;
                    const oi = other.querySelectorAll('input');
                    if (oi.length < 2 || !oi[0].value || !oi[1].value) continue;
                    const [osh, osm] = oi[0].value.split(':').map(Number), [oeh, oem] = oi[1].value.split(':').map(Number);
                    if (sMin < oeh * 60 + oem && eMin > osh * 60 + osm) return 'Обеды пересекаются';
                }
            }
            return true;
        }

        function calcDay(row) {
            if (row.querySelector('.day-type-select')?.value !== 'рабочий_день') return 0;
            const ws = row.querySelector('input[name*="work_start"]')?.value;
            const we = row.querySelector('input[name*="work_end"]')?.value;
            if (!ws || !we) return 0;
            const [sh, sm] = ws.split(':').map(Number), [eh, em] = we.split(':').map(Number);
            let t = (eh * 60 + em) - (sh * 60 + sm);
            row.querySelectorAll('.break-row').forEach(br => {
                const inp = br.querySelectorAll('input');
                if (inp.length >= 2 && inp[0].value && inp[1].value) {
                    const [bh, bm] = inp[0].value.split(':').map(Number), [eh2, em2] = inp[1].value.split(':').map(Number);
                    t -= (eh2 * 60 + em2) - (bh * 60 + bm);
                }
            });
            return Math.max(0, t);
        }

        function recalc() {
            let w = 0, err = false, msg = '';
            document.querySelectorAll('.day-card').forEach(card => {
                const type = card.querySelector('.day-type-select')?.value;
                const det = card.querySelector('.day-details');
                const sum = card.querySelector('.day-summary');
                if (type === 'рабочий_день') {
                    det?.classList.remove('hidden');
                    card.classList.add('border-gray-200'); card.classList.remove('border-gray-100', 'bg-gray-50');
                    const v = validateBreaks(card);
                    if (v !== true) { err = true; msg = v; card.classList.add('border-red-300'); if (sum) sum.textContent = 'ошибка'; return; }
                    card.classList.remove('border-red-300');
                    const m = calcDay(card); w += m;
                    if (sum) sum.textContent = (m / 60).toFixed(1) + ' ч';
                } else {
                    det?.classList.add('hidden');
                    card.classList.add('border-gray-100', 'bg-gray-50'); card.classList.remove('border-gray-200', 'border-red-300');
                    if (sum) sum.textContent = 'выходной';
                }
            });
            const h = w / 60, t = parseInt(document.getElementById('weeklyHoursTotal').value) || 40, d = h - t, da = Math.abs(d);
            document.getElementById('totalInfo').textContent = `(${h.toFixed(1)} / ${t} ч)`;
            const ge = document.getElementById('globalError'), sb = document.getElementById('statusBadge'), btn = document.getElementById('submitBtn');
            if (err) {
                ge.textContent = '⚠️ ' + msg; ge.classList.remove('hidden'); sb.classList.add('hidden'); btn.disabled = true;
            } else if (da < 0.05) {
                ge.classList.add('hidden'); sb.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700'; sb.textContent = '✓ Готово'; sb.classList.remove('hidden'); btn.disabled = false;
            } else if (d < 0) {
                ge.textContent = '⚠️ Недобор ' + da.toFixed(1) + ' ч'; ge.classList.remove('hidden'); sb.classList.add('hidden'); btn.disabled = true;
            } else {
                ge.textContent = '⚠️ Перебор +' + da.toFixed(1) + ' ч'; ge.classList.remove('hidden'); sb.classList.add('hidden'); btn.disabled = true;
            }
        }

        document.getElementById('weeklyHoursTotal').addEventListener('input', recalc);
        document.querySelectorAll('.day-type-select').forEach(s => s.addEventListener('change', recalc));
        document.addEventListener('input', e => { if (e.target.closest('.day-card')) recalc(); });
        recalc();
    </script>
@endsection