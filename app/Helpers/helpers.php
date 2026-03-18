<?php

if (!function_exists('format_for_textarea')) {
    function format_for_textarea($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        // Если это строка
        if (is_string($value)) {
            // Пробуем декодировать как JSON
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode("\n", $decoded);
            }
            
            // Если не JSON, возвращаем как есть (уже текст с переносами)
            return $value;
        }
        
        // Если это массив
        if (is_array($value)) {
            return implode("\n", $value);
        }
        
        return (string) $value;
    }
}