<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property string|null $button_text
 * @property string|null $button_link
 * @property int|null $image_id
 * @property int|null $video_id
 * @property string|null $video_url
 * @property int|null $display_order
 * @property string $status
 * @property string|null $created_at
 * @property-read Media|null $media
 * @property-read Media|null $videoMedia
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereButtonLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageSlider whereVideoUrl($value)
 *
 * @mixin Model
 */
class HomepageSlider extends Model
{
    protected $table = 'homepage_sliders';

    public $timestamps = false;

    protected $fillable = ['image_id', 'video_id', 'video_url', 'display_order', 'status'];

    public function media()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function videoMedia()
    {
        return $this->belongsTo(Media::class, 'video_id');
    }
}
