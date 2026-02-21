<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListBookingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'car_id' => 'integer|exists:cars,id',
            'date_from' => 'date|date_format:Y-m-d',
            'date_to' => 'date|date_format:Y-m-d|after_or_equal:date_from',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Максимальное количество записей на странице — 100',
            'date_to.after_or_equal' => 'Дата окончания должна быть после даты начала',
        ];
    }
}
