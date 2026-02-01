<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DepartmentCollection extends ResourceCollection
{

    public $collects = DepartmentResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => $this->when($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator, function () {
                return [
                    'current_page' => $this->resource->currentPage(),
                    'from' => $this->resource->firstItem(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'to' => $this->resource->lastItem(),
                    'total' => $this->resource->total(),
                    'has_more_pages' => $this->resource->hasMorePages(),
                    'pagination_type' => config('app.pagination_type', 'pagination'),
                ];
            }),
            'links' => $this->when($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator, function () {
                return [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ];
            }),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'success' => true,
        ];
    }
}
