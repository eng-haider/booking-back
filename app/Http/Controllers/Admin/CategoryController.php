<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\Admin\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {
        $this->middleware(['permission:view_categories'])->only(['index', 'show', 'active', 'statistics']);
        $this->middleware(['permission:create_categories'])->only(['store']);
        $this->middleware(['permission:edit_categories'])->only(['update', 'toggleActive']);
        $this->middleware(['permission:delete_categories'])->only(['destroy']);
        $this->middleware(['permission:reorder_categories'])->only(['reorder']);
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryRepository->getAll($request->all());

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryRepository->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id, ['venues', 'venues.provider']);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $this->categoryRepository->update($category, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        // Check if category has venues
        if ($category->venues()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category. It has associated venues.',
            ], 422);
        }

        $this->categoryRepository->delete($category);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    /**
     * Get all active categories.
     */
    public function active(): JsonResponse
    {
        $categories = $this->categoryRepository->getActive();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Toggle category active status.
     */
    public function toggleActive(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $this->categoryRepository->toggleActive($category);

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|min:0',
        ]);

        $this->categoryRepository->reorder($request->input('order'));

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully.',
        ]);
    }

    /**
     * Get category statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $category = Category::withCount('venues')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $activeVenues = $category->venues()->where('status', 'active')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'statistics' => [
                    'total_venues' => $category->venues_count,
                    'active_venues' => $activeVenues,
                    'inactive_venues' => $category->venues_count - $activeVenues,
                ],
            ],
        ]);
    }
}
