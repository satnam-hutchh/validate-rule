<?php

namespace Hutchh\VerificationRule\Repositories\EmailVerification;
use Hutchh\VerificationRule\Repositories\abstractModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

use Hutchh\VerificationRule\Payload;
use Hutchh\VerificationRule\Exceptions;

class VerificationRuleRepository  extends abstractModel{

    /**
     * Change Account Email.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeRequest($emailRequest){
        try{
            $initRequests       = 0;
            
            if($this->verificationGroup->emailVerificationDetails){
                if($this->verificationGroup->emailVerificationDetails->is_verified){
                    $this->verificationGroup->emailVerificationDetails->delete();
                }else{
                    $initRequests   = ++$this->verificationGroup->emailVerificationDetails->requests;
                    if(!$this->requestExpired($this->verificationGroup->emailVerificationDetails->expires_at)){
                        $remainingTime = $this->requestToExpired($this->verificationGroup->emailVerificationDetails->expires_at);
                        throw new Exceptions\TooManyRequestException("Try after $remainingTime seconds.!", $remainingTime);
                    }
                }
            }
            
            $initRequests                   = $initRequests<=$this->config['requests']?$initRequests:0;
            $payloadAttribute               = new Payload\VerificationPayloadBuilder($emailRequest);
            $payloadAttribute->requests     = $initRequests;
            $payloadAttribute->attemptAt    = Carbon::now();
            $payloadAttribute->isVerified   = 0;
            $payloadAttribute->expiresAt    = $initRequests<=$this->config['requests']?$this->config['expires']:$this->config['breach'];
            
            $verificationRuleDetail = $this->verificationGroup->verificationRuleDetails()->updateOrCreate([
                'rule_type' => $this->config['ruleType']
            ], $payloadAttribute->toArray());

            return $payloadAttribute;
        }catch(\Illuminate\Database\QueryException $e){
            throw $e;
        }catch(\Throwable $e){
            throw $e;
        }
    }

    /**
     * Resend Account Email.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendRequest(){
        try{
            $initRequests       = 0;
            if(!$this->verificationGroup)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if(!$this->verificationGroup->emailVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if($this->verificationGroup->emailVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $initRequests   = ++$this->verificationGroup->emailVerificationDetails->requests;
            if(!$this->requestExpired($this->verificationGroup->emailVerificationDetails->expires_at)){
                $remainingTime = $this->requestToExpired($this->verificationGroup->emailVerificationDetails->expires_at);
                throw new Exceptions\TooManyRequestException("Try after $remainingTime seconds.!", $remainingTime);
            }

            $initRequests                           = $initRequests<=$this->config['requests']?$initRequests:0;
            $ruleData                               = $this->verificationGroup->emailVerificationDetails->rule_data;
            $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
            $oauthVerificationRuleModel->email      = $ruleData['email'];
            $oauthVerificationRuleModel->requests   = $initRequests;
            $oauthVerificationRuleModel->attemptAt  = Carbon::now();
            $oauthVerificationRuleModel->expiresAt  = $initRequests<=$this->config['requests']?$this->config['expires']:$this->config['breach'];
          
            $verificationRuleDetail = $this->verificationGroup->verificationRuleDetails()->updateOrCreate([
                'rule_type' => $this->config['ruleType']
            ], $oauthVerificationRuleModel->toArray());
            $verificationRuleDetail->otpCode = $oauthVerificationRuleModel->otpCode;
            return $verificationRuleDetail;
        }catch(\Illuminate\Database\QueryException $e){
            throw $e;
        }catch(\Throwable $e){
            throw $e;
        }
    }

    /**
     * Cancel Account Email.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancelRequest(){
        try{
            if(!$this->verificationGroup)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            if(!$this->verificationGroup->emailVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            if($this->verificationGroup->emailVerificationDetails && $this->verificationGroup->emailVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $this->verificationGroup->emailVerificationDetails()->delete();
            return $this->verificationGroup->emailVerificationDetails;
        }catch(\Illuminate\Database\QueryException $e){
            throw $e;
        }catch(\Throwable $e){
            throw $e;
        }
    }

    /**
     * Verify Account Email.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyRequest($otpRequest, $alreadyCheck=false){
        try{
            if(!$this->verificationGroup)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if(!$this->verificationGroup->emailVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if($this->verificationGroup->emailVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $initAttempts   = ++$this->verificationGroup->emailVerificationDetails->attempts;
            if(!$alreadyCheck && $this->otpExpired($this->verificationGroup->emailVerificationDetails->attempt_at)){
                throw new Exceptions\TooManyRequestException("OTP Time Expire!");
            }

            $ruleData       = $this->verificationGroup->emailVerificationDetails->rule_data;
            if (!$alreadyCheck && \Hash::check($otpRequest['otp'], $ruleData['otp']) == false) {
                $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
                $oauthVerificationRuleModel->attempts   = $initAttempts;
                $this->verificationGroup->emailVerificationDetails->update($oauthVerificationRuleModel->toArray());
                throw new Exceptions\TooManyRequestException('Invalid otp attempt');
            }

            $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
            $oauthVerificationRuleModel->isVerified = 1;
            $oauthVerificationRuleModel->verifiedAt = Carbon::now();
            $oauthVerificationRuleModel->oauthVerificationData  = [
                "message" => "Self attested!"
            ];
            $this->verificationGroup->emailVerificationDetails->update($oauthVerificationRuleModel->toArray());
            return $this->verificationGroup->emailVerificationDetails;
        }catch(\Illuminate\Database\QueryException $e){
            throw $e;
        }catch(\Throwable $e){
            throw $e;
        }

    }
}
