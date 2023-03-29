<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedCollection extends ResourceCollection
{

    /**
     * An array to store pagination data that comes from paginate() method.
     * @var array
     */
    protected $pagination;

    /**
     * PaginatedCollection constructor.
     *
     * @param mixed $resource paginated resource using paginate method on models or relations.
     */
    public function __construct($resource)
    {
        // /Users/abutabikh/Work/naqi_api/vendor/laravel/framework/src/Illuminate/Support/Arr.php

        $this->pagination = [
            'current_page' => $resource->currentPage(),
            'first_page_url' => $resource->url(1),
            'from' => $resource->firstItem(),
            'last_page' => $resource->lastPage(),
            'last_page_url' => $resource->url($resource->lastPage()),
            'next_page_url' => $resource->nextPageUrl(),
            'path' => $resource->path(),
            'per_page' => $resource->perPage(),
            'prev_page_url' => $resource->previousPageUrl(),
            'to' => $resource->lastItem(),
            'total' => $resource->total(),
        ];

        $resource = $resource->getCollection();

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     * now we have data and pagination info.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // our resources
            'data' => $this->collection,

            // pagination data
            'pagination' => $this->pagination
        ];
    }
}
