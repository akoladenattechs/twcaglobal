<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $member_id
 * @property string $position
 * @property string|null $department
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $status
 * @property numeric|null $salary
 * @property string|null $responsibilities
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read ChurchMember|null $member
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereResponsibilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchStaff whereUpdatedAt($value)
 *
 * @mixin Model
 */
class ChurchStaff extends Model
{
    protected $table = 'church_staff';

    public $timestamps = false;

    protected $fillable = ['member_id', 'position', 'department', 'start_date', 'end_date', 'status', 'salary', 'responsibilities'];

    public function member()
    {
        return $this->belongsTo(ChurchMember::class, 'member_id');
    }
}
