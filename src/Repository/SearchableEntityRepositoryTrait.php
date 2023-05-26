<?php

namespace App\Repository;

use App\Search\SearchableEntitySearch;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template T
 */
trait SearchableEntityRepositoryTrait
{
    /**
     * Recherche d'entités, avec pagination
     *
     * @param SearchableEntitySearch $search
     * @return Paginator<T>
     */
    public function search(SearchableEntitySearch $search): Paginator
    {
        // Construction de la requête
        $query = $this->getSearchQuery($search);

        // Gestion pages
        $query->setFirstResult(($search->page-1)*$search->limit);
        if ($search->limit) $query->setMaxResults($search->limit);

        // Paginator
        return new Paginator($query->getQuery(), $fetchJoinCollection = true);
    }

    abstract function getSearchQuery(SearchableEntitySearch $search): QueryBuilder;

}
