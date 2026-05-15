<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_hash', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name', 80)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('region', 120)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet', 'bot', 'unknown'])->default('unknown');
            $table->string('browser', 60)->nullable();
            $table->string('browser_version', 40)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('os_version', 40)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer_host', 190)->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('language', 10)->nullable();
            $table->boolean('is_unique')->default(true);
            $table->boolean('is_bot')->default(false);
            $table->timestamp('clicked_at')->useCurrent();

            $table->index(['link_id', 'clicked_at']);
            $table->index('country_code');
            $table->index(['device_type', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('clicks');
    }
};
