<?php

namespace UserBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use UserBundle\Service\MeService;

/**
 * @author Vitaly Dergunov
 */
class MeController extends AbstractController implements ServiceSubscriberInterface
{
    /**
     * @var MeService
     */
    private $meService;

    /**
     * MeService constructor.
     *
     * @param MeService $meService
     */
    public function __construct(MeService $meService)
    {
        $this->meService = $meService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function RClientsNetAction(Request $request): JsonResponse
    {
        try {
            $clientToken = $this->meService->getClientToken($request);

            return $this->json([
                'accessToken' => $clientToken,
            ]);
        } catch (\RuntimeException $exc) {
            return $this->json([
                'error' => $exc->getMessage(),
            ]);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return [
            'router' => '?'.RouterInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
        ];
    }
}
