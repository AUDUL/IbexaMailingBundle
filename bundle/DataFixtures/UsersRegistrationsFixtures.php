<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\DataFixtures;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class UsersRegistrationsFixtures extends Fixture implements DependentFixtureInterface
{
    public const FIXTURE_COUNT_USER = 100;

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        for ($i = 1; $i <= self::FIXTURE_COUNT_USER; ++$i) {
            $user = new User();
            $user
                ->setEmail($faker->unique()->email)
                ->setBirthDate($faker->dateTimeThisCentury)
                ->setCity($faker->city)
                ->setCompany($faker->company)
                ->setCountry($faker->countryCode)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setGender($faker->title)
                ->setJobTitle($faker->jobTitle)
                ->setPhone($faker->phoneNumber)
                ->setState($faker->text)
                ->setZipcode($faker->postcode)
                ->setStatus($faker->randomElement(User::STATUSES))
                ->setOrigin($faker->randomElement(['site', 'import']));

            $nbRegistrations = $faker->numberBetween(0, MailingListFixtures::FIXTURE_COUNT_MAILINGLIST);
            for ($j = 0; $j <= $nbRegistrations; ++$j) {
                $registration = new Registration();
                $mailingListIndex = $faker->numberBetween(1, MailingListFixtures::FIXTURE_COUNT_MAILINGLIST);
                $mailingList = $this->getReference("mailing-list-{$mailingListIndex}");
                /* @var MailingList $mailingList */
                $registration->setMailingList($mailingList);
                $registration->setApproved($mailingList->isWithApproval() ? $faker->boolean : true);
                $user->addRegistration($registration);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MailingListFixtures::class,
        ];
    }
}
