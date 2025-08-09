<?php

namespace Hutchh\VerificationRule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Hutchh\VerificationRule\Contracts;
use Hutchh\VerificationRule\Traits\UuidTrait;

class VerificationRule extends Model implements Contracts\VerificationRuleContract
{
    use HasFactory, SoftDeletes, UuidTrait;

    public $incrementing    = false;
    protected $table        = 'oauth_verification_rules';
    private string $otpCode;

    protected $fillable     = [
        'id', 'rule_type', 'rule_data', 'model_id', 'model_type', 'oauth_verification_data', 'is_verified', 'verified_at',
        'requests' ,'attempts', 'expires_at', 'attempt_at', 'user_id', 'created_at', 'updated_at', 'deleted_at', 
    ];

    protected $casts = [
        'rule_data'                 => 'array',
        'oauth_verification_data'   => 'array',
        'is_verified'               => 'boolean',
    ];

    public function scopeForModel(Builder $query, Model $model): Builder {
        return $query
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey());
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

}
