<?php

namespace Hutchh\VerificationRule\Payload;
use Carbon\Carbon;

class VerificationPayloadBuilder extends abstractBuilder {
     
    private $otpCode;

    protected $fillable = [
        'user_id', 'rule_type', 'rule_data', 'expires_at', 'attempt_at', 'verified_at', 'attempts', 'requests', 'verification_group_id', 'oauth_user_id', 'oauth_verification_data', 'is_verified', 'verified_at'
    ];

    public function generateOTP(int $length = 6){
        return implode('', array_map(function($value) {
            return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9);
        }, range(1, $length)));
    }

    public function setEmailAttribute($value){
        $this->otpCode = $this->generateOTP();
        $this->attributes['rule_type'] = 'emailVerify';
        $this->attributes['rule_data'] = [
            "email" => $value,
            "otp"   => \Hash::make($this->otpCode)
        ];
    }

    public function setPhoneAttribute($value){
        $this->otpCode          = $this->generateOTP();
        $this->attributes['rule_type'] = 'phoneVerify';
        $phoneAttribute         = new PhonePayloadBuilder($value);
        $phoneAttribute->otp    = $this->otpCode;
        $this->attributes['rule_data'] = $phoneAttribute->toArray();
    }

    public function setNumberAttribute($value){
        $this->otpCode          = $this->generateOTP();
        $this->attributes['rule_type'] = 'phoneVerify';
        $phoneAttribute         = new PhonePayloadBuilder($value);
        $phoneAttribute->otp    = $this->otpCode;
        $this->attributes['rule_data'] = $phoneAttribute->toArray();
    }

    public function setExpiresAtAttribute($value){
        $this->attributes['expires_at']     = now()->addSeconds($value);
    }

    public function getOtpCodeAttribute(){
        return $this->otpCode;
    }
    
    public function getVerifiedAtAttribute(){
        return $this->attributes['verified_at']??null;
    }
    
}