<?php

namespace App\Http\Resources\Swagger;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     required={"name", "email", "password", "role", "phone"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
 *     @OA\Property(property="role", type="string", enum={"admin", "customer", "technician"}, example="customer"),
 *     @OA\Property(property="phone", type="string", example="+1234567890")
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="name", type="string", enum={"Fire Extinguisher", "Smoke Detector", "Fire Alarm"}),
 *     @OA\Property(property="type", type="string", enum={"water", "foam", "CO2", "DCP"}),
 *     @OA\Property(property="type_capacity", type="string"),
 *     @OA\Property(property="serial_number", type="string"),
 *     @OA\Property(property="status", type="string", enum={"Active", "Expired", "Needs Maintenance"}),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="assigned_to", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="MaintenanceRecord",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="product_id", type="integer"),
 *     @OA\Property(property="technician_id", type="integer"),
 *     @OA\Property(property="maintenance_date", type="string", format="date"),
 *     @OA\Property(property="status", type="string", enum={"completed", "pending", "overdue"}),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="role", type="string", enum={"admin", "customer", "technician"}),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Schemas
{
    // This class is only created for Swagger annotations
}
