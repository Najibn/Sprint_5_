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
     * Get products assigned to the technician.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAssignedProducts()
    {
        $technician = auth('api')->user();
        $products = Product::where('assigned_to', $technician->id)
            ->where('status', 'Needs Maintenance')
            ->get();
        
        return ProductResource::collection($products);
    }

    /**
     * Get maintenance records assigned to the technician.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getMaintenanceRecords()
    {
        $technician = auth('api')->user();
        $maintenanceRecords = MaintenanceRecord::where('technician_id', $technician->id)
            ->with('product')
            ->get();
        
        return MaintenanceRecordResource::collection($maintenanceRecords);
    }

    /**
     * Update a maintenance record.
     *
     * @param  \App\Http\Requests\UpdateMaintenanceRecordRequest  $request
     * @param  \App\Models\MaintenanceRecord  $maintenanceRecord
     * @return \App\Http\Resources\MaintenanceRecordResource
     */
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
     * Get details of a specific product assigned to the technician.
     *
     * @param  int  $id
     * @return \App\Http\Resources\ProductResource
     */
    public function getProduct($id)
    {
        $technician = auth('api')->user();
        $product = Product::where('assigned_to', $technician->id)
            ->where('id', $id)
            ->firstOrFail();
        
        return new ProductResource($product);
    }

    /**
     * Get maintenance history for a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
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
