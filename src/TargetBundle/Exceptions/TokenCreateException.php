<?php

namespace TargetBundle\Exceptions;

use Exception;

/**
 * Class TokenCreateException.
 *
 * @author Vitaly Dergunov
 */
class TokenCreateException extends TargetException
{
    /**
     * TokenCreateException constructor.
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
