<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ManuscriptVersion;
use App\Models\Publication;
use App\Models\Work;

class ActivityLogObserver
{
    protected array $modelsToObserve = [
        Work::class,
        ManuscriptVersion::class,
        Publication::class,
    ];

    public function created($model): void
    {
        ActivityLog::log('created', null, $model, [
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]);
    }

    public function updated($model): void
    {
        $changes = $model->getChanges();
        if (empty($changes)) {
            return;
        }

        ActivityLog::log('updated', null, $model, [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'changes' => $changes,
        ]);
    }

    public function deleted($model): void
    {
        ActivityLog::log('deleted', null, $model, [
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]);
    }
}
