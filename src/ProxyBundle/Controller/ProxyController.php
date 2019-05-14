<?php

namespace ProxyBundle\Controller;

use ProxyBundle\DTO\RequestDTO;
use ProxyBundle\Event\ProxyRequestEvent;
use ProxyBundle\Events;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vitaly Dergunov
 */
class ProxyController extends AbstractController
{
    /**
     * @param Request                  $request
     * @param EventDispatcherInterface $dispatcher
     *
     * @return JsonResponse
     */
    public function proxyAction(Request $request, EventDispatcherInterface $dispatcher)
    {
        $proxyType = $this->getParameter('proxy.type');

        $proxyEvent = new ProxyRequestEvent(RequestDTO::createFromRequest($request), $proxyType);

        foreach ([
                    Events::CHECK_URI,
                    Events::CHECK_PROXY_TYPE,
                    Events::AGENCY_AND_CLIENT_IS_RECEIVED,
                    Events::SETUP_CREDENTIALS,
                    Events::CREATE_USER_IF_NOT_EXISTS,
                    Events::CHECK_TOKEN,
                    Events::CHECK_HTTP_METHOD,
                    Events::CHECK_ACCESS_METHODS,
                    Events::CHECK_COMPANY,
                    Events::PROXY_REQUEST,
                ] as $event) {
            $dispatcher->dispatch($event, $proxyEvent);

            if ($proxyEvent->hasResponse()) {
                return $proxyEvent->getResponse();
            }
        }

        return $this->json([
            'error' => Response::HTTP_NO_CONTENT,
        ]);
    }
}
