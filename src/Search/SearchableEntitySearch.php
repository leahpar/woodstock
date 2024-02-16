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
    public int $count = 0; // Nombre de résultats de la recherche

    // Tri
    public ?string $tri = null;
    public ?string $order = null;

    // Recherche générale
    public ?string $search = null;

    public function getTarget(): string
    {
        // Ex : App\Search\ReferenceSearch -> Reference
        return substr(substr(strrchr(static::class, '\\'), 1), 0, -6);
    }
}
