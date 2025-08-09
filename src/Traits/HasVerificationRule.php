<?php

namespace Hutchh\VerificationRule\Traits;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVerificationRule
{

    /** @var string */
    private $verificationRuleClass;

    public function getVerificationRuleClass(){
        if (! isset($this->verificationRuleClass)) {
            $this->verificationRuleClass = config('verification.verification_model');
        }
        return $this->verificationRuleClass;
    }

    public function emailVerificationDetails(){
        return $this->morphOne(config('verification.verification_model'),'model')->where('rule_type',config('verification.email_verification.ruleType'));
    }

    public function phoneVerificationDetails(){
        return $this->morphOne(config('verification.verification_model'),'model')->where('rule_type',config('verification.phone_verification.ruleType'));
    }

    public function verificationRuleDetails() : MorphMany{
        return $this->morphMany(config('verification.verification_model'),'model');
    }
}
