<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MotivationalQoute;
use Laravel\Sanctum\HasApiTokens;
use League\Fractal\Scope;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_otp',
        'role',
        'email_verified_at',
        'device_type',
        'device_token',
        'social_type',
        'social_token',
        'last_name',
        'address',
        'latitude',
        'longitude',
        'category_id',
        'description',
        'customer_profile_id',
        'payment_profile_id',
        'profile_image',
        'is_forgot',
        'is_verified',
        'is_blocked',
        'is_push_notify',
        'is_profile_completed',
        'phone',
        'sub_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_push_notify' => 'integer',
    ];

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function scopeNearbyBusinesses($query, $latitude, $longitude, $radius, $limit)
    {
        return
            // $query->selectRaw('*,
            //     ( 3959 * acos( cos( radians(?) ) *
            //     cos( radians( latitude ) )
            //     * cos( radians( longitude ) - radians(?)
            //     ) + sin( radians(?) ) *
            //     sin( radians( latitude ) ) )
            //     ) AS distance', [$latitude, $longitude, $latitude])
            //     ->having('distance', '<=', $radius)
            //     ->orderBy('distance', 'asc')
            //     ->limit($limit)
            $query->select('*')
                ->selectRaw("(3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                // ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                ->having('distance', '<=', $radius)
                ->where('role', 'Business')
                ->whereNotNull('sub_id')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereNotNull('sub_id')
                ->orderBy('distance')
                ->limit($limit);
        ;
    }


    /**
     * get modified by User object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function userFavWords()
    {
        return $this->hasMany(UserFavoriteWords::class, 'word_dictionary_id');
    }

    /**
     * get modified by User object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function userSchedule()
    {
        return $this->hasMany(Schedule::class, 'owner_id');
    }


    /**
     * get modified by User object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function userFeedback()
    {
        return $this->hasMany(UserFeedback::class, 'user_id');
    }



    /**
     * get modified by User object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function receiver_notifications()
    {
        return $this->hasMany(Notification::class, 'receiver_id``');
    }



    /**
     * get modified by User object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sender_notifications()
    {
        return $this->hasMany(Notification::class, 'sender_id``');
    }

}
