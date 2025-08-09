<?php

namespace Hutchh\VerificationRule\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

use Hutchh\VerificationRule\Models      as Eloquents;

use Hutchh\VerificationRule\Exceptions;
use Hutchh\VerificationRule\Contracts;


class VerificationRuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../../config/verification.php', 'verification');

        $this->app->singleton(
            abstract: Managers\VerificationRuleManager::class,
            concrete: fn (Application $app) => new Managers\VerificationRuleManager($app),
        );

        // Get the AliasLoader instance
        $loader = AliasLoader::getInstance();

        // Add your aliases
        $loader->alias('Verification', \Hutchh\VerificationRule\Facades\ManagerFacade::class);
    }

    /**
     * Bootstrap services. 
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/verification.php' => config_path('verification.php'),
            ],'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations/verification'),
            ], 'migrations');
        }
    }

    public static function resolveVerificationRuleModel(): string
    {
        $verificationRuleModel = config('eventstream.verification_model') ?? Eloquents\VerificationRule::class;
        if (! is_a($verificationRuleModel, Model::class, true) || ! is_a($verificationRuleModel, Contracts\VerificationRuleContract::class, true)) {
            throw Exceptions\InvalidConfiguration::modelIsNotValid($verificationRuleModel);
        }
        return $verificationRuleModel;
    }

    public static function getVerificationRuleModel(): Contracts\VerificationRuleContract
    {
        $verificationRuleModel = self::resolveVerificationRuleModel();
        return new $verificationRuleModel();
    }

}
