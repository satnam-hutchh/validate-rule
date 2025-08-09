<?php

namespace Hutchh\VerificationRule\Managers;

use Hutchh\VerificationRule\Repositories;
use Hutchh\VerificationRule\Payload;

use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Log;

class VerificationRuleManager extends Manager
{
    protected \Illuminate\Database\Eloquent\Model $verificationModel;

    private function setAuthUserModel($guard=null){
        $authGuard = app('auth')->guard($guard);
        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }
        $this->verificationModel = $authGuard->user();
    }

    public function setVerificationModel(\Illuminate\Database\Eloquent\Model $verificationModel){
        $this->verificationModel = $verificationModel;
    }

    public function getVerificationModel(){
        if(!isset($this->verificationModel)){
            $this->setAuthUserModel();
        }
        return $this->verificationModel;
    }

    public function getDefaultDriver(){
        return $this->config->get('verification.driver','email_verification');
    }

    protected function createEmailVerificationDriver(){
        $config = $this->config->get('verification.email_verification', []);
        return new Repositories\EmailVerification\VerificationRuleRepository($this->getVerificationModel(),$config);
    }

    protected function createPhoneVerificationDriver(){
        $config = $this->config->get('verification.phone_verification', []);
        return new Repositories\PhoneVerification\VerificationRuleRepository($this->getVerificationModel(),$config);
    }
}