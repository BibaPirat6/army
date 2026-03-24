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

    // app/Services/JsonService.php
    public function handleFiles($files, string $fieldName, array $removedIndexes = []): ?array
    {
        if (empty($files)) {
            return null;
        }

        $uploadedFiles = [];

        foreach ($files as $index => $file) {
            if (! in_array($index, $removedIndexes) && $file->isValid()) {
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs("uploads/{$fieldName}", $filename, 'public');
                $uploadedFiles[] = $path;
            }
        }

        return ! empty($uploadedFiles) ? $uploadedFiles : null;
    }
}
