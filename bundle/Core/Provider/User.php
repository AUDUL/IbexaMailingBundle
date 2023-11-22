<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Provider;

use CodeRhapsodie\IbexaMailingBundle\Entity\User as UserEntity;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class User
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @param array<mixed> $filters
     *
     * @return Pagerfanta<\CodeRhapsodie\IbexaMailingBundle\Entity\User>
     */
    public function getPagerFilters(array $filters = [], int $page = 1, int $limit = 25): Pagerfanta
    {
        $adapter = new QueryAdapter($this->userRepository->createQueryBuilderForFilters($filters));
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getStatusesData(array $filters = []): array
    {
        unset($filters['status']);
        $total = 0;
        $statuses = [];
        foreach (UserEntity::STATUSES as $status) {
            $statuses[$status] = $this->userRepository->countByFilters($filters + ['status' => $status]);

            $total += $statuses[$status];
        }

        return ['count' => $total, 'results' => $statuses];
    }
}
