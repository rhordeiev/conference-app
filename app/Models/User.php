<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable
        = [
            'firstname',
            'lastname',
            'birthdate',
            'country_name',
            'type',
            'phone',
            'email',
            'password',
        ];

    protected $casts
        = [
            'birthdate' => 'date',
        ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_name', 'name');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden
        = [
            'remember_token',
            'updated_at',
            'created_at',
        ];

    public static function getUserById($id)
    {
        return self::all()->where('id', $id)->first();
    }

    public static function findUserByEmail($email)
    {
        return self::all()->where('email', $email)->first();
    }

    public function hasConference($id)
    {
        return $this->belongsToMany(
            Conference::class,
            'conference_user',
            'user_id',
            'conference_id'
        )->where('conference_id', $id)->first();
    }

    public function hasListenerReport($id)
    {
        return $this->belongsToMany(
            Report::class,
            'report_listener',
            'listener_id',
            'report_id'
        )->where('report_id', $id)->first();
    }

    public function hasReport($conferenceId): Model|HasMany|null
    {
        return $this->hasMany(
            Report::class,
            'creator_id'
        )->where('conference_id', $conferenceId)->first();
    }
}
