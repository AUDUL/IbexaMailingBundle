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
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('ibexamailing:install')]
class InstallCommand extends Command
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $schemaTool = new SchemaTool($this->entityManager);

        $ignore = $schemaTool->getCreateSchemaSql([]);

        $sqls = $schemaTool->getCreateSchemaSql([
            $this->entityManager->getClassMetadata(Broadcast::class),
            $this->entityManager->getClassMetadata(Campaign::class),
            $this->entityManager->getClassMetadata(ConfirmationToken::class),
            $this->entityManager->getClassMetadata(Mailing::class),
            $this->entityManager->getClassMetadata(MailingList::class),
            $this->entityManager->getClassMetadata(Registration::class),
            $this->entityManager->getClassMetadata(StatHit::class),
            $this->entityManager->getClassMetadata(User::class),
        ]);

        $sqls = array_diff($sqls, $ignore);

        foreach ($sqls as $sql) {
            $this->entityManager->getConnection()->executeQuery($sql);
        }

        $io->info('Tables created');

        return parent::SUCCESS;
    }
}