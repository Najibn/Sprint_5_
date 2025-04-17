<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'type_capacity',
        'serial_number',
        'status',
        'location',
        'assigned_to'
    ];

 
    // Relationship: A product belongs to a user (customer/admin)
    public function user()
    {
        return $this->belongsTo(User::class); //'assigned_to'
    }

    // Relationship: A product can have many maintenance records
    public function maintenanceRecords()
    {
        return $this->hasMany(related:MaintenanceRecord::class);
    }

    public function assignedTechnician()
    {
    return $this->belongsTo(User::class, 'assigned_to');
    }
}
