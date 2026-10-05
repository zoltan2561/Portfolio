<?php

// Independent processes exercise SQLite's actual cross-connection write lock.
use App\Services\Randi\InviteStore;
use App\Services\Randi\RandiConflict;
use App\Services\Randi\ResponseValidator;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.connections.randi.database' => $argv[1]]);
CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-02 12:00:00', 'UTC'));
$deadline = microtime(true) + 8;
while (! file_exists($argv[5]) && microtime(true) < $deadline) {
    usleep(10000);
}
try {
    app(InviteStore::class)->submit($argv[2], $argv[3], ['decision' => $argv[4], 'date_mode' => 'discuss_later', 'activity' => 'surprise'], app(ResponseValidator::class));
    echo 'saved';
} catch (RandiConflict) {
    echo 'conflict';
}
