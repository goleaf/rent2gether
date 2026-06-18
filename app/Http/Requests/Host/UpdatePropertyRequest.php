<?php

namespace App\Http\Requests\Host;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(PropertyType::class)],
            'status' => ['required', new Enum(PropertyStatus::class)],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:50'],
            'apartment' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'integer'],
            'has_elevator' => ['boolean'],
            'show_exact_address' => ['boolean'],
            'nearest_transport' => ['nullable', 'string', 'max:500'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:50'],
            'rules' => ['nullable', 'array'],
            'rules.*' => ['string', 'max:255'],
        ];
    }
}
