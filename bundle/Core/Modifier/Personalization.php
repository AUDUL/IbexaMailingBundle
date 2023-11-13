<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Modifier;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User;

class Personalization
{
    public function modify(Mailing $mailing, User $user, string $html, array $options = []): string
    {
        $map = [
            '##EMAIL##' => $user->getEmail(),
            '##FIRSTNAME##' => $user->getFirstName(),
            '##LASTNAME##' => $user->getLastName(),
            '##COUNTRY##' => $user->getCountry(),
            '##CITY##' => $user->getCity(),
            '##COMPANY##' => $user->getCompany(),
            '##GENDER##' => $user->getGender(),
            '##JOBTITLE##' => $user->getJobTitle(),
            '##PHONE##' => $user->getPhone(),
            '##ZIPCODE##' => $user->getZipcode(),
        ];

        return str_replace(array_keys($map), array_values($map), $html);
    }
}
