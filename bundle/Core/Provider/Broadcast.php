<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Provider;

use Carbon\Carbon;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Broadcast as BroadcastEntity;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use DateTime;
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
            ->setUpdated(new DateTime());
        $this->store($broadcast);

        return $broadcast;
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
