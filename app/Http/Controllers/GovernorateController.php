<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use Illuminate\Http\JsonResponse;

class GovernorateController extends Controller
{
    /**
     * Get all active governorates
     */
    public function index(): JsonResponse
    {
        $governorates = Governorate::active()
            ->orderBy('name_en')
            ->get(['id', 'name_ar', 'name_en']);

        return response()->json([
            'success' => true,
            'data' => $governorates,
        ]);
    }
}
