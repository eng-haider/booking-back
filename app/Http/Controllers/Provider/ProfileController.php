<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\UpdateProviderProfileRequest;
use App\Repositories\Provider\ProviderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProviderRepository $providerRepository
    ) {
        // Permission middleware removed - routes are already protected by auth:provider and provider middleware
        // All authenticated providers should be able to manage their own profile
    }

    /**
     * Get provider profile
     */
    public function show(Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findByUserId(
            $request->user()->id,
            ['user', 'venues.resources']
        );

        return response()->json([
            'success' => true,
            'data' => $provider,
        ]);
    }

    /**
     * Update provider profile
     */
    public function update(UpdateProviderProfileRequest $request): JsonResponse
    {
        $provider = $request->user()->provider;
        $data = $request->validated();

        $this->providerRepository->update($provider, $data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $provider->fresh(['user']),
        ]);
    }

    /**
     * Get provider statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $statistics = $this->providerRepository->getStatistics($provider);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
