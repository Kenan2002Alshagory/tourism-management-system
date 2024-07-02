<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SCreateTripRequest extends FormRequest
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
            'trip_id' => 'required|exists:trips,id',
            'itineraries' => 'required|array',
            'itineraries.*.places' => 'required|array',
            'itineraries.*.places.*.place_id' => 'required|exists:places,id',
            'itineraries.*.day_num' => 'required|integer',
            'itineraries.*.places.*.description' => 'nullable|string',
        ];
    }
}
