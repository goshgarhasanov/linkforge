<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('webhooks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('secret', 64);
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('last_delivered_at')->nullable();
            $table->text('last_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Capsule::schema()->create('webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event', 60);
            $table->json('payload');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('was_successful')->default(false);
            $table->timestamp('attempted_at')->useCurrent();

            $table->index(['webhook_id', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('webhook_deliveries');
        Capsule::schema()->dropIfExists('webhooks');
    }
};
