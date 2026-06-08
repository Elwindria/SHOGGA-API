<?php

namespace App\Admin\Controller;

use App\Admin\Log\DTO\LogFilter;
use App\Admin\Log\Service\LogReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LogController extends AbstractController
{
    #[Route('/dashboard/logs', name: 'dashboard_logs', methods: ['GET'])]
    public function index(Request $request, LogReader $logReader): Response
    {
        $filter = LogFilter::fromRequest($request);
        $total = $logReader->count($filter);

        return $this->render('admin/log/index.html.twig', [
            'logs' => $logReader->read($filter),
            'files' => $logReader->getAvailableFiles(),
            'presets' => $logReader->getPresets(),
            'filter' => $filter,
            'total' => $total,
            'totalPages' => max(1, (int) ceil($total / $filter->limit)),
        ]);
    }
}