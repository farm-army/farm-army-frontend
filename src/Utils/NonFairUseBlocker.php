<?php declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class NonFairUseBlocker implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$event->getRequest()->attributes->has('address')) {
            return;
        }

        $strtolower = strtolower($event->getRequest()->attributes->get('address'));
        if ($strtolower === 'd19030ea41cf9cf814f9053d6526f36aee2461a1' || $strtolower === '0xd19030ea41cf9cf814f9053d6526f36aee2461a1') {
            $event->setResponse(new Response('', 429));
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
