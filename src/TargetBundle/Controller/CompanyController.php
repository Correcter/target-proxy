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
use TargetBundle\Entity\TargetCompany;
use TargetBundle\Form\CompanyType;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class CompanyController extends AbstractController implements ServiceSubscriberInterface
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
    public function companyAction(Request $request): Response
    {
        $data = [];
        foreach ($this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetCompany::class)
            ->findAll() as $method) {
            $data[] = [
                'caption' => 'company',
                'id' => $method->getId(),
                'title' => $method->getCompanyName(),
            ];
        }

        return $this->render('view/base.html.twig', [
            'data' => $data,
            'basePath' => '/company',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $targetCompany = new TargetCompany();

        $form = $this->createForm(CompanyType::class, $targetCompany);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->buildError($form, 400);
            }

            $this->entityManager->persist($targetCompany);
            $this->entityManager->flush();

            return $this->redirectToRoute('company.create');
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/company',
        ]);
    }

    /**
     * @param null|int $companyId
     *
     * @return Response
     */
    public function manageAction(int $companyId = null, Request $request): Response
    {
        $company =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetCompany::class)
                ->find($companyId);

        if (null === $company) {
            return $this->render('error.html.twig', [
                'form' => [
                    'errors' => [
                        'message' => 'Такой компании не существует',
                    ],
                    'compound' => false,
                ],
            ]);
        }

        if (!($company instanceof TargetCompany)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return $this->redirectToRoute('company.manage', [
                'companyId' => $companyId,
            ]);
        }

        return $this->render('view/base.html.twig', [
            'form' => $form->createView(),
            'basePath' => '/company',
        ]);
    }

    /**
     * @param null|int $companyId
     *
     * @return Response
     */
    public function deleteAction(int $companyId = null): Response
    {
        $this->entityManager->remove(
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetCompany::class)
                ->find($companyId)
        );
        $this->entityManager->flush();

        return $this->redirectToRoute('company.all');
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
