<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $hidden
        = [
            'created_at',
            'updated_at',
        ];

    public function conferences(): HasMany
    {
        return $this->hasMany(Conference::class, 'country_name', 'name');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_name', 'name');
    }
}
