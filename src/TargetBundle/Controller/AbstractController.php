<?php

namespace TargetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as FrameworkAbstractController;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of AbstractController.
 *
 * @author Vitaly Dergunov (Cimus <correcter@inbox.ru>)
 */
abstract class AbstractController extends FrameworkAbstractController implements ServiceSubscriberInterface
{
    /**
     * @param array $message
     *
     * @return Response
     */
    protected function notFound(array $message = [])
    {
        return new Response($message, 404);
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @param int|null $code
     * @return Response
     */
    protected function buildError(\Symfony\Component\Form\FormInterface $form, int $code = null)
    {
        return new Response($this->createFormErrors($form), $code);
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    protected function createFormErrors(\Symfony\Component\Form\FormInterface $form)
    {
        return $this->createFromErrorsForErrorIterator($form->getErrors(true, false));
    }

    /**
     * @param \Symfony\Component\Form\FormErrorIterator $iterator
     * @param array                                     $errors
     *
     * @return array
     */
    protected function createFromErrorsForErrorIterator(\Symfony\Component\Form\FormErrorIterator $iterator, $errors = [])
    {
        foreach ($iterator as $error) {
            if ($error  instanceof \Symfony\Component\Form\FormError) {
                $errors[] = $error->getMessage();
            } elseif ($error instanceof \Symfony\Component\Form\FormErrorIterator) {
                $errors[$error->getForm()->getName()] = $this->createFromErrorsForErrorIterator($error);
            }
        }

        return $errors;
    }
}
