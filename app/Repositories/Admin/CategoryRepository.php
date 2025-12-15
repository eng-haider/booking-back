<?php

namespace App\Repositories\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CategoryRepository
{
    /**
     * Get all categories with filters, sorting, and pagination
     */
    public function getAll(array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Category::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('slug'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::scope('active'),
            ])
            ->allowedSorts(['id', 'name', 'sort_order', 'created_at', 'updated_at'])
            ->allowedIncludes(['venues', 'venues.provider'])
            ->defaultSort('sort_order', 'name')
            ->paginate($perPage);
    }

    /**
     * Find category by ID
     */
    public function findById(int $id, array $relations = []): ?Category
    {
        return Category::with($relations)->find($id);
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug, array $relations = []): ?Category
    {
        return Category::with($relations)->where('slug', $slug)->first();
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set default sort_order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = Category::max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        return Category::create($data);
    }

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): bool
    {
        // Auto-generate slug if name changed and slug not provided
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $category->update($data);
    }

    /**
     * Delete a category
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Get all active categories
     */
    public function getActive(): Collection
    {
        return Category::active()->ordered()->get();
    }

    /**
     * Reorder categories
     */
    public function reorder(array $order): bool
    {
        foreach ($order as $id => $sortOrder) {
            Category::where('id', $id)->update(['sort_order' => $sortOrder]);
        }

        return true;
    }

    /**
     * Toggle category active status
     */
    public function toggleActive(Category $category): bool
    {
        return $category->update(['is_active' => !$category->is_active]);
    }
}
