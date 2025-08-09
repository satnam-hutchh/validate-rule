<?php

namespace Hutchh\VerificationRule\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

interface VerificationRuleContract
{
    public function causer(): MorphTo;
    public function scopeForModel(Builder $query, Model $model): Builder;
}
