<?php

declare(strict_types=1);

namespace IbexaMailingBundle;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231113113924 extends AbstractMigration
{
    private const TABLES = [
        'novaezmailing_user' => 'mailing_user',
        'novaezmailing_mailing_list' => 'mailing_mailing_list',
        'novaezmailing_campaign' => 'mailing_campaign',
        'novaezmailing_mailing' => 'mailing_mailing',
        'novaezmailing_campaign_mailinglists_destination' => 'mailing_campaign_mailinglists_destination',
        'novaezmailing_confirmation_token' => 'mailing_confirmation_token',
        'novaezmailing_broadcast' => 'mailing_broadcast',
        'novaezmailing_stats_hit' => 'mailing_stats_hit',
        'novaezmailing_registrations' => 'mailing_registrations'
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $oldTable => $newTable) {
            $result = $this->connection->executeQuery("SHOW TABLE STATUS LIKE '$oldTable';");

            if (\count($result->fetchAllAssociative()) === 0) {
                continue;
            }

            $this->addSql("INSERT INTO $newTable SELECT * FROM $oldTable");
        }

        foreach (\array_reverse(self::TABLES) as $oldTable => $newTable) {
            $this->addSql("DROP TABLE $oldTable");
        }
    }

    public function down(Schema $schema): void
    {

    }
}
