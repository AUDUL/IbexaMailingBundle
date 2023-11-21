<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Import;

use Carbon\Carbon;
use CodeRhapsodie\IbexaMailingBundle\Core\DataHandler\UserImport;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use CodeRhapsodie\IbexaMailingBundle\Entity\User as UserEntity;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class User
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserRepository $userRepository)
    {
    }

    public function rowsIterator(UserImport $userImport): \Generator
    {
        $encoding = Csv::guessEncoding($userImport->getFile()->getPathname());
        $reader = new Csv();
        $reader->setInputEncoding($encoding);
        $spreadsheet = $reader->load($userImport->getFile()->getPathname());

        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator() as $row) {
            if ($row->getRowIndex() === 1) {
                continue;
            }
            $cells = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            yield $cells;
        }
    }

    /**
     * Hydrate user.
     *
     * @param array<int, mixed> $cells
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function hydrateUser(array $cells): UserEntity
    {
        $user = new UserEntity();
        if (isset($cells[0])) {
            $user = $this->userRepository->findOneBy(['email' => $cells[0]]);
            if (!$user instanceof UserEntity) {
                $user = new UserEntity();
                $user->setEmail(filter_var($cells[0], \FILTER_SANITIZE_EMAIL));
            }
        }
        if (isset($cells[1])) {
            $user->setFirstName($cells[1]);
        }
        if (isset($cells[2])) {
            $user->setLastName($cells[2]);
        }
        if (isset($cells[3])) {
            $user->setGender($cells[3]);
        }
        if (isset($cells[4])) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', (string) $cells[4]);
            } catch (\Exception) {
                $date = Date::excelToDateTimeObject((int) $cells[4]);
            }
            $user->setBirthDate($date);
        }
        if (isset($cells[5])) {
            $user->setPhone((string) $cells[5]);
        }
        if (isset($cells[6])) {
            $user->setZipcode((string) $cells[6]);
        }
        if (isset($cells[7])) {
            $user->setCity($cells[7]);
        }
        if (isset($cells[8])) {
            $user->setState($cells[8]);
        }
        if (isset($cells[9])) {
            $user->setCountry($cells[9]);
        }
        if (isset($cells[10])) {
            $user->setJobTitle($cells[10]);
        }
        if (isset($cells[11])) {
            $user->setCompany($cells[11]);
        }
        $user->setRestricted(false);
        $user->setOrigin('import');

        if ($user->getStatus() !== UserEntity::REMOVED) {
            $user->setStatus(UserEntity::CONFIRMED);
        }

        return $user;
    }

    /**
     * Register the user to the MailingListRepository.
     */
    public function registerUser(UserEntity $user, MailingList $mailingList): UserEntity
    {
        $registration = new Registration();
        $registration
            ->setUser($user)
            ->setMailingList($mailingList)
            ->setApproved(true)
            ->setUpdated(new \DateTime());
        $user->addRegistration($registration);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
