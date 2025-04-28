<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Search by district name and city name
        $results = District::whereTranslationLike('name', "%{$query}%")
            ->orWhereHas('city', function ($q) use ($query) {
                $q->whereTranslationLike('name', "%{$query}%");
            })
            ->get();

        return response()->json($results);
    }
}
