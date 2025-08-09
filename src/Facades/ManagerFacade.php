<?php

namespace Hutchh\VerificationRule\Facades;

use Illuminate\Support\Facades\Facade;
use Hutchh\VerificationRule\Managers;

class ManagerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Managers\VerificationRuleManager::class;
    }
}