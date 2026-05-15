<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('notifications');
    }
};
