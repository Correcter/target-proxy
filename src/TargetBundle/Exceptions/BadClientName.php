<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class BadClientName.
 *
 * @author Vitaly Dergunov
 */
class BadClientName extends UserNotFound
{
    /**
     * BadClientName constructor.
     *
     * @param string         $message
     * @param null           $variables
     * @param null|Exception $previous
     */
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
