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
use TargetBundle\Entity\TargetMethod;
use TargetBundle\Form\MethodType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class MethodController extends AbstractController implements ServiceSubscriberInterface
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
    public function methodAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetMethod::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'method',
                'id' => $method->getId(),
                'title' => $method->getMethodName(),
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/method',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetMethod = new TargetMethod();

        $form = $this->createForm(MethodType::class, $targetMethod);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetMethod);
            $this->entityManager->flush();

            return $this->redirectToRoute('method.manage', [
                'methodId' => $targetMethod->getId(),
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/method',
        ]);
    }

    /**
     * @param null|int $methodId
     *
     * @return Response
     */
    public function manageAction(int $methodId = null, Request $request): Response
    {
        $targetMethod =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetMethod::class)
                ->find($methodId);

        if (null === $targetMethod) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Такого метода доступа к TargetMail не существует',
                    ],
                    'compound' => false,
                ],
            ]);
        }

        if (!($targetMethod instanceof TargetMethod)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(MethodType::class, $targetMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($targetMethod);
            $this->entityManager->flush();

            return $this->redirectToRoute('method.manage', [
                'methodId' => $methodId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/method',
        ]);
    }

    /**
     * @param null|int $methodId
     *
     * @return Response
     */
    public function deleteAction(int $methodId = null): Response
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetMethod::class)
                ->find($methodId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('method.all');
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
