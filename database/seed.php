<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$config = require dirname(__DIR__) . '/config/database.php';
$default = $config['default'];

$capsule = new Capsule();
$capsule->addConnection($config['connections'][$default]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$adminEmail    = $_ENV['ADMIN_EMAIL']    ?? 'admin@linkforge.io';
$adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'Admin12345';

$existing = User::query()->where('email', $adminEmail)->first();

if ($existing instanceof User) {
    if ($existing->role !== UserRole::SuperAdmin) {
        $existing->forceFill(['role' => UserRole::SuperAdmin, 'is_active' => true])->save();
        echo "✓ {$adminEmail} mövcud idi, rolu super_admin-ə yüksəldildi.\n";
    } else {
        echo "✓ {$adminEmail} artıq super admin-dir, dəyişiklik yoxdur.\n";
    }
} else {
    User::query()->create([
        'name'              => 'LinkForge Admin',
        'email'             => $adminEmail,
        'password_hash'     => password_hash($adminPassword, PASSWORD_ARGON2ID),
        'role'              => UserRole::SuperAdmin,
        'is_active'         => true,
        'email_verified_at' => now(),
        'locale'            => 'az',
    ]);

    echo "✓ Super admin yaradıldı:\n";
    echo "  E-poçt:  {$adminEmail}\n";
    echo "  Şifrə:   {$adminPassword}\n";
    echo "\n⚠ Production-da bu şifrəni dərhal dəyişdirin!\n";
}
