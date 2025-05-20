<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

if (!function_exists('pagination')) {
    function pagination(LengthAwarePaginator $paginatedData, $data, $search = null): array
    {
        $pages = [];
        for ($i = 1; $i <= $paginatedData->lastPage(); $i++) {

            $url = $paginatedData->url($i);
            if ($search) {
                $url .= '&search=' . urlencode($search);
            }
            $pages[] = [
                'page' => $i,
                'url' => $url
            ];
        }
        // Pagination
        return [
            'current_page' => $paginatedData->currentPage(),
            'first_page' => $paginatedData->url(1),
            'last_page' =>$paginatedData->lastPage(),
            'last_page' => $paginatedData->url($paginatedData->lastPage()),
            'per_page' => $paginatedData->perPage(),
            'delivery_data' => $data,
            'pages' => $pages, // Include all page URLs
        ];

    }
}