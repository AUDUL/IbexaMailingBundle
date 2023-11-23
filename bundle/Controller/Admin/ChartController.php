<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Controller\Admin;

use CodeRhapsodie\IbexaMailingBundle\Core\Utils\ChartDataBuilder;
use CodeRhapsodie\IbexaMailingBundle\Repository\BroadcastRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\StatHitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ChartController extends AbstractController
{
    public function browserChart(int $broadcastId, BroadcastRepository $broadcastRepository, StatHitRepository $statHitRepository): Response
    {
        $item = $broadcastRepository->find($broadcastId);

        $data = $statHitRepository->getBrowserMapCount([$item]);

        $chartBuilder = new ChartDataBuilder('Browser Repartition', 'doughnut');
        $chartBuilder->addDataSet(array_values($data), array_keys($data));

        return $this->render('@IbexaMailing/admin/chart/generic.html.twig', ['chart' => $chartBuilder()]);
    }

    public function osChart(int $broadcastId, BroadcastRepository $broadcastRepository, StatHitRepository $statHitRepository): Response
    {
        $item = $broadcastRepository->find($broadcastId);

        $data = $statHitRepository->getOSMapCount([$item]);

        $chartBuilder = new ChartDataBuilder('OS Repartition', 'doughnut');
        $chartBuilder->addDataSet(array_values($data), array_keys($data));

        return $this->render('@IbexaMailing/admin/chart/generic.html.twig', ['chart' => $chartBuilder()]);
    }

    public function urlChart(int $broadcastId, BroadcastRepository $broadcastRepository, StatHitRepository $statHitRepository): Response
    {
        $item = $broadcastRepository->find($broadcastId);

        $data = $statHitRepository->getURLMapCount([$item]);

        $chartBuilder = new ChartDataBuilder('URLs Clicked Repartition', 'pie');
        $chartBuilder->addDataSet(array_values($data), array_keys($data));

        return $this->render('@IbexaMailing/admin/chart/generic.html.twig', ['chart' => $chartBuilder()]);
    }

    public function openedChart(int $broadcastId, BroadcastRepository $broadcastRepository, StatHitRepository $statHitRepository): Response
    {
        $item = $broadcastRepository->find($broadcastId);
        $openedCount = $statHitRepository->getOpenedCount([$item]);
        $broadcastCount = $item->getEmailSentCount();
        $data = [
            'Opened' => $openedCount,
            'Not Opened' => $broadcastCount - $openedCount,
        ];

        $chartBuilder = new ChartDataBuilder('Opened emails', 'pie');
        $chartBuilder->addDataSet(array_values($data), array_keys($data));

        return $this->render('@IbexaMailing/admin/chart/generic.html.twig', ['chart' => $chartBuilder()]);
    }

    public function openedTimeChart(int $broadcastId, BroadcastRepository $broadcastRepository, StatHitRepository $statHitRepository): Response
    {
        $item = $broadcastRepository->find($broadcastId);

        $data = $statHitRepository->getOpenedCountPerDay([$item]);

        $chartBuilder = new ChartDataBuilder('Opened per day', 'bar');
        $values = array_values($data);
        $chartBuilder->addDataSet($values, array_keys($data), array_pad([], \count($values), '#36a2eb'));
        $chartBuilder->addDataSet($values, array_keys($data), ['#ff6384'], 'line');

        return $this->render('@IbexaMailing/admin/chart/generic.html.twig', ['chart' => $chartBuilder()]);
    }
}
