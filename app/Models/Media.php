<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $file_name
 * @property string $file_type
 * @property int $file_size
 * @property string|null $uploaded_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploadedAt($value)
 *
 * @mixin Model
 */
class Media extends Model
{
    protected $table = 'media';

    public $timestamps = false;

    protected $fillable = ['title', 'description', 'file_name', 'file_type', 'file_size'];

    public function getUrlAttribute(): string
    {
        $name = (string) $this->file_name;
        if ($name === '') {
            return '';
        }

        // All media rows store an absolute URL (R2/CDN or another external
        // source) — return it as-is. Any other value can only be legacy data
        // that is not deployed, so return '' and let views render their
        // placeholder instead of a broken link.
        if (str_starts_with($name, 'http://') || str_starts_with($name, 'https://') || str_starts_with($name, '//')) {
            return $name;
        }

        return '';
    }
}
