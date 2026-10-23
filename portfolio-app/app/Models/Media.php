<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use LogsActivity;

    protected $table = 'media';

    protected $fillable = ['name', 'original_name', 'disk', 'path', 'mime_type', 'size', 'width', 'height', 'alt', 'uploaded_by'];

    protected static function booted(): void
    {
        // Remove the file together with its record
        static::deleted(fn (Media $media) => Storage::disk($media->disk)->delete($media->path));
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return media_url($this->path);
    }

    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $i ? 1 : 0).' '.$units[$i];
    }

    public function dimensions(): ?string
    {
        return $this->width ? "{$this->width} × {$this->height}" : null;
    }
}
