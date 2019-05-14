<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class InvalidProxyType.
 *
 * @author Vitaly Dergunov
 */
class InvalidProxyType extends TargetException
{
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
