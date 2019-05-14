<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class TokenNotFound.
 *
 * @author Vitaly Dergunov
 */
class TokenNotFound extends TargetException
{
    /**
     * TokenNotFound constructor.
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
