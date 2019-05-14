<?php

namespace TargetBundle\Exceptions;

use Exception;

class TargetNotFound extends TargetException
{
    /**
     * TargetNotFound constructor.
     *
     * @param string         $message
     * @param null           $variables
     * @param null|Exception $previous
     * @param mixed          $code
     */
    public function __construct($message = 'The service did not return a valid response in the allotted time', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
