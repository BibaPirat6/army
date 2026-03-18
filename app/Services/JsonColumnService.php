<?php

namespace App\Services;

class JsonColumnService
{
    public function parseLines(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        $lines = array_values(
            array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $text))
            )
        );

        // ✅ Превращаем в МАССИВ строк (это и будет валидный JSON после json_encode)
        return $lines;
    }

   
  
}
