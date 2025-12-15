<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProviderRequest;
use App\Http\Requests\Admin\UpdateProviderRequest;
use App\Repositories\Admin\ProviderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
    public function __construct(
        protected ProviderRepository $providerRepository
    ) {
        $this->middleware(['permission:view providers'])->only(['index', 'show', 'statistics']);
        $this->middleware(['permission:create providers'])->only(['store']);
        $this->middleware(['permission:edit providers'])->only(['update', 'updateStatus']);
        $this->middleware(['permission:delete providers'])->only(['destroy']);
    }

    /**
     * Display a listing of providers.
     */
    public function index(): JsonResponse
    {
        $providers = $this->providerRepository->getAll(request()->all());

        return response()->json([
            'success' => true,
            'data' => $providers,
        ]);
    }

    /**
     * Store a newly created provider.
     */
    public function store(StoreProviderRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while ($this->providerRepository->slugExists($data['slug'])) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $provider = $this->providerRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Provider created successfully',
            'data' => $provider->load('user'),
        ], 201);
    }

    /**
     * Display the specified provider.
     */
    public function show(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id, ['user', 'venues']);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
            ], 404);
        }

        $statistics = $this->providerRepository->getStatistics($provider);

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'statistics' => $statistics,
            ],
        ]);
    }

    /**
     * Update the specified provider.
     */
    public function update(UpdateProviderRequest $request, int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
            ], 404);
        }

        $data = $request->validated();

        // Handle slug update
        if (isset($data['slug']) && $data['slug'] !== $provider->slug) {
            if ($this->providerRepository->slugExists($data['slug'], $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug already exists',
                ], 422);
            }
        }

        $this->providerRepository->update($provider, $data);

        return response()->json([
            'success' => true,
            'message' => 'Provider updated successfully',
            'data' => $provider->fresh(['user', 'venues']),
        ]);
    }

    /**
     * Remove the specified provider.
     */
    public function destroy(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
            ], 404);
        }

        $this->providerRepository->delete($provider);

        return response()->json([
            'success' => true,
            'message' => 'Provider deleted successfully',
        ]);
    }

    /**
     * Update provider status.
     */
    public function updateStatus(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
            ], 404);
        }

        $status = request()->input('status');

        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 422);
        }

        $this->providerRepository->updateStatus($provider, $status);

        return response()->json([
            'success' => true,
            'message' => 'Provider status updated successfully',
            'data' => $provider->fresh(),
        ]);
    }

    /**
     * Get provider statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
            ], 404);
        }

        $statistics = $this->providerRepository->getStatistics($provider);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
