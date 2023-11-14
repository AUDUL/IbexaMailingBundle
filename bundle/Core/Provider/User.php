<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Provider;

use CodeRhapsodie\IbexaMailingBundle\Entity\User as UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class User
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPagerFilters(array $filters = [], int $page = 1, int $limit = 25): Pagerfanta
    {
        $repo = $this->entityManager->getRepository(UserEntity::class);
        $adapter = new QueryAdapter($repo->createQueryBuilderForFilters($filters));
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function getStatusesData(array $filters = []): array
    {
        unset($filters['status']);
        $repo = $this->entityManager->getRepository(UserEntity::class);
        $total = 0;
        $statuses = [];
        foreach (UserEntity::STATUSES as $status) {
            $statuses[$status] = $repo->countByFilters($filters + ['status' => $status]);

            $total += $statuses[$status];
        }

        return ['count' => $total, 'results' => $statuses];
    }
}
