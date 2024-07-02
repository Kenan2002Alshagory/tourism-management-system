<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FCreateTripRequest extends FormRequest
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
            'trip_name' => 'required|string|max:255',
            'start_date' => 'required|date|before:end_date|after:today',
            'end_date' => 'required|date|after:today',
            'from' => 'required|string|max:255',
            'duration' => 'required|integer',
            'destination' => 'required|string|max:255',
            'guide_name' => 'required|string|max:255',
            'travelers_num' => 'required|integer',
            'trip_type' => 'required|string|max:255',
            'trip_price' => 'required|numeric',
            'trip_description' => 'required|string',
            'trip_image' => 'required',
        ];
    }
}
