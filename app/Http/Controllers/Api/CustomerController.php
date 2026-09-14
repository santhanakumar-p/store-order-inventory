<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function search(SearchCustomerRequest $request): CustomerResource|JsonResponse
    {
        $customer = Customer::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($customer === null) {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        return new CustomerResource($customer);
    }
}
