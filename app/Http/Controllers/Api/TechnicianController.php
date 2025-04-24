<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use App\Http\Resources\MaintenanceRecordResource;
use App\Http\Requests\UpdateMaintenanceRecordRequest;

class TechnicianController extends Controller
{    

/**
 * @OA\Get(
 *     path="/technician/assigned_products",
 *     tags={"Technician"},
 *     summary="Get technician's assigned products",
 *     description="Retrieve products assigned to the authenticated technician",
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

    //Get products assigned to the technician.
    public function getAssignedProducts()
    {
        $technician = auth('api')->user();
        $products = Product::where('assigned_to', $technician->id)
            ->where('status', 'Needs Maintenance')
            ->get();
        
        return ProductResource::collection($products);
    }


/**
 * @OA\Get(
 *     path="/technician/maintenance_records",
 *     tags={"Technician"},
 *     summary="Get technician's maintenance records",
 *     description="Retrieve maintenance records assigned to the authenticated technician",
 *     security={{"bearerAuth":{}}},
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

    //Get maint. records assigned to the technician.
    public function getMaintenanceRecords()
    {
        $technician = auth('api')->user();
        $maintenanceRecords = MaintenanceRecord::where('technician_id', $technician->id)
            ->with('product')
            ->get();
        
        return MaintenanceRecordResource::collection($maintenanceRecords);
    }


/**
 * @OA\Put(
 *     path="/technician/maintenance_records/{id}",
 *     tags={"Technician"},
 *     summary="Update maintenance record",
 *     description="Update a maintenance record assigned to the authenticated technician",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of maintenance record to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceRecord")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Maintenance record updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceRecord")
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Maintenance record not assigned to technician"
 *     )
 * )
 */

    //Update a maint. record.
    public function updateMaintenanceRecord(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintenanceRecord)
    {
        $technician = auth('api')->user();
        
        // Check if this maintenance record belongs to the authenticated technician
        if ($maintenanceRecord->technician_id !== $technician->id) {
            return response()->json([
                'message' => 'You are not authorized to update this maintenance record.'
            ], 403);
        }
        
        $validatedData = $request->validated();
        $maintenanceRecord->update($validatedData);
        
        // If status changed to completed, update product status as well
        if ($request->has('status') && $request->status === 'completed') {
            $product = $maintenanceRecord->product;
            if ($product && $product->status === 'Needs Maintenance') {
                $product->status = 'Active';
                $product->save();
            }
        }
        
        return new MaintenanceRecordResource($maintenanceRecord->fresh()->load('product'));
    }



/**
 * @OA\Get(
 *     path="/technician/products/{id}",
 *     tags={"Technician"},
 *     summary="Get assigned product details",
 *     description="Retrieve details of a product assigned to the authenticated technician",
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
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     )
 * )
 */

    
    //Get details of a specific product assigned to the technician.
    public function getProduct($id)
    {
        $technician = auth('api')->user();
        $product = Product::where('assigned_to', $technician->id)
            ->where('id', $id)
            ->firstOrFail();
        
        return new ProductResource($product);
    }


/**
 * @OA\Get(
 *     path="/technician/products/{id}/maintenance-history",
 *     tags={"Technician"},
 *     summary="Get product maintenance history",
 *     description="Retrieve maintenance history for a product assigned to the authenticated technician",
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

    
    //Get maintenance history for a specific product.
    public function getProductMaintenanceHistory($id)
    {
        $technician = auth('api')->user();
        $product = Product::where('assigned_to', $technician->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $maintenanceRecords = $product->maintenanceRecords()
            ->latest()
            ->get();
        
        return MaintenanceRecordResource::collection($maintenanceRecords);
    }
}
