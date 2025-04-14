<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function index()
    {
        return response()->json(Direction::all());
    }

    public function show($id)
    {
        $direction = Direction::find($id);
        if ($direction) {
            return response()->json($direction);
        }
        return response()->json(['error' => __('messages.direction_not_found')], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'direction' => 'required|string|unique:directions,direction',
            'price' => 'required|numeric|min:0',
        ]);

        $direction = Direction::updateOrCreate(['direction' => $request->direction], ['price' => $request->price]);

        return response()->json([
            'message' => __('messages.direction_added'),
            'direction' => $direction,
        ]);
    }
}
