<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return ProductResource::collection($products);
    }

    public function lowStock(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->lowStock()
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return ProductResource::collection($products);
    }
}
