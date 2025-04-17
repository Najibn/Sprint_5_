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
     * Display a listing of maintenance records.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $maintenanceRecords = MaintenanceRecord::with('product', 'technician')->get();
        return MaintenanceRecordResource::collection($maintenanceRecords);
    }

    /**
     * Store a newly created maintenance record.
     *
     * @param  \App\Http\Requests\StoreMaintenanceRecordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreMaintenanceRecordRequest $request)
    {
        $validatedData = $request->validated();
        
        $maintenanceRecord = MaintenanceRecord::create($validatedData);
        
        return (new MaintenanceRecordResource($maintenanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified maintenance record.
     *
     * @param  \App\Models\MaintenanceRecord  $maintenanceRecord
     * @return \App\Http\Resources\MaintenanceRecordResource
     */
    public function show(MaintenanceRecord $maintenanceRecord)
    {
        return new MaintenanceRecordResource($maintenanceRecord);
    }

    /**
     * Update the specified maintenance record.
     *
     * @param  \App\Http\Requests\UpdateMaintenanceRecordRequest  $request
     * @param  \App\Models\MaintenanceRecord  $maintenanceRecord
     * @return \App\Http\Resources\MaintenanceRecordResource
     */
    public function update(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintenanceRecord)
    {
        $validatedData = $request->validated();
        
        $maintenanceRecord->update($validatedData);
        
        return new MaintenanceRecordResource($maintenanceRecord);
    }

    /**
     * Remove the specified maintenance record.
     *
     * @param  \App\Models\MaintenanceRecord  $maintenanceRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(MaintenanceRecord $maintenanceRecord)
    {
        $maintenanceRecord->delete();
        
        return response()->json(null, 204);
    }
}
