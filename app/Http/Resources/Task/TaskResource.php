<?php

namespace App\Http\Resources\Task;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\File\FileResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->user),
            'file' => new FileResource($this->file),
            'status' => Task::getStatuses()[$this->status],
        ];
    }
}
