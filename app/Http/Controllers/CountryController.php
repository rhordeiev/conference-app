<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

class CountryController extends Controller
{
    public function all(): Collection
    {
        return Country::all();
    }
}
