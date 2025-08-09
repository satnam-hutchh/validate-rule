<?php

namespace Hutchh\VerificationRule\Repositories;
use Illuminate\Support\Carbon;
/**
 * Class BaseSender.
 */
abstract class abstractModel
{
    /**
     * The model used to send verification.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */

    
    public function __construct(protected \Illuminate\Database\Eloquent\Model $verificationGroup, protected $config){
        //
    }

    /**
     * Check expire date passed.
     *
     * @param  string  $expiresAt
     * @return bool
     */
    public function needDelay($expiresAt){
        return Carbon::parse($expiresAt)->subSeconds(1)->greaterThan(Carbon::now());
    }

    /**
     * Determine if the request has expired.
     *
     * @param  string  $expiresAt
     * @return bool
    */
    public function requestExpired($expiresAt){
        return Carbon::parse($expiresAt)->isPast();
    }

    /**
     * Request Remaining Time.
     *
     * @param  string  $expiresAt
     * @param  int  $diffInSeconds
     * @return int
    */
    public function requestToExpired($expiresAt){
        if($this->needDelay($expiresAt))
        return abs(Carbon::parse($expiresAt)->diffInSeconds(Carbon::now()));
        return 0;
    }

    /**
     * Determine if the otp has expired.
     *
     * @param  string  $expiresAt
     * @return bool
    */
    public function otpExpired($expiresAt){
        return Carbon::parse($expiresAt)->addSeconds($this->config['expires'])->isPast();
    }

    /**
     * Remaining Time.
     *
     * @param  string  $expiresAt
     * @param  int  $diffInSeconds
     * @return int
    */
    public function otpToExpired($expiresAt){
        if(!$this->otpExpired($expiresAt))
        return abs($this->config['expires'] - Carbon::parse($expiresAt)->diffInSeconds(Carbon::now()));
        return 0;
    }

    /**
     * Generate OTP.
     *
     * @param  int  $length
     * @return int
    */
    public function generateOTP(int $length = 6){
        return implode('', array_map(function($value) {
            return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9);
        }, range(1, $length)));
    }
}