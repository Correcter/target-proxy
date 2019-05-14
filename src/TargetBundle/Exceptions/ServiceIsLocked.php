<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class ServiceIsLocked.
 *
 * @author Vitaly Dergunov
 */
class ServiceIsLocked extends TargetException
{
    /**
     * ServiceIsLocked constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param null|Exception $previous
     */
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
