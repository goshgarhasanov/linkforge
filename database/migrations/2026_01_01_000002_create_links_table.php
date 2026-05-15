<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('links', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('short_code', 32)->unique();
            $table->text('original_url');
            $table->string('title', 255)->nullable();
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_clicks')->nullable();
            $table->unsignedBigInteger('click_count')->default(0);
            $table->unsignedBigInteger('unique_click_count')->default(0);
            $table->json('utm_parameters')->nullable();
            $table->string('ios_deep_link')->nullable();
            $table->string('android_deep_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('links');
    }
};
