<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'organization_id' => $this->organization_id,
            'type' => $this->type,
            'slug' => $this->slug,
            'name' => $this->name,
            'designation' => $this->designation,
            'company_name' => $this->company_name,
            'category' => $this->category,
            'tagline' => $this->tagline,
            'about' => $this->about,
            'preferred_language' => $this->preferred_language,
            'status' => $this->status,
            'style' => $this->style,
            'published_at' => $this->published_at,
            'contacts' => $this->whenLoaded('contacts'),
            'social_links' => $this->whenLoaded('socialLinks'),
            'services' => $this->whenLoaded('services'),
            'products' => $this->whenLoaded('products'),
            'links' => ['public' => $this->status === 'published' ? route('public.profile', $this->slug) : null],
        ];
    }
}
