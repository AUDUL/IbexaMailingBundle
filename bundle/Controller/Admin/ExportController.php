<?php

declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Controller\Admin;

use Doctrine\DBAL\Connection;
use Novactive\Bundle\eZMailingBundle\Entity\MailingList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/export")
 */
class ExportController
{
    /**
     * @Route("/mailing-list/{mailinglist}", name="novaezmailing_mailinglist_export")
     * @Security("is_granted('view', mailinglist)")
     */
    public function showAction(MailingList $mailinglist,
                               Connection  $connection,
    )
    {
        $sql = "SELECT u.USER_email, u.USER_first_name, u.USER_last_name, u.USER_gender, u.USER_status
from novaezmailing_user u
inner join novaezmailing_registrations nr on u.USER_id = nr.USER_id
where nr.ML_id = ?";

        return new StreamedResponse(function () use ($connection, $sql, $mailinglist) {
            $csv = fopen('php://output', 'w+');
            fputcsv($csv, ['email', 'first_name', 'last_name', 'salutation', 'status'], ";");

            foreach ($connection->iterateAssociative($sql, [$mailinglist->getId()]) as $user) {
                fputcsv($csv, $user, ";");
            }
            fclose($csv);
        }, headers: ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="mailing-list.csv"']);
    }
}