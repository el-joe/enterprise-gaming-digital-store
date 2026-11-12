<?php

namespace App\Http\Resources\Admin\Cms;

use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'path' => $this->path,
            'url' => app(MediaUrls::class)->url($this->disk, $this->path),
            'original_filename' => $this->original_filename,
            'mime' => $this->mime,
            'size_bytes' => $this->size_bytes,
            'width' => $this->width,
            'height' => $this->height,
            'alt' => $this->alt,
            'conversions' => $this->conversions,
            'folder' => $this->folder,
            'source' => $this->source->value,
            'license_note' => $this->license_note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
