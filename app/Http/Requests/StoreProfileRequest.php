<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Profile::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', Rule::exists('organization_user', 'organization_id')->where(fn ($query) => $query->where('user_id', $this->user()->id)->where('status', 'active'))],
            'type' => ['required', Rule::in(['individual', 'business', 'employee', 'branch', 'restaurant', 'professional', 'freelancer'])],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('profiles', 'slug')],
            'designation' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'preferred_language' => ['required', Rule::in(['en', 'ar'])],
        ];
    }
}
