<?php

namespace App\Libraries;

class CustomPaginate
{
    public static function build($paginate)
    {
        $paginate = $paginate->toArray();
        return [
            'meta' => [
                'count' => $paginate['to'],
                'total' => $paginate['total'],
            ],
            'links' => [
                'first' => (isset($paginate['first_page_url']) ? $paginate['first_page_url'] : null),
                'last'  => (isset($paginate['last_page_url']) ? $paginate['last_page_url'] : null),
                'next'  => (isset($paginate['next_page_url']) ? $paginate['next_page_url'] : null),
                'prev'  => (isset($paginate['prev_page_url']) ? $paginate['prev_page_url'] : null),
            ],
            'data' => $paginate['data']
        ];
    }
}