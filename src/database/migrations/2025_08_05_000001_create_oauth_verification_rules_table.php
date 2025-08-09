<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_verification_rules', function (Blueprint $table) {
            Builder :: $defaultMorphKeyType = 'uuid';
            $table->uuid('id')->unique();
            $table->primary('id');

            $table->uuidMorphs('model', 'model_id_model_type_index');
            $table->string('rule_type', 20);
            $table->json('rule_data');
            $table->json('oauth_verification_data')->nullable();

            $table->smallInteger('requests')->default(0);
            $table->smallInteger('attempts')->default(0);

            $table->dateTime('expires_at')->nullable();
            $table->dateTime('attempt_at')->nullable();
            
            $table->boolean('is_verified')->default(0);
            $table->dateTime('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_verification_rules');
    }
};
