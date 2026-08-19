<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\Task;
use App\Models\Work;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    public function search(string $query, int $limit = 10): Collection
    {
        $results = collect();

        $works = Work::where('title_public', 'like', "%{$query}%")
            ->orWhere('title_internal', 'like', "%{$query}%")
            ->orWhere('author_name', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(fn ($work) => [
                'title' => $work->title_public,
                'description' => 'Obra: '.$work->author_name,
                'url' => "/admin/works/{$work->id}/edit",
                'type' => 'work',
            ]);

        $publications = Publication::whereHas('work', fn ($q) => $q->where('title_public', 'like', "%{$query}%"))
            ->orWhere('asin', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(fn ($pub) => [
                'title' => $pub->work->title_public ?? 'Sin título',
                'description' => 'Publicación: '.$pub->platform->name ?? '',
                'url' => "/admin/publications/{$pub->id}/edit",
                'type' => 'publication',
            ]);

        $tasks = Task::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(fn ($task) => [
                'title' => $task->title,
                'description' => 'Tarea: '.($task->work->title_public ?? 'Sin obra'),
                'url' => "/admin/tasks/{$task->id}/edit",
                'type' => 'task',
            ]);

        return $results->concat($works)->concat($publications)->concat($tasks)->take($limit);
    }
}
