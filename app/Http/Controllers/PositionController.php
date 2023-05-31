<?php

namespace App\Http\Controllers;

use App\Models\Positions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PositionController extends Controller
{
    public function getPositions()
    {
        $positions = Positions::select("id", "name")->get();

        if (empty($positions->first())) {
            return response([
                "success" => false,
                "positions" => "Positions not found"
        ], Response::HTTP_NOT_FOUND);
        }

        return response([
            "success" => true,
            "positions" => $positions
        ]);
    }
}
