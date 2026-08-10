<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $author
 * @property string|null $description
 * @property numeric $price
 * @property string|null $purchase_link
 * @property string|null $download_link
 * @property int|null $image_id
 * @property string|null $status
 * @property int|null $available
 * @property string|null $created_at
 * @property-read Media|null $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereDownloadLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book wherePurchaseLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereTitle($value)
 *
 * @property string|null $pdf_file
 * @property bool $allow_pdf_download
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book wherePdfFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Book whereAllowPdfDownload($value)
 *
 * @mixin Model
 */
class Book extends Model
{
    protected $table = 'books';

    public $timestamps = false;

    protected $fillable = ['title', 'slug', 'author', 'description', 'price', 'purchase_link', 'download_link', 'pdf_file', 'allow_pdf_download', 'image_id', 'status', 'available'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && ! $model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (true) {
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
