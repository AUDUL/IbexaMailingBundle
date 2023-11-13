<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\DataFixtures;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\MailingList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class MailingListFixtures extends Fixture
{
    public const FIXTURE_COUNT_MAILINGLIST = 10;

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        for ($i = 1; $i <= self::FIXTURE_COUNT_MAILINGLIST; ++$i) {
            $mailingList = new MailingList();
            $mailingList->setNames(
                [
                    'fre-FR' => $faker->unique()->sentence(6).'( FR )',
                    'eng-GB' => $faker->unique()->sentence(6).'( GB )',
                    'eng-US' => $faker->unique()->sentence(6).'( US )',
                ]
            );
            $mailingList->setWithApproval($faker->boolean(60));
            $manager->persist($mailingList);
            $this->addReference("mailing-list-{$i}", $mailingList);
        }
        $manager->flush();
    }
}
