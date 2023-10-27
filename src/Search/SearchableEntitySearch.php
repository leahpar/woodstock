<?php

namespace App\Search;


class SearchableEntitySearch
{
    use HydrateTrait;

    public function __construct(?array $data = null)
    {
        if ($data) $this->hydrate($data);
    }

    // Pagination
    public int $page = 1;
    public int $limit = 50;

    // Tri
    public ?string $tri = null;
    public ?string $order = null;

    // Recherche générale
    public ?string $search = null;
}
