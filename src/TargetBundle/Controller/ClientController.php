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
use TargetBundle\Entity\TargetClient;
use TargetBundle\Form\ClientType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class ClientController extends AbstractController implements ServiceSubscriberInterface
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
    public function clientAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetClient::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'client',
                'id' => $method->getId(),
                'title' => $method->getClientName(),
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/client',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetClient = new TargetClient();

        $form = $this->createForm(ClientType::class, $targetClient);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetClient);
            $this->entityManager->flush();

            return $this->redirectToRoute('client.manage', [
                'clientId' => $targetClient->getId(),
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/client',
        ]);
    }

    /**
     * @param null|int $clientId
     *
     * @return Response
     */
    public function manageAction(int $clientId = null, Request $request): Response
    {
        $client =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetClient::class)
                ->find($clientId);

        if (null === $client) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Такого клиента не существует',
                    ],
                    'compound' => false,
                ],
            ]);
        }

        if (!($client instanceof TargetClient)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return $this->redirectToRoute('client.manage', [
                'clientId' => $clientId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/client',
        ]);
    }

    /**
     * @param null|int $clientId
     *
     * @return Response
     */
    public function deleteAction(int $clientId = null)
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetClient::class)
                ->find($clientId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('client.all');
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
