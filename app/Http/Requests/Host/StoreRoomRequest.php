<?php

namespace App\Http\Requests\Host;

use App\Enums\GenderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'gender_type' => ['required', new Enum(GenderType::class)],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area_sqm' => ['nullable', 'numeric', 'min:1'],
            'has_lock' => ['boolean'],
            'has_window' => ['boolean'],
            'has_wardrobe' => ['boolean'],
            'has_desk' => ['boolean'],
            'has_ac' => ['boolean'],
            'has_heating' => ['boolean'],
            'has_balcony' => ['boolean'],
            'rules' => ['nullable', 'array'],
            'rules.*' => ['string', 'max:255'],
        ];
    }
}
