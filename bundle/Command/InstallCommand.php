<?php

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use CodeRhapsodie\IbexaMailingBundle\Entity\StatHit;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('ibexamailing:install')]
class InstallCommand extends Command
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = new SchemaTool($this->entityManager);

        $sqls = $schemaTool->getCreateSchemaSql([
            new ClassMetadata(Broadcast::class),
            new ClassMetadata(Campaign::class),
            new ClassMetadata(ConfirmationToken::class),
            new ClassMetadata(Mailing::class),
            new ClassMetadata(MailingList::class),
            new ClassMetadata(Registration::class),
            new ClassMetadata(StatHit::class),
            new ClassMetadata(User::class),
        ]);

        foreach ($sqls as $sql) {
            $this->entityManager->getConnection()->executeQuery($sql);
        }
        return parent::SUCCESS;
    }
}