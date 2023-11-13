<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\UserImport;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Import\User;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Provider\User as UserProvider;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User as UserEntity;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Form\ImportType;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Form\MailingListType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Core\Helper\TranslationHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/mailinglist")
 */
class MailingListController
{
    /**
     * @Route("/show/{mailingList}/{status}/{page}/{limit}", name="ibexamailing_mailinglist_show",
     *                                              defaults={"page":1, "limit":10, "status":"all"})
     * @Security("is_granted('view', mailingList)")
     * @Template()
     */
    public function showAction(
        MailingList  $mailingList,
        UserProvider $provider,
        string       $status = 'all',
        int          $page = 1,
        int          $limit = 10
    ): array
    {
        $filers = [
            'mailingLists' => [$mailingList],
            'status' => 'all' === $status ? null : $status,
        ];

        return [
            'pager' => $provider->getPagerFilters($filers, $page, $limit),
            'item' => $mailingList,
            'statuses' => $provider->getStatusesData($filers),
            'currentStatus' => $status,
        ];
    }

    /**
     * @Route("", name="ibexamailing_mailinglist_index")
     * @Template()
     */
    public function indexAction(EntityManagerInterface $entityManager): array
    {
        $repo = $entityManager->getRepository(MailingList::class);

        return ['items' => $repo->findAll()];
    }

    /**
     * @Route("/edit/{mailinglist}", name="ibexamailing_mailinglist_edit")
     * @Route("/create", name="ibexamailing_mailinglist_create")
     * @Security("is_granted('edit', mailinglist)")
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function editAction(
        ?MailingList           $mailinglist,
        Request                $request,
        RouterInterface        $router,
        FormFactoryInterface   $formFactory,
        EntityManagerInterface $entityManager,
        TranslationHelper      $translationHelper
    )
    {
        if (null === $mailinglist) {
            $mailinglist = new MailingList();
            $languages = array_filter($translationHelper->getAvailableLanguages());
            $mailinglist->setNames(array_combine($languages, array_pad([], count($languages), '')));
        }

        $form = $formFactory->create(MailingListType::class, $mailinglist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mailinglist
                ->setUpdated(new DateTime());
            $entityManager->persist($mailinglist);
            $entityManager->flush();

            return new RedirectResponse(
                $router->generate('ibexamailing_mailinglist_show', ['mailingList' => $mailinglist->getId()])
            );
        }

        return [
            'item' => $mailinglist,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/delete/{mailinglist}", name="ibexamailing_mailinglist_remove")
     * @Security("is_granted('edit', mailinglist)")
     */
    public function deleteAction(
        MailingList            $mailinglist,
        EntityManagerInterface $entityManager,
        RouterInterface        $router
    ): RedirectResponse
    {
        $entityManager->remove($mailinglist);
        $entityManager->flush();

        return new RedirectResponse($router->generate('ibexamailing_mailinglist_index'));
    }

    /**
     * @Route("/import/{mailinglist}", name="ibexamailing_mailinglist_import")
     * @Security("is_granted('edit', mailinglist)")
     * @Template()
     */
    public function importAction(
        MailingList          $mailinglist,
        FormFactoryInterface $formFactory,
        Request              $request,
        User                 $importer,
        ValidatorInterface   $validator
    ): array
    {
        $userImport = new UserImport();
        $form = $formFactory->create(ImportType::class, $userImport);
        $form->handleRequest($request);
        $count = null;
        $errorList = null;
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($importer->rowsIterator($userImport) as $index => $row) {
                try {
                    $user = $importer->hydrateUser($row);
                    $user
                        ->setUpdated(new DateTime());
                    $errors = $validator->validate($user);
                    if (count($errors) > 0) {
                        $errorList["Line {$index}"] = $errors;
                        continue;
                    }
                    if ($user->getStatus() === UserEntity::REMOVED) {
                        continue;
                    }
                    $importer->registerUser($user, $mailinglist);
                    ++$count;
                } catch (\Exception $e) {
                    $errorList["Line {$index}"] = [['message' => $e->getMessage()]];
                }
            }
        }

        return [
            'count' => $count,
            'error_list' => $errorList,
            'item' => $mailinglist,
            'form' => $form->createView(),
        ];
    }
}
