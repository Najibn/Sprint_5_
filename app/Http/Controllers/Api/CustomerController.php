<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Http\Resources\MaintenanceRecordResource;
use App\Http\Requests\UpdateCustomerProductRequest;

class CustomerController extends Controller
{
    /**
     * Get customer's products.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProducts()
    {
        $products = Product::where('user_id', Auth::id())->get();
        return ProductResource::collection($products);
    }

    /**
     * Get a specific product owned by the customer.
     *
     * @param  int  $id
     * @return \App\Http\Resources\ProductResource
     */
    public function getProduct($id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this product.');
        }

        return new ProductResource($product);
    }

    /**
     * Get maintenance records for a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProductMaintenanceRecords($id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view maintenance records for this product.');
        }

        $maintenanceRecords = $product->maintenanceRecords()->latest()->get();
    
        return MaintenanceRecordResource::collection($maintenanceRecords);
    }

    /**
     * Update a product owned by the customer.
     *
     * @param  \App\Http\Requests\UpdateCustomerProductRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\ProductResource
     */
    public function updateProduct(UpdateCustomerProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validatedData = $request->validated();

        $product->update($validatedData);

        
        return new ProductResource($product);
    }
}
