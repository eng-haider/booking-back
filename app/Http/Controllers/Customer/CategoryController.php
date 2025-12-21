<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\CategoryRepository;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    /**
     * Get all active categories (public endpoint).
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->getActive();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
