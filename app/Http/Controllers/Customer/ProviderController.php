<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Repositories\Customer\ProviderRepository;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    protected $providerRepository;

    public function __construct(ProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * Display a listing of providers.
     */
    public function index()
    {
        try {
            $providers = $this->providerRepository->getAll();

            return response()->json([
                'success' => true,
                'data' => $providers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch providers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified provider.
     */
    public function show($id)
    {
        try {
            $provider = $this->providerRepository->findById($id);
            $statistics = $this->providerRepository->getStatistics($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'owner' => $provider,
                    'statistics' => $statistics,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Search providers.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $providers = $this->providerRepository->search($query);

            return response()->json([
                'success' => true,
                'data' => $providers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
