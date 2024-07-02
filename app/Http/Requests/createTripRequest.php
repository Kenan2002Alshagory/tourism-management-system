<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTripRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
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
            'itineraries' => 'required|array',
            'itineraries.*.places' => 'required|array',
            'itineraries.*.places.*.place_id' => 'required|exists:places,id',
            'itineraries.*.day_num' => 'required|integer',
            'itineraries.*.places.*.description' => 'nullable|string',
        ];
    }
}
