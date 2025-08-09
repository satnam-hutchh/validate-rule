<?php

namespace Hutchh\VerificationRule\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidConfigurationException extends Exception
{
    public static function modelIsNotValid(string $className): self
    {
        return new static("The given model class does not extend `".Model::class.'`');
    }
}
