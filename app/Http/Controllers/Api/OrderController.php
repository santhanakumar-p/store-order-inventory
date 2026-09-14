<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(IndexOrdersRequest $request): AnonymousResourceCollection
    {
        $customer = Customer::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($customer === null) {
            return OrderResource::collection(collect());
        }

        $orders = Order::query()
            ->whereBelongsTo($customer)
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request, OrderService $orderService): JsonResponse
    {
        $order = $orderService->create($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
