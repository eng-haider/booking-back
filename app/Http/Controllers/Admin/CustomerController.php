<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Repositories\Admin\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {
        $this->middleware(['permission:view customers'])->only(['index', 'show', 'search', 'statistics', 'overallStatistics']);
        $this->middleware(['permission:create customers'])->only(['store']);
        $this->middleware(['permission:edit customers'])->only(['update', 'updateStatus', 'verifyEmail']);
        $this->middleware(['permission:delete customers'])->only(['destroy']);
    }

    /**
     * Display a listing of customers.
     */
    public function index(): JsonResponse
    {
        $customers = $this->customerRepository->getAll(request()->all());

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if email already exists
        if ($this->customerRepository->emailExists($data['email'])) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 422);
        }

        // Check if phone already exists
        if ($this->customerRepository->phoneExists($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already exists',
            ], 422);
        }

        $customer = $this->customerRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer->load('user'),
        ], 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id, ['user', 'bookings.venue']);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $statistics = $this->customerRepository->getStatistics($customer);

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'statistics' => $statistics,
            ],
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $data = $request->validated();

        // Check if email already exists (excluding current customer)
        if (isset($data['email']) && $this->customerRepository->emailExists($data['email'], $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 422);
        }

        // Check if phone already exists (excluding current customer)
        if (isset($data['phone']) && $this->customerRepository->phoneExists($data['phone'], $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already exists',
            ], 422);
        }

        $this->customerRepository->update($customer, $data);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer->fresh(['user']),
        ]);
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $this->customerRepository->delete($customer);

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }

    /**
     * Update customer status.
     */
    public function updateStatus(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $status = request()->input('status');

        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 422);
        }

        $this->customerRepository->updateStatus($customer, $status);

        return response()->json([
            'success' => true,
            'message' => 'Customer status updated successfully',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * Verify customer email.
     */
    public function verifyEmail(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        if ($customer->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
            ], 422);
        }

        $this->customerRepository->verifyEmail($customer);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * Get customer statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findById($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $statistics = $this->customerRepository->getStatistics($customer);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Search customers.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 422);
        }

        $customers = $this->customerRepository->search($query);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get overall customer statistics.
     */
    public function overallStatistics(): JsonResponse
    {
        $statistics = $this->customerRepository->getOverallStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
