<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['path', 'visitor', 'project_id', 'viewed_on'];

    protected function casts(): array
    {
        return ['viewed_on' => 'date'];
    }
}
