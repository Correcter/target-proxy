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
use TargetBundle\Entity\TargetAgency;
use TargetBundle\Form\AgencyType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class AgencyController extends AbstractController implements ServiceSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AgencyController constructor.
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
    public function agencyAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetAgency::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'agency',
                'id' => $method->getId(),
                'title' => $method->getAgencyName(),
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/agency',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetAgency = new TargetAgency();

        $form = $this->createForm(AgencyType::class, $targetAgency);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetAgency);
            $this->entityManager->flush();

            return $this->redirectToRoute('agency.manage', [
                'agencyId' => $targetAgency->getId(),
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/agency',
        ]);
    }

    /**
     * @param null|int $agencyId
     *
     * @return Response
     */
    public function manageAction(int $agencyId = null, Request $request): Response
    {
        $targetAgency =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetAgency::class)
                ->find($agencyId);

        if (null === $targetAgency) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Такого агентства не существует',
                    ],
                    'compound' => false,
                ],
            ]);
        }

        if (!($targetAgency instanceof TargetAgency)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AgencyType::class, $targetAgency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($targetAgency);
            $this->entityManager->flush();

            return $this->redirectToRoute('agency.manage', [
                'agencyId' => $agencyId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/agency',
        ]);
    }

    /**
     * @param null|int $agencyId
     *
     * @return Response
     */
    public function deleteAction(int $agencyId = null): Response
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetAgency::class)
                ->find($agencyId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('agency.all');
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
