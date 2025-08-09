<?php

namespace Hutchh\VerificationRule\Traits;
use Illuminate\Support\Str;

trait UuidTrait{

    public $incrementing = false;

	public static function bootUuidTrait(): void
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}