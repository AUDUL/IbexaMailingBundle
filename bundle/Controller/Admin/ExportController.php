<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/export")
 */
class ExportController
{
    /**
     * @Route("/mailing-list/{mailinglist}", name="ibexamailing_mailinglist_export")
     *
     * @Security("is_granted('view', mailinglist)")
     */
    public function showAction(
        MailingList $mailinglist,
        Connection $connection,
    ): Response {
        $sql = 'SELECT u.USER_email, u.USER_first_name, u.USER_last_name, u.USER_gender,u.USER_birth_date,u.USER_phone,u.USER_zipcode,u.USER_city,u.USER_state,u.USER_country,u.USER_job_title,u.USER_company ,u.USER_status
from mailing_user u
inner join mailing_registrations nr on u.USER_id = nr.USER_id
where nr.ML_id = ?';

        return new StreamedResponse($this->generate($connection, $sql, [$mailinglist->getId()]), headers: ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="'.urlencode(str_replace(' ', '_', $mailinglist->getName())).'.csv"']);
    }

    /**
     * @Route("/users", name="ibexamailing_users_export")
     */
    public function exportUsersAction(Connection $connection): Response
    {
        $sql = 'SELECT u.USER_email, u.USER_first_name, u.USER_last_name, u.USER_gender,u.USER_birth_date,u.USER_phone,u.USER_zipcode,u.USER_city,u.USER_state,u.USER_country,u.USER_job_title,u.USER_company ,u.USER_status
from mailing_user u;';

        return new StreamedResponse($this->generate($connection, $sql), headers: ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="users.csv"']);
    }

    /**
     * @param array<mixed> $parameters
     */
    private function generate(Connection $connection, string $sql, array $parameters = []): \Closure
    {
        return function () use ($connection, $sql, $parameters) {
            $csv = fopen('php://output', 'w+');
            fputcsv($csv, ['Courriel', 'Prénom', 'Nom', 'Sexe', 'Date de naissance', 'Téléphone', 'Code postal', 'Ville', 'Etat', 'Pays', 'Profession', 'Société', 'status'], ';');

            foreach ($connection->iterateAssociative($sql, $parameters) as $user) {
                array_walk(
                    $user,
                    function (&$entry) {
                        $entry = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $entry);
                    }
                );
                fputcsv($csv, $user, ';');
            }
            fclose($csv);
        };
    }
}
