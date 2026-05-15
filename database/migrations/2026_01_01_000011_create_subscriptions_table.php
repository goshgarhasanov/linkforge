<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 32);
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled', 'incomplete'])->default('incomplete');
            $table->string('stripe_customer_id', 120)->nullable()->index();
            $table->string('stripe_subscription_id', 120)->nullable()->unique();
            $table->string('stripe_price_id', 120)->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Capsule::schema()->create('billing_events', function (Blueprint $table): void {
            $table->id();
            $table->string('stripe_event_id', 120)->unique();
            $table->string('event_type', 80)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('billing_events');
        Capsule::schema()->dropIfExists('subscriptions');
    }
};
