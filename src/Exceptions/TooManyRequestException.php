<?php

namespace Hutchh\VerificationRule\Exceptions;

use Exception;

class TooManyRequestException extends Exception
{
    public function __construct($message = 'Unauthenticated.', protected $delayTime = null)
    {
        parent::__construct($message);
    }

    /**
     * Get the guards that were checked.
     *
     * @return array
     */
    public function delayTime()
    {
        return $this->delayTime;
    }
}
