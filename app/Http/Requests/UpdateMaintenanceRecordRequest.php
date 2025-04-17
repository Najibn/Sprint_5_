<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|required|exists:products,id',
            'technician_id' => 'sometimes|required|exists:users,id',
            'maintenance_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:completed,pending,overdue',
            'notes' => 'nullable|string',
        ];
    }
}
