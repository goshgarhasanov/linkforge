<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$config = require dirname(__DIR__) . '/config/database.php';
$default = $config['default'];

$capsule = new Capsule();
$capsule->addConnection($config['connections'][$default]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

if (! Capsule::schema()->hasTable('migrations')) {
    Capsule::schema()->create('migrations', function (Blueprint $table): void {
        $table->id();
        $table->string('migration')->unique();
        $table->unsignedInteger('batch');
        $table->timestamp('ran_at')->useCurrent();
    });
}

$batch = ((int) Capsule::table('migrations')->max('batch')) + 1;
$applied = Capsule::table('migrations')->pluck('migration')->toArray();
$files = glob(__DIR__ . '/migrations/*.php') ?: [];
sort($files);

$applied_count = 0;
foreach ($files as $file) {
    $name = basename($file, '.php');

    if (in_array($name, $applied, true)) {
        continue;
    }

    echo "→ Tətbiq olunur: {$name}\n";
    $migration = require $file;
    $migration->up();

    Capsule::table('migrations')->insert([
        'migration' => $name,
        'batch'     => $batch,
    ]);

    $applied_count++;
}

if ($applied_count === 0) {
    echo "✓ Bütün migration-lar artıq tətbiq edilib.\n";
} else {
    echo "\n✓ {$applied_count} migration uğurla tətbiq edildi (batch #{$batch}).\n";
}
