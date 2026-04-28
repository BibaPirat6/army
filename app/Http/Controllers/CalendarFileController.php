<?php

namespace App\Http\Controllers;

use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CalendarFileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('tasks/temp', 'public');

        $taskFile = TaskFile::create([
            'task_id'       => $request->task_id ?: null, // null вместо 0
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'file_id' => $taskFile->id,
        ]);
    }

    public function attachToTask(Request $request, $taskId)
    {
        // Привязываем временные файлы (где task_id IS NULL) к задаче
        TaskFile::whereNull('task_id')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->update(['task_id' => $taskId]);

        return response()->json(['success' => true]);
    }

    public function destroy(TaskFile $file)
    {
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        $file->delete();

        return response()->json(['success' => true]);
    }
}