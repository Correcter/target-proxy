<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class TargetException.
 *
 * @author Vitaly Dergunov
 */
class TargetException extends Exception
{
    /**
     * TargetException constructor.
     *
     * @param string         $message
     * @param null           $variables
     * @param null|Exception $previous
     * @param mixed          $code
     */
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
