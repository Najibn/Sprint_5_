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
 * @OA\Get(
 *     path="/customer/products",
 *     tags={"Customer"},
 *     summary="Get customer's products",
 *     description="Retrieve all products belonging to the authenticated customer",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Product")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User must be/have customer role"
 *     )
 * )
 */
    //Get customer's products.
    public function getProducts()
    {
        $products = Product::where('user_id', Auth::id())->get();
        return ProductResource::collection($products);
    }

/**
 * @OA\Get(
 *     path="/customer/products/{id}",
 *     tags={"Customer"},
 *     summary="Get customer's product by ID",
 *     description="Retrieve a specific product belonging to the authenticated customer",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of product to return",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Product does not belong to customer"
 *     )
 * )
 */

   //Get a specific product owned/assigned by the customer.
    public function getProduct($id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this product.');
        }

        return new ProductResource($product);
    }


/**
 * @OA\Get(
 *     path="/customer/products/{id}/maintenance_records",
 *     tags={"Customer"},
 *     summary="Get maintenance records for a product",
 *     description="Retrieve maintenance records for a specific product belonging to the authenticated customer",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of product",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/MaintenanceRecord")
 *         )
 *     )
 * )
 */

    
    //Get maintenance records for a specific product.
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
 * @OA\Put(
 *     path="/customer/products/{id}",
 *     tags={"Customer"},
 *     summary="Update customer's product",
 *     description="Update a product belonging to the authenticated customer",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of product to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="location", type="string", example="New Location")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     )
 * )
 */

    //Update a product owned by a customer.
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
