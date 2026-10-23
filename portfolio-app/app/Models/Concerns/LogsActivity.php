<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;

/**
 * Records created / updated / deleted events in the activity log.
 * Models can override activityLabel() to control the human-readable target.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => ActivityLog::record('created', $model));
        static::updated(function ($model) {
            // Ignore counters (e.g. views) and ordering-only saves
            $changed = array_diff(array_keys($model->getChanges()), ['updated_at', 'views', 'sort_order']);
            if ($changed) {
                ActivityLog::record('updated', $model, ['fields' => array_values($changed)]);
            }
        });
        static::deleted(fn ($model) => ActivityLog::record('deleted', $model));
    }

    public function activityLabel(): string
    {
        foreach (['title', 'name', 'client_name', 'company'] as $attribute) {
            $value = $this->getAttribute($attribute);
            if ($value) {
                return is_array($value) ? (string) ($value['en'] ?? reset($value)) : (string) $value;
            }
        }

        return class_basename($this).' #'.$this->getKey();
    }
}
