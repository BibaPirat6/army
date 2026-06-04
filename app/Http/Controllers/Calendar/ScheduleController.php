<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskInstance;
use App\Models\WorkDay;
use App\Services\Schedule\TimelineBuilder;
use App\Services\WorkloadPlanner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * ГЛАВНАЯ СТРАНИЦА ГРАФИКА
     */
    public function index(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $schedule = $planner->generatePlan($employee, $from, $to);

        return view('admin.calendar.schedule.index', [
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'schedule' => $schedule,
            'hasSchedule' => WorkDay::where('employee_id', $employee->id)->exists(),
        ]);
    }

    /**
     * ФОРМА НАСТРОЙКИ
     */
    public function setup(Employee $employee)
    {
        return view('admin.calendar.schedule.setup', compact('employee'));
    }

    /**
     * СОХРАНЕНИЕ ГРАФИКА
     */
    public function generate(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'weekly_hours' => 'required|integer|min:1|max:168',
            'days' => 'required|array',
            'days.*.type' => 'required|in:рабочий_день,выходной',
            'days.*.work_start' => 'nullable|date_format:H:i',
            'days.*.work_end' => 'nullable|date_format:H:i',
            'days.*.breaks' => 'nullable|array',
        ]);

        $year = (int) $validated['year'];
        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);
        $current = $start->copy();

        // 1. СОХРАНЯЕМ work_days
        while ($current->lte($end)) {
            $dow = (string) $current->dayOfWeek;
            $day = $validated['days'][$dow] ?? null;
            $isWorking = $day && $day['type'] === 'рабочий_день';

            WorkDay::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $current->toDateString(),
                ],
                [
                    'type' => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorking ? ($day['work_start'] ?? '09:00') : null,
                    'work_end' => $isWorking ? ($day['work_end'] ?? '18:00') : null,
                    'breaks' => $isWorking ? ($day['breaks'] ?? null) : null,
                    'weekly_hours' => $validated['weekly_hours'],
                ]
            );

            $current->addDay();
        }

        // 2. ПЕРЕСЧЁТ ПЛАНА
        TaskInstance::whereIn('task_id', function ($q) use ($employee) {
            $q->select('task_id')
                ->from('task_assignments')
                ->where('employee_id', $employee->id);
        })->delete();

        $from = Carbon::create($year, 1, 1);
        $to = Carbon::create($year, 12, 31);
        $planner->generatePlan($employee, $from, $to);

        return redirect()
            ->route('calendar.schedule.employee', $employee->id)
            ->with('success', 'График обновлён и пересчитан');
    }

    /**
     * Получить информацию о задании для модального окна
     */
    public function assignmentInfo(TaskAssignment $taskAssignment): JsonResponse
    {
        try {
            // Загружаем связи с проверкой существования
            $taskAssignment->load([
                'task.subtasks',
                'employee.employeePositions.commissariatPosition.commissariat',
            ]);

            // Получаем сотрудника
            $employee = $taskAssignment->employee;
            
            if (!$employee) {
                throw new \Exception('Сотрудник не найден для назначения #' . $taskAssignment->id);
            }

            // Получаем ФИО сотрудника через связь с people
            $employeeName = $this->getEmployeeFullName($employee);

            // Получаем задачу
            $task = $taskAssignment->task;
            
            if (!$task) {
                throw new \Exception('Задача не найдена для назначения #' . $taskAssignment->id);
            }

            // Рассчитываем время итерации
            $iterationTime = $task->subtasks->sum('avg_time_minutes') ?: 60;

            // Вычисляем оставшуюся квоту
            $remainingQuota = max(0, (int)$taskAssignment->quota - (int)$taskAssignment->completed_count);

            return response()->json([
                'success' => true,
                'assignment_id' => $taskAssignment->id,
                'task_id' => $task->id,
                'task_name' => $task->title ?? 'Без названия',
                'employee_name' => $employeeName,
                'quota' => (int)$taskAssignment->quota,
                'completed_count' => (int)$taskAssignment->completed_count,
                'iteration_time' => $iterationTime,
                'priority' => $taskAssignment->priority ?? 0,
                'remaining_quota' => $remainingQuota,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Назначение не найдено', [
                'assignment_id' => $taskAssignment->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Назначение не найдено',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Ошибка получения информации о назначении', [
                'assignment_id' => $taskAssignment->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка загрузки данных: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получение полного имени сотрудника
     */
    private function getEmployeeFullName(Employee $employee): string
    {
        // Проверяем наличие связи с person через модель Employee
        if (method_exists($employee, 'person')) {
            $person = $employee->person;
            
            if ($person) {
                $lastName = $person->фамилия ?? $person->last_name ?? '';
                $firstName = $person->имя ?? $person->first_name ?? '';
                
                return trim($lastName . ' ' . $firstName) ?: 'Сотрудник #' . $employee->id;
            }
        }

        // Запасной вариант - получаем через employee_positions
        if (method_exists($employee, 'employeePositions')) {
            $currentPosition = $employee->employeePositions()
                ->where('status', 'active')
                ->first();
            
            if ($currentPosition && method_exists($currentPosition, 'person')) {
                $person = $currentPosition->person;
                if ($person) {
                    $lastName = $person->фамилия ?? $person->last_name ?? '';
                    $firstName = $person->имя ?? $person->first_name ?? '';
                    
                    return trim($lastName . ' ' . $firstName) ?: 'Сотрудник #' . $employee->id;
                }
            }
        }

        // Если ничего не нашли - возвращаем ID
        return 'Сотрудник #' . $employee->id;
    }

    /**
     * Отметка выполнения задачи
     */
    public function complete(Request $request, TaskAssignment $taskAssignment): JsonResponse
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'completed_count' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:' . (int)$taskAssignment->quota,
                ],
            ]);

            $newCompletedCount = (int)$validated['completed_count'];
            $oldCompletedCount = (int)$taskAssignment->completed_count;

            // Если значение не изменилось
            if ($newCompletedCount === $oldCompletedCount) {
                return response()->json([
                    'success' => true,
                    'message' => 'Изменений нет',
                    'data' => [
                        'assignment_id' => $taskAssignment->id,
                        'completed_count' => $newCompletedCount,
                        'remaining_quota' => $taskAssignment->quota - $newCompletedCount,
                    ],
                ]);
            }

            DB::beginTransaction();

            // Блокируем запись
            $lockedAssignment = TaskAssignment::where('id', $taskAssignment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Обновляем
            $lockedAssignment->completed_count = $newCompletedCount;

            // Если полностью выполнено
            if ($newCompletedCount >= $lockedAssignment->quota && $lockedAssignment->quota > 0) {
                // Удаляем будущие распределения
                TaskInstance::where('task_id', $lockedAssignment->task_id)
                    ->where('date', '>', now()->format('Y-m-d'))
                    ->delete();
            } 
            // Если частично выполнено - перераспределяем
            elseif ($newCompletedCount > 0 && $newCompletedCount > $oldCompletedCount) {
                try {
                    $planner = app(WorkloadPlanner::class);
                    
                    if (method_exists($planner, 'redistributeAfterCompletion')) {
                        $planner->redistributeAfterCompletion($lockedAssignment);
                    } else {
                        // Если метода нет - просто удаляем старые инстансы
                        TaskInstance::where('task_id', $lockedAssignment->task_id)
                            ->where('date', '>', now()->format('Y-m-d'))
                            ->delete();
                            
                        Log::warning('Метод redistributeAfterCompletion не найден в WorkloadPlanner');
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка перераспределения', [
                        'error' => $e->getMessage(),
                    ]);
                    // Продолжаем выполнение даже при ошибке перераспределения
                }
            }

            $lockedAssignment->save();

            DB::commit();

            Log::info('Прогресс задачи обновлен', [
                'assignment_id' => $taskAssignment->id,
                'old_completed' => $oldCompletedCount,
                'new_completed' => $newCompletedCount,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Прогресс обновлен',
                'data' => [
                    'assignment_id' => $taskAssignment->id,
                    'completed_count' => $newCompletedCount,
                    'remaining_quota' => $taskAssignment->quota - $newCompletedCount,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные: ' . implode(', ', $e->errors()['completed_count'] ?? ['неизвестная ошибка']),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка при отметке выполнения', [
                'assignment_id' => $taskAssignment->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения. Попробуйте позже.',
            ], 500);
        }
    }

    /**
     * Таймлайн дня
     */
    public function timeline(
        Request $request,
        Employee $employee,
        WorkloadPlanner $planner,
        TimelineBuilder $timelineBuilder
    ) {
        $date = $request->date
            ? Carbon::parse($request->date)
            : now();

        $schedule = $planner->generatePlan(
            $employee,
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay()
        );

        $dayData = $schedule['plan'][$date->toDateString()] ?? null;

        if (!$dayData) {
            abort(404);
        }

        $assignments = TaskAssignment::query()
            ->where('employee_id', $employee->id)
            ->with('task:id,title,color')
            ->get();

        foreach ($dayData['task_meta'] as $taskId => &$meta) {
            $assignment = $assignments->firstWhere('task_id', $taskId);
            $meta['color'] = $assignment?->task?->color ?? '#3B82F6';
            $meta['priority'] = $assignment?->priority ?? 999;
        }

        $blocks = $timelineBuilder->build($dayData);

        return view(
            'admin.calendar.schedule.timeline',
            compact('employee', 'date', 'dayData', 'blocks')
        );
    }

    /**
     * Проверка прав доступа к назначению
     */
    private function authorizeAccess(TaskAssignment $assignment): void
    {
        $user = Auth::user();

        // Администратор имеет полный доступ
        if ($user->hasRole('администратор')) {
            return;
        }

        // Проверяем, что пользователь - сотрудник, которому принадлежит назначение
        $employee = $user->employee;

        if (!$employee || $assignment->employee_id !== $employee->id) {
            // Проверяем, может быть пользователь - начальник отдела
            if (!$user->hasRole('начальник_отдела')) {
                abort(403, 'У вас нет прав для изменения этого назначения');
            }

            // Начальник отдела может редактировать только своих подчиненных
            $isSubordinate = Employee::where('id', $assignment->employee_id)
                ->whereHas('employeePositions.commissariatPosition.commissariat', function ($q) use ($employee) {
                    $q->whereHas('departments', function ($subQ) use ($employee) {
                        $subQ->whereHas('divisions', function ($divQ) use ($employee) {
                            $divQ->whereHas('positions', function ($posQ) use ($employee) {
                                $posQ->whereHas('employeePositions', function ($epQ) use ($employee) {
                                    $epQ->where('employee_id', $employee->id);
                                });
                            });
                        });
                    });
                })
                ->exists();

            if (!$isSubordinate) {
                abort(403, 'Вы можете редактировать назначения только своих подчиненных');
            }
        }
    }

    /**
     * Очистка будущих распределений для выполненного назначения
     */
    private function cleanupFutureInstances(TaskAssignment $assignment): void
    {
        // Удаляем TaskInstance только для этой задачи, начиная с завтрашнего дня
        TaskInstance::where('task_id', $assignment->task_id)
            ->where('date', '>', now()->format('Y-m-d'))
            ->whereDoesntHave('taskAssignments', function ($q) use ($assignment) {
                // Не удаляем, если есть другие активные назначения на эту задачу
                $q->where('id', '!=', $assignment->id)
                    ->where('status', '!=', 'completed');
            })
            ->delete();
    }

    /**
     * Обновление прогресса в TaskInstance
     */
    private function updateTaskInstanceProgress(TaskAssignment $assignment): void
    {
        // Находим все TaskInstance для этой задачи
        $instances = TaskInstance::where('task_id', $assignment->task_id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->get();

        foreach ($instances as $instance) {
            // Получаем все назначения для этого инстанса
            $totalQuota = TaskAssignment::where('task_instance_id', $instance->id)
                ->sum('quota');

            $totalCompleted = TaskAssignment::where('task_instance_id', $instance->id)
                ->sum('completed_count');

            if ($totalQuota > 0) {
                $instance->daily_quota = max(0, $totalQuota - $totalCompleted);
                $instance->save();
            }
        }
    }
}