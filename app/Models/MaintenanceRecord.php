<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'technician_id',
        'maintenance_date',
        'status',
        'notes',
    ];

   //casting date column to datetime
     protected $casts = [
        'maintenance_date' => 'datetime',
     ];

     // Relationship: A maintenance record belongs to a product
     public function product()
     {
         return $this->belongsTo(Product::class);
     }
 
     // Relationship: A maintenance record is performed by a technician (user)
     public function technician()
     {
         return $this->belongsTo(User::class, 'technician_id');
     }

}
