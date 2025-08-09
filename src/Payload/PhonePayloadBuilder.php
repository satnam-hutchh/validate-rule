<?php

namespace Hutchh\VerificationRule\Payload;
use Carbon\Carbon;
use Hutchh\VerificationRule\Helpers\Helper;

class PhonePayloadBuilder extends abstractBuilder {
     
    private $otpCode;

    protected $fillable = [
        'contact_number','country_code','area_code','contact_type', 'otp', 'complete_number',
    ];   

    public function setOtpAttribute($value){
        $this->attributes['otp']  = \Hash::make($value);
        $this->otpCode = $value;
    }

    public function setTypeAttribute($value){
        $this->attributes['contact_type']  = $value;
    }    

    public function setCountryCodeAttribute($value){
        $this->attributes['country_code']  = (int)$value;
        $this->attributes['complete_number']  = '+'.trim(implode('', array_intersect_key($this->attributes,[
            'country_code'  => '',
            'area_code'  => '',
            'contact_number'  => '',
        ])));
    }

    public function setAreaCodeAttribute($value){
        $this->attributes['area_code']  = (int)$value;
        $this->attributes['complete_number']  = '+'.trim(implode('', array_intersect_key($this->attributes,[
            'country_code'  => '',
            'area_code'  => '',
            'contact_number'  => '',
        ])));
    }

    public function setNumberAttribute($value){
        $this->attributes['contact_number']  = $value;
        $this->attributes['complete_number']  = '+'.trim(implode('', array_intersect_key($this->attributes,[
            'country_code'  => '',
            'area_code'  => '',
            'contact_number'  => '',
        ])));
    }
    
}