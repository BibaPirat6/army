<div class="w-24 shrink-0 border-r bg-gray-50">

    @for($hour = 0; $hour < 24; $hour++)

        <div class="
            h-[120px]
            border-b
            relative
            text-xs
            text-gray-500
        ">
            <div class="absolute top-0 left-3">

                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00

            </div>
        </div>

    @endfor

</div>