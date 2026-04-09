<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissariatPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Загружаем справочники
        $commissariats = DB::table('commissariats')->get();
        if ($commissariats->count() === 0) {
            $this->command->error('❌ Нет комиссариатов в базе. Запустите CommissariatSeeder.');
            return;
        }

        $departmentsByComm = DB::table('departments')->get()->groupBy('commissariat_id');
        $divisionsByDept = DB::table('divisions')->whereNotNull('department_id')->get()->groupBy('department_id');
        $independentDivisionsByComm = DB::table('divisions')->whereNull('department_id')->get()->groupBy('commissariat_id');

        // Должности сгруппированные по chief_type name
        $chiefTypes = DB::table('chief_types')->pluck('id', 'name'); // name => id
        $positions = DB::table('positions')->get()->groupBy('chief_type_id'); // chief_type_id => collection

        $getByChiefName = function(string $name) use ($chiefTypes, $positions) {
            $id = $chiefTypes[$name] ?? null;
            if (!$id) return collect();
            return $positions[$id] ?? collect();
        };

        $commHead = $getByChiefName('начальник комиссариата')->first();
        $deptHeads = $getByChiefName('начальник отдела')->values();
        $divHeads = $getByChiefName('начальник отделения')->values();
        $workers = $getByChiefName('работник')->values();

        if (!$commHead) {
            $this->command->error('❌ В справочнике positions отсутствует должность типа "начальник комиссариата".');
            return;
        }
        if ($deptHeads->isEmpty()) {
            $this->command->error('❌ Отсутствуют должности типа "начальник отдела".');
            return;
        }
        if ($divHeads->isEmpty()) {
            $this->command->error('❌ Отсутствуют должности типа "начальник отделения".');
            return;
        }
        if ($workers->isEmpty()) {
            $this->command->error('❌ Отсутствуют должности типа "работник".');
            return;
        }

        $totalPerComm = 30;
        $insertedTotal = 0;

        foreach ($commissariats as $comm) {
            $records = [];

            // Собираем места в комиссариате
            $places = [];

            // 1) комиссариат (уровень commissariat)
            $places[] = ['department_id' => null, 'division_id' => null, 'is_independent' => false];

            // 2) отделы
            $commDepts = $departmentsByComm[$comm->id] ?? collect();
            foreach ($commDepts as $dept) {
                $places[] = ['department_id' => $dept->id, 'division_id' => null, 'is_independent' => false];

                // отделения внутри отдела
                $deptDivs = $divisionsByDept[$dept->id] ?? collect();
                foreach ($deptDivs as $div) {
                    $places[] = ['department_id' => $dept->id, 'division_id' => $div->id, 'is_independent' => false];
                }
            }

            // 3) самостоятельные отделения (department_id = null) — помечаем is_independent = true
            $indDivs = $independentDivisionsByComm[$comm->id] ?? collect();
            foreach ($indDivs as $div) {
                $places[] = ['department_id' => null, 'division_id' => $div->id, 'is_independent' => true];
            }

            // === Сначала — руководящие штатные позиции ===
            // 1) Начальник комиссариата (1)
            $records[] = [
                'commissariat_id' => $comm->id,
                'department_id' => null,
                'division_id' => null,
                'position_id' => $commHead->id,
                'rate_total' => 1.00,
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 2) Начальники отделов: по одному на каждый отдел (если отделов меньше — создаём столько, сколько есть)
            $deptHeadIdx = 0;
            foreach ($commDepts as $dept) {
                $pos = $deptHeads[$deptHeadIdx % $deptHeads->count()];
                $records[] = [
                    'commissariat_id' => $comm->id,
                    'department_id' => $dept->id,
                    'division_id' => null,
                    'position_id' => $pos->id,
                    'rate_total' => 1.00,
                    'is_independent' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $deptHeadIdx++;
            }

            // 3) Начальники отделений: по одному на каждое отделение (включая самостоятельные)
            $divHeadIdx = 0;
            // отделения в отделах
            foreach ($commDepts as $dept) {
                $deptDivs = $divisionsByDept[$dept->id] ?? collect();
                foreach ($deptDivs as $div) {
                    $pos = $divHeads[$divHeadIdx % $divHeads->count()];
                    $records[] = [
                        'commissariat_id' => $comm->id,
                        'department_id' => $dept->id,
                        'division_id' => $div->id,
                        'position_id' => $pos->id,
                        'rate_total' => 1.00,
                        'is_independent' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $divHeadIdx++;
                }
            }
            // самостоятельные отделения
            foreach ($indDivs as $div) {
                $pos = $divHeads[$divHeadIdx % $divHeads->count()];
                $records[] = [
                    'commissariat_id' => $comm->id,
                    'department_id' => null,
                    'division_id' => $div->id,
                    'position_id' => $pos->id,
                    'rate_total' => 1.00,
                    'is_independent' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $divHeadIdx++;
            }

            // === Дозаполнение до totalPerComm рабочими позициями ===
            $currentCount = count($records);
            $remaining = $totalPerComm - $currentCount;
            if ($remaining > 0) {
                // Составляем список мест для балансировки (без повторного добавления руководителей)
                // places[] уже содержит все места; используем циклическое распределение
                $placesCount = count($places);
                if ($placesCount === 0) {
                    $this->command->warn("⚠️ Нет доступных мест для комиссариата {$comm->name}");
                } else {
                    $workerIdx = 0;
                    for ($i = 0; $i < $remaining; $i++) {
                        $place = $places[$i % $placesCount];
                        $workerPos = $workers[$workerIdx % $workers->count()];
                        $records[] = [
                            'commissariat_id' => $comm->id,
                            'department_id' => $place['department_id'],
                            'division_id' => $place['division_id'],
                            'position_id' => $workerPos->id,
                            'rate_total' => 1.00,
                            'is_independent' => ($place['division_id'] !== null && $place['department_id'] === null) ? true : false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $workerIdx++;
                    }
                }
            }

            // Вставляем пакетно, избегая дублирования
            // Используем insertOrIgnore, чтобы при повторном запуске не было ошибок
            $chunks = array_chunk($records, 100);
            $inserted = 0;
            foreach ($chunks as $chunk) {
                $res = DB::table('commissariat_positions')->insertOrIgnore($chunk);
                // insertOrIgnore возвращает boolean, а не число вставленных строк — считаем условно
                $inserted += is_array($chunk) ? count($chunk) : 0;
            }

            $insertedTotal += count($records);
            $this->command->info("✓ Комиссариат '{$comm->name}': добавлено штатных позиций: ".count($records));
        }

        $this->command->info("✓ Всего создано commissariat_positions (прибл.): {$insertedTotal}");
    }
}
