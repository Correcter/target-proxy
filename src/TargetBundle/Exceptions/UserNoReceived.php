<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class UserNoReceived.
 *
 * @author Vitaly Dergunov
 */
class UserNoReceived extends TargetException
{
    /**
     * UserNoReceived constructor.
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
