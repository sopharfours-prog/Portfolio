<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'cover_url' => $this->coverUrl(),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id, 'url' => $image->url(), 'is_cover' => $image->is_cover,
                'width' => $image->width, 'height' => $image->height,
            ])),
            'public_url' => route('projects.show', $this->resource),
        ];
    }
}
