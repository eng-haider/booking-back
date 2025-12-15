<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;

class AmenityController extends Controller
{
    /**
     * Display a listing of all amenities.
     */
    public function index(): JsonResponse
    {
        $amenities = Amenity::select('id', 'name_en', 'name_ar', 'icon')
            ->orderBy('name_en')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $amenities,
        ]);
    }
}
