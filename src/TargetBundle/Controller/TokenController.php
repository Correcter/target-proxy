<?php

namespace TargetBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Templating\EngineInterface;
use TargetBundle\Entity\TargetClientToken;
use TargetBundle\Form\TokenType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class TokenController extends AbstractController implements ServiceSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * MeController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function tokenAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetClientToken::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'token',
                'id' => $method->getId(),
                'title' => mb_substr($method->getAccessToken(), 0, 100).'...',
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/token',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetToken = new TargetClientToken();

        $form = $this->createForm(TokenType::class, $targetToken);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetToken);
            $this->entityManager->flush();

            return $this->redirectToRoute('token.manage', [
                'tokenId' => $targetToken->getId(),
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/token',
        ]);
    }

    /**
     * @param null|int $tokenId
     *
     * @return Response
     */
    public function manageAction(int $tokenId = null, Request $request): Response
    {
        $token =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetClientToken::class)
                ->find($tokenId);

        if (null === $token) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Токена не существует',
                    ],
                ],
            ]);
        }

        if (!($token instanceof TargetClientToken)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TokenType::class, $token);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $this->redirectToRoute('token.manage', [
                'tokenId' => $tokenId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/token',
        ]);
    }

    /**
     * @param null|int $tokenId
     *
     * @return Response
     */
    public function deleteAction(int $tokenId = null): Response
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetClientToken::class)
                ->find($tokenId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('token.all');
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
            'templating' => '?'.EngineInterface::class,
            'twig' => '?'.Environment::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
        ];
    }
}
