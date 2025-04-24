<?php

namespace App\Http\Controllers\Api;

use App\Models\MaintenanceRecord;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\MaintenanceRecordResource;
use App\Http\Requests\StoreMaintenanceRecordRequest;
use App\Http\Requests\UpdateMaintenanceRecordRequest;

class MaintenanceRecordController extends Controller
{

/**
 * @OA\Get(
 *     path="/maintenance_records",
 *     tags={"Maintenance Records"},
 *     summary="Get all maintenance records",
 *     description="Retrieve all maintenance records (admin only)",
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

    //Display a listing of maintenance records.
    public function index()
    {
        $maintRecords = MaintenanceRecord::with('product', 'technician')->get();
        return MaintenanceRecordResource::collection($maintRecords);
    }


/**
 * @OA\Post(
 *     path="/maintenance_records",
 *     tags={"Maintenance Records"},
 *     summary="Create a new maintenance record",
 *     description="Create a new maintenance record (admin only)",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceRecord")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Maintenance record created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceRecord")
 *     )
 * )
 */

   //Store a newly created maintenance record.
    public function store(StoreMaintenanceRecordRequest $request)
    {
        $validatedData = $request->validated();
        
        $maintRecord = MaintenanceRecord::create($validatedData);
        
        return (new MaintenanceRecordResource($maintRecord))
            ->response()
            ->setStatusCode(201);
    }


/**
 * @OA\Get(
 *     path="/maintenance_records/{id}",
 *     tags={"Maintenance Records"},
 *     summary="Get maintenance record by ID",
 *     description="Retrieve a specific maintenance record by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of maintenance record to return",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceRecord")
 *     )
 * )
 */

    //Display the specified maintenance record.
    public function show(MaintenanceRecord $maintRecord)
    {
        return new MaintenanceRecordResource($maintRecord);
    }


/**
 * @OA\Put(
 *     path="/maintenance_records/{id}",
 *     tags={"Maintenance Records"},
 *     summary="Update maintenance record",
 *     description="Update maintenance record information (admin only)",
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
 *     )
 * )
 */

    //Update the specified maintenance record.
    public function update(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintRecord)
    {
        $validatedData = $request->validated();
        
        $maintRecord->update($validatedData);
        
        return new MaintenanceRecordResource($maintRecord);
    }


/**
 * @OA\Delete(
 *     path="/maintenance_records/{id}",
 *     tags={"Maintenance Records"},
 *     summary="Delete maintenance record",
 *     description="Delete a maintenance record (admin only)",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of maintenance record to delete",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Maintenance record deleted successfully"
 *     )
 * )
 */

    // Remove the specified maintenance record.
    public function destroy(MaintenanceRecord $maintRecord)
    {
        $maintRecord->delete();
        
        return response()->json(null, 204);
    }
}
