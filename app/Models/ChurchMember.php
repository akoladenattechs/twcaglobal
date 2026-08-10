<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $date_of_birth
 * @property string $date_joined
 * @property string|null $membership_status
 * @property string|null $marital_status
 * @property string $gender
 * @property string|null $occupation
 * @property string|null $emergency_contact
 * @property string|null $emergency_phone
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereDateJoined($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereEmergencyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereMembershipStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChurchMember whereUpdatedAt($value)
 *
 * @mixin Model
 */
class ChurchMember extends Model
{
    protected $table = 'church_members';

    public $timestamps = true;

    protected $fillable = ['first_name', 'last_name', 'other_name', 'email', 'phone', 'address', 'city', 'state', 'country', 'nationality', 'date_of_birth', 'date_joined', 'membership_status', 'marital_status', 'gender', 'occupation', 'emergency_contact', 'emergency_phone', 'notes', 'center_id'];

    public function center()
    {
        return $this->belongsTo(CenterLocation::class, 'center_id');
    }
}
