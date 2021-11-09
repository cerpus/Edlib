<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->load('licenses');

        return [
            'id' => $this->id,
            'site' => $this->site,
            'content_id' => $this->content_id,
            'name' => $this->name,
            'licenses' => LicenseResource::collection($this->licenses)
        ];
    }
}
