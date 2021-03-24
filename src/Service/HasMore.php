<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

trait HasMore
{

    public function hasMore(ServiceEntityRepository $repository, int $lastQueryCount, int $limit, int $offset): bool
    {
        if ($lastQueryCount < $limit) {
            return false;
        }

        $result = $repository->findBy([], ['created_at' => 'desc'], $limit, $offset);
        return count($result) >= 1;
    }
}