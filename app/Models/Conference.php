<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conference extends Model
{
    use HasFactory;

    protected $fillable
        = [
            'title',
            'date',
            'lat',
            'lng',
            'start_time',
            'end_time',
            'country_name',
        ];

    protected $casts
        = [
            'date' => 'date',
        ];

    public static function getConferenceById($id)
    {
        return self::all()->where('id', $id)->first();
    }

    public static function searchByTitle($title): Collection|array
    {
        return self::query()->where('title', 'like', "${title}%")->get();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_name', 'name');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function conferenceUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'conference_user',
            'conference_id',
            'user_id'
        );
    }

    public function getUsersById($id): Collection
    {
        return $this->belongsToMany(
            User::class,
            'conference_user',
            'conference_id',
            'user_id'
        )->where('conference_user.conference_id', '=', $id)->get(['users.*']);
    }

    public function getCategory()
    {
        return $this->belongsToMany(
            Category::class,
            'category_conference',
            'conference_id',
            'category_id'
        )->first();
    }

    public function conferenceCategory(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_conference',
            'conference_id',
            'category_id'
        );
    }

    public function conferenceReports(): HasMany
    {
        return $this->hasMany(
            Report::class
        );
    }
}
