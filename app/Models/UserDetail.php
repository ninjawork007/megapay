<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table   = 'user_details';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'country_id',
        'email_verification',
        'phone_verification_code',
        'two_step_verification_type',
        'two_step_verification_code',
        // 'google2fa_secret',
        'two_step_verification',
        'last_login_at',
        'last_login_ip',
        'city',
        'state',
        'address_1',
        'address_2',
        'default_currency',
        'timezone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    // protected $hidden = [
    //     'google2fa_secret',
    // ];

    /**
     * Ecrypt the user's google_2fa secret.
     */
    // public function setGoogle2faSecretAttribute($value)
    // {
    //     $this->attributes['google2fa_secret'] = encrypt($value);
    // }

    /**
     * Decrypt the user's google_2fa secret.
     */
    // public function getGoogle2faSecretAttribute($value)
    // {
    //     return decrypt($value);
    // }
}
