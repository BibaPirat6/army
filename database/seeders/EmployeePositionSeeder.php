<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Импортируем модели
use App\Models\Employee;
use App\Models\CommissariatPosition;
use App\Models\EmployeePositionStatus;
use App\Models\EmployeePosition;

class EmployeePositionSeeder extends Seeder
{
    /**
     * Веса статусов для распределения
     */
    private const STATUS_WEIGHTS = [
        'работает' => 70,
        'отпуск'   => 15,
        'декрет'   => 10,
        'уволен'   => 5,
    ];

    /**
     * ~25% должностей останутся вакантными
     */
    private const VACANCY_RATE = 0;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Статусы
        $statuses = DB::table('employee_position_statuses')->pluck('id', 'name')->toArray();
        if (empty($statuses)) {
            $this->command->error('❌ Нет статусов в employee_position_statuses. Запустите EmployeePositionStatusSeeder.');
            return;
        }

        // Загружаем все штатные позиции с привязанной позицией (чтобы знать chief_type)
        $commPositions = DB::table('commissariat_positions as cp')
            ->join('positions as p', 'cp.position_id', '=', 'p.id')
            ->select('cp.*', 'p.chief_type_id')
            ->orderBy('cp.commissariat_id')
            ->get()
            ->groupBy('commissariat_id');

        if ($commPositions->isEmpty()) {
            $this->command->error('❌ Нет commissariat_positions. Запустите CommissariatPositionSeeder.');
            return;
        }

        // Сотрудники сгруппированные по комиссариату
        $employeesByComm = DB::table('employees')->get()->groupBy('commissariat_id');

        $totalAssigned = 0;
        $totalPositions = 0;

        foreach ($commPositions as $commId => $positions) {
            $positions = $positions->values();
            $totalPositions += $positions->count();

            // сотрудники в этом комиссариате (shuffle чтобы распределение было случайным)
            $availableEmployees = ($employeesByComm[$commId] ?? collect())->shuffle()->values();
            $empIdx = 0;

            foreach ($positions as $pos) {
                // 25% оставляем вакантными
                if (rand(1, 100) <= (int)(self::VACANCY_RATE * 100)) {
                    continue;
                }

                // берем сотрудника из этого комиссариата, если есть
                $employee = $availableEmployees->get($empIdx) ?? null;
                if ($employee) {
                    $empIdx++;
                } else {
                    // fallback: любой сотрудник из таблицы (вдруг штат персонально меньше)
                    $employee = DB::table('employees')->inRandomOrder()->first();
                    if (!$employee) {
                        $this->command->warn("⚠️ Нет доступных сотрудников для назначения позиции id={$pos->id}");
                        continue;
                    }
                }

                // определим статус — руководителям даём "работает"
                $statusName = ($pos->chief_type_id !== null) ? 'работает' : $this->getWeightedRandomStatus();
                $statusId = $statuses[$statusName] ?? reset($statuses);

                // ставка: руководители — 1.0, остальные — случайно из набора (ограничено available rate в commissariat_positions.rate_total)
                $rateOptions = [0.25, 0.5, 0.75, 1.0];
                $rate = ($pos->chief_type_id !== null) ? 1.0 : $rateOptions[array_rand($rateOptions)];

                // дата начала/окончания/ожидаемого возвращения
                $startedAt = $this->generateStartDate($statusName);
                $endedAt = null;
                $expectedReturnAt = null;
                $isActive = true;

                if ($statusName === 'уволен') {
                    $isActive = false;
                    $endedAt = $startedAt->copy()->addMonths(rand(3, 24));
                } elseif ($statusName === 'отпуск') {
                    $expectedReturnAt = $startedAt->copy()->addDays(rand(7, 56));
                } elseif ($statusName === 'декрет') {
                    $expectedReturnAt = $startedAt->copy()->addMonths(rand(18, 36));
                }

                // Вставляем назначение
                DB::table('employee_positions')->insert([
                    'employee_id' => $employee->id,
                    'commissariat_position_id' => $pos->id,
                    'rate' => round($rate, 2),
                    'employee_position_status_id' => $statusId,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'is_active' => $isActive,
                    'expected_return_at' => $expectedReturnAt,
                ]);

                $totalAssigned++;
            }
        }

        $this->command->info("✓ Назначений создано: {$totalAssigned} / {$totalPositions} (прибл.)");
    }

    /**
     * Получить случайный статус с учётом весов
     */
    private function getWeightedRandomStatus(): string
    {
        $pool = [];
        foreach (self::STATUS_WEIGHTS as $status => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = $status;
            }
        }
        return $pool[array_rand($pool)];
    }

    /**
     * Рассчитать занимаемую ставку в зависимости от статуса
     */
    private function calculateOccupiedRate(float $availableRate, string $statusName): float
    {
        if (!in_array($statusName, ['работает', 'уволен'])) {
            return min(0.01, $availableRate);
        }
        $options = [0.25, 0.5, 0.75, 1.0];
        $candidate = $options[array_rand($options)];
        return min($candidate, $availableRate);
    }

    /**
     * Сгенерировать дату начала в зависимости от статуса
     */
    private function generateStartDate(string $statusName): Carbon
    {
        $now = now();
        if ($statusName === 'уволен') {
            return $now->copy()->subMonths(rand(12, 48));
        }
        if ($statusName === 'декрет') {
            return $now->copy()->subMonths(rand(6, 30));
        }
        if ($statusName === 'отпуск') {
            return $now->copy()->subDays(rand(0, 30));
        }
        return $now->copy()->subDays(rand(0, 730));
    }

    /**
     * Вывести статистику
     */
    private function printStats(): void
    {
        $stats = DB::table('employee_positions')
            ->join('employee_position_statuses', 'employee_positions.employee_position_status_id', '=', 'employee_position_statuses.id')
            ->selectRaw('employee_position_statuses.name, COUNT(*) as count')
            ->groupBy('employee_position_statuses.name')
            ->pluck('count', 'name');

        $this->command->info("\n📊 Статистика назначений:");
        foreach (self::STATUS_WEIGHTS as $status => $_) {
            $count = $stats[$status] ?? 0;
            $bar = str_repeat('█', (int)($count / 2));
            $this->command->line("   {$status}: {$count} {$bar}");
        }

        $vacant = DB::table('commissariat_positions as cp')
            ->leftJoin('employee_positions as ep', function($join) {
                $join->on('cp.id', '=', 'ep.commissariat_position_id')
                     ->where('ep.is_active', true)
                     ->whereNull('ep.ended_at');
            })
            ->whereNull('ep.id')
            ->count();

        $this->command->info("   💼 Вакантные должности: {$vacant}");
    }
}