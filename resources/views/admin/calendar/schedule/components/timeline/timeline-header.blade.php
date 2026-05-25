<div class="flex items-center justify-between">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Таймлайн сотрудника
        </h1>

        <p class="text-sm text-gray-500 mt-1">
            {{ $employee->full_name }}
        </p>
    </div>

    <div class="flex items-center gap-3">

        <a
            href="?date={{ $date->copy()->subDay()->toDateString() }}"
            class="
                px-4
                py-2
                rounded-xl
                border
                bg-white
                hover:bg-gray-50
            "
        >
            ←
        </a>

        <div class="font-semibold text-gray-700">
            {{ $date->format('d.m.Y') }}
        </div>

        <a
            href="?date={{ $date->copy()->addDay()->toDateString() }}"
            class="
                px-4
                py-2
                rounded-xl
                border
                bg-white
                hover:bg-gray-50
            "
        >
            →
        </a>
    </div>
</div>