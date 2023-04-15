<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ConferenceHelper
{
    public static function addFieldToConference(Collection $conferences, User $user, string $column): void
    {
        foreach ($conferences as $conference) {
            $user->hasConference($conference['id']) ?
                $conference[$column] = true
                : $conference[$column] = false;
        }
    }
}
