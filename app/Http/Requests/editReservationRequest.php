<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class editReservationRequest extends FormRequest
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
            'reservationId'=>'required|integer|exists:reservations,id',
            'travelers_num'=>'required|integer|gt:0',
        ];
    }
}
