<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'name' => 'required|in:Fire Extinguisher,Smoke Detector,Fire Alarm',
            'type' => 'required|in:water,foam,CO2,DCP',
            'type_capacity' => 'required|string',
            'serial_number' => 'required|string|unique:products,serial_number',
            'status' => 'required|string|in:Active,Expired,Needs Maintenance',
            'assigned_to' => $this->status === 'Needs Maintenance' ? 'required|exists:users,id' : 'nullable',
            'location' => 'required|string',
        ];
    }
}
