<?php

return [
    'driver'    => env('VERIFICATION_RULE_DRIVER', 'email_verification'),
    'email_verification' => [
        'ruleType'  => 'emailVerify',
        "expires"   => 60, //Seconds
        "delays"    => 60, //Seconds
        "breach"    => 1800, //Seconds
        "attempts"  => 5, //Seconds
        "requests"  => 5, //Seconds

    ],
    'phone_verification' => [
        'ruleType'  => 'phoneVerify',
        "expires"   => 60, //Seconds
        "delays"    => 60, //Seconds
        "breach"    => 1800, //Seconds
        "attempts"  => 5, //Seconds
        "requests"  => 5, //Seconds
    ],
    'verification_model'    => Hutchh\VerificationRule\Models\VerificationRule::class,
];