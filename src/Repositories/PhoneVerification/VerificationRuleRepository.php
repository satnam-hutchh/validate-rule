<?php

namespace Hutchh\VerificationRule\Repositories\PhoneVerification;
use Hutchh\VerificationRule\Repositories\abstractModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Hutchh\VerificationRule\Helpers\Verification\Payload;
use Hutchh\VerificationRule\Exceptions;

class VerificationRuleRepository  extends abstractModel{

    /**
     * Change Account Email.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeRequest($phoneRequest){
        try{
            $initRequests       = 0;
            if($this->verificationGroup->phoneVerificationDetails){
                if($this->verificationGroup->phoneVerificationDetails->is_verified){
                    $this->verificationGroup->phoneVerificationDetails->delete();
                }else{
                    $initRequests   = ++$this->verificationGroup->phoneVerificationDetails->requests;
                    if(!$this->requestExpired($this->verificationGroup->phoneVerificationDetails->expires_at)){
                        $remainingTime = $this->requestToExpired($this->verificationGroup->phoneVerificationDetails->expires_at);
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
            
            if(!$this->verificationGroup->phoneVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if($this->verificationGroup->phoneVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $initRequests   = ++$this->verificationGroup->phoneVerificationDetails->requests;
            if(!$this->requestExpired($this->verificationGroup->phoneVerificationDetails->expires_at)){
                $remainingTime = $this->requestToExpired($this->verificationGroup->phoneVerificationDetails->expires_at);
                throw new Exceptions\TooManyRequestException("Try after $remainingTime seconds.!", $remainingTime);
            }

            $initRequests                           = $initRequests<=$this->config['requests']?$initRequests:0;
            $ruleData                               = $this->verificationGroup->phoneVerificationDetails->rule_data;
            $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
            $oauthVerificationRuleModel->phone      = $ruleData;
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
            if(!$this->verificationGroup->phoneVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            if($this->verificationGroup->phoneVerificationDetails && $this->verificationGroup->phoneVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $this->verificationGroup->phoneVerificationDetails->forceDelete();
            return $this->verificationGroup->phoneVerificationDetails;
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
            
            if(!$this->verificationGroup->phoneVerificationDetails)
                throw new Exceptions\TooManyRequestException('Have no active request!');
            
            if($this->verificationGroup->phoneVerificationDetails->is_verified)
                throw new Exceptions\TooManyRequestException('Have no active request!');

            $initAttempts   = ++$this->verificationGroup->phoneVerificationDetails->attempts;
            if($this->otpExpired($this->verificationGroup->phoneVerificationDetails->attempt_at)){
                throw new Exceptions\TooManyRequestException("OTP Time Expire!");
            }

            $ruleData       = $this->verificationGroup->phoneVerificationDetails->rule_data;
            if (\Hash::check($otpRequest['otp'], $ruleData['otp']) == false && !$alreadyCheck) {
                $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
                $oauthVerificationRuleModel->attempts   = $initAttempts;
                $this->verificationGroup->phoneVerificationDetails->update($oauthVerificationRuleModel->toArray());
                throw new Exceptions\TooManyRequestException('Invalid otp attempt');
            }

            $oauthVerificationRuleModel             = new Payload\VerificationPayloadBuilder();
            $oauthVerificationRuleModel->isVerified = 1;
            $oauthVerificationRuleModel->verifiedAt = Carbon::now();
            $oauthVerificationRuleModel->oauthVerificationData  = [
                "message" => "Self attested!"
            ];
            $this->verificationGroup->phoneVerificationDetails->update($oauthVerificationRuleModel->toArray());
            return $this->verificationGroup->phoneVerificationDetails;
        }catch(\Illuminate\Database\QueryException $e){
            throw $e;
        }catch(\Throwable $e){
            throw $e;
        }

    }
}
