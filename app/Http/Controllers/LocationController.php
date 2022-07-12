<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PharIo\Manifest\Author;

class LocationController extends Controller
{
    function get()
    {
        return response()->json(['locationData' => Location::with('user')->paginate(10)]);
    }

    function getByInstructor()
    {
        DB::statement("SET sql_mode=''");

        $locationData = Location::whereHas(
            'user',
            fn ($q) =>
            $q->whereHas(
                'enrollment',
                fn ($q) => $q->whereHas(
                    'course',
                    fn ($q) => $q->where('author_id', Auth::user()->id)
                )
            )
        )
            ->groupBy('country')
            ->get(['country', DB::raw('count(*) as total'), 'country_code', 'language']);

        $languageData = Location::whereHas(
            'user',
            fn ($q) =>
            $q->whereHas(
                'enrollment',
                fn ($q) => $q->whereHas(
                    'course',
                    fn ($q) => $q->where('author_id', Auth::user()->id)
                )
            )
        )
            ->groupBy('language')
            ->get([DB::raw('count(*) as total'), 'language']);

        return response()->json(compact('locationData', 'languageData'));
    }

    function getByAdmin()
    {
        DB::statement("SET sql_mode=''");

        $locationData = Location::whereHas(
            'user',
            fn ($q) =>
            $q->has(
                'enrollment'
            )
        )
            ->groupBy('country')
            ->get(['country', DB::raw('count(*) as total'), 'country_code', 'language']);

        $languageData = Location::whereHas(
            'user',
            fn ($q) =>
            $q->has(
                'enrollment',
            )
        )
            ->groupBy('language')
            ->get([DB::raw('count(*) as total'), 'language']);

        return response()->json(compact('locationData', 'languageData'));
    }
}
