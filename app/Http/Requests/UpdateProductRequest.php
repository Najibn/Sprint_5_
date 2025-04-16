<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'user_id' => 'sometimes|required|exists:users,id',
            'name' => 'sometimes|required|in:Fire Extinguisher,Smoke Detector,Fire Alarm',
            'type' => 'sometimes|required|in:water,foam,CO2,DCP',
            'type_capacity' => 'sometimes|required|string',
            'serial_number' => 'sometimes|required|string|unique:products,serial_number,' . $this->product->id,
            'status' => 'sometimes|required|string',
            'assigned_to' => $this->status === 'Needs Maintenance' ? 'required|exists:users,id' : 'nullable',
            'location' => 'sometimes|required|string',
        ];
    }
}
