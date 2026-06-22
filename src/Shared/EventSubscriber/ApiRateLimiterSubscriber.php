<?php

namespace App\Shared\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class ApiRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $apiGlobalLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $clientIp = $request->getClientIp() ?? 'unknown';

        $limiter = $this->apiGlobalLimiter->create($clientIp);
        $limit = $limiter->consume(1);

        if ($limit->isAccepted()) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'success' => false,
            'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
        ], Response::HTTP_TOO_MANY_REQUESTS));
    }
}