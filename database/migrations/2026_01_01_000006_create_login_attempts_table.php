<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('login_attempts', function (Blueprint $table): void {
            $table->id();
            $table->string('email', 190)->nullable()->index();
            $table->string('ip_address', 45)->index();
            $table->boolean('was_successful')->default(false);
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->useCurrent();

            $table->index(['ip_address', 'attempted_at']);
            $table->index(['email', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('login_attempts');
    }
};
