<?php

namespace TargetBundle\EventListener;

use Doctrine\ORM\EntityManager;

/**
 * @author Vitaly Dergunov
 */
class AbstractListener
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * AbstractListener constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
}
