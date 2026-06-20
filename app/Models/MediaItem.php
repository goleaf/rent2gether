<?php

namespace App\Models;

use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'mediable_type',
        'mediable_id',
        'owner_user_id',
        'collection',
        'disk',
        'path',
        'thumb_path',
        'thumbnail_path',
        'mobile_path',
        'full_path',
        'original_filename',
        'mime',
        'mime_type',
        'size',
        'size_bytes',
        'width',
        'height',
        'alt_text',
        'caption_en',
        'caption_ru',
        'sort_order',
        'is_primary',
        'is_cover',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_cover' => 'boolean',
            'size' => 'integer',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('is_primary', true)
                ->orWhere('is_cover', true);
        });
    }

    public function imagePath(string $variant = 'mobile'): string
    {
        return match ($variant) {
            'thumb' => $this->thumb_path ?: $this->thumbnail_path ?: $this->mobile_path ?: $this->path,
            'full' => $this->full_path ?: $this->path,
            'original' => $this->path,
            default => $this->mobile_path ?: $this->thumb_path ?: $this->thumbnail_path ?: $this->path,
        };
    }

    public function imageUrl(string $variant = 'mobile'): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->imagePath($variant));
    }

    public function localizedCaption(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($locale) {
            'ru' => $this->caption_ru ?: $this->caption_en ?: $this->alt_text,
            default => $this->caption_en ?: $this->caption_ru ?: $this->alt_text,
        };
    }
}
