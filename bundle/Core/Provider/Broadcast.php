<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Provider;

use Carbon\Carbon;
use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast as BroadcastEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

class Broadcast
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function start(Mailing $mailing, string $html): BroadcastEntity
    {
        $broadcast = new BroadcastEntity();
        $broadcast
            ->setMailing($mailing)
            ->setStarted(Carbon::now())
            ->setHtml($html)
            ->setUpdated(new \DateTime());
        $this->store($broadcast);

        return $broadcast;
    }

    public function increment(int $broadcastId, int $increment = 1): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(BroadcastEntity::class, 'b')
            ->set('b.emailSentCount', 'b.emailSentCount + :increment')
            ->where('b.id = :id')
            ->setParameter('id', $broadcastId)
            ->setParameter('increment', $increment, ParameterType::INTEGER)
            ->getQuery()
            ->execute();
    }

    public function end(BroadcastEntity $broadcast): void
    {
        $broadcast->setEnded(Carbon::now());
        $this->store($broadcast);
    }

    public function store(BroadcastEntity $broadcast): void
    {
        $this->entityManager->persist($broadcast);
        $this->entityManager->flush();
    }
}
