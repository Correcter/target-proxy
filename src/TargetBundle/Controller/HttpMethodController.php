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
use TargetBundle\Entity\TargetHttpMethod;
use TargetBundle\Form\HttpMethodType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class HttpMethodController extends AbstractController implements ServiceSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * MethodController constructor.
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
    public function httpMethodAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(TargetHttpMethod::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'httpMethod',
                'id' => $method->getId(),
                'title' => $method->getHttpMethodName(),
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/httpMethod',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetHttpMethod = new TargetHttpMethod();

        $form = $this->createForm(HttpMethodType::class, $targetHttpMethod);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetHttpMethod);
            $this->entityManager->flush();

            return $this->redirectToRoute('httpMethod.manage', [
                'httpMethodId' => $targetHttpMethod->getId(),
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/httpMethod',
        ]);
    }

    /**
     * @param null|int $httpMethodId
     *
     * @return Response
     */
    public function manageAction(int $httpMethodId = null, Request $request): Response
    {
        $targetHttpMethod =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetHttpMethod::class)
                ->find($httpMethodId);

        if (null === $targetHttpMethod) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Такого HTTP-метода доступа к TargetMail не существует',
                    ],
                ],
            ]);
        }

        if (!($targetHttpMethod instanceof TargetHttpMethod)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HttpMethodType::class, $targetHttpMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($targetHttpMethod);
            $this->entityManager->flush();

            return $this->redirectToRoute('httpMethod.manage', [
                'httpMethodId' => $httpMethodId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/httpMethod',
        ]);
    }

    /**
     * @param null|int $httpMethodId
     *
     * @return Response
     */
    public function deleteAction(int $httpMethodId = null): Response
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetHttpMethod::class)
                ->find($httpMethodId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('httpMethod.all');
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
