<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Products Management"
 * )
 */

class ProductController extends Controller
{
/**
 * @OA\Get(
 *     path="/products",
 *     tags={"Products"},
 *     summary="Get all products",
 *     description="Retrieve products based on user role",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Product")
 *         )
 *     )
 * )
 */

    //Display a listing of products based on user role.
    public function index()
    {
        $user = auth('api')->user();
        $products = $this->getProductsByRole($user);
        
        return ProductResource::collection($products);
    }


/**
 * @OA\Post(
 *     path="/products",
 *     tags={"Products"},
 *     summary="Create a new product",
 *     description="Create a new product (admin only)",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     )
 * )
 */

    // Store a newly created product.
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        
        $product = Product::create($validatedData);
        
        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }



/**
 * @OA\Get(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Get product by ID",
 *     description="Retrieve a specific product by ID",
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
 *     )
 * )
 */

    //Display the specified product.
    public function show(Product $product)
    {
        return new ProductResource($product);
    }


/**
 * @OA\Put(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Update product",
 *     description="Update product information (admin only)",
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
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     )
 * )
 */

    //Update the specified product.
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        
        $product->update($validatedData);
        
        return new ProductResource($product);
    }



/**
 * @OA\Delete(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Delete product",
 *     description="Delete a product (admin only)",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of product to delete",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Product deleted successfully"
 *     )
 * )
 */

    //Remove the specified product.
    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json(null, 204);
    }

    //Get products based on the users role.
    private function getProductsByRole($user)
    {
        return match ($user->role) {
            'customer' => Product::where('status', 'Active')->get(),
            'technician' => Product::where('status', 'Needs Maintenance')->get(),
            'admin' => Product::with('user')->get(),
            default => collect(),
        };
    }
}
