<?php

namespace App\Console\Commands;

use App\Services\Randi\InviteStore;
use Illuminate\Console\Command;

class RandiInstall extends Command
{
    protected $signature = 'randi:install';

    protected $description = 'A külön randimodul-adatbázis adatvesztés nélküli inicializálása';

    public function handle(): int
    {
        $path = config('database.connections.randi.database');
        InviteStore::assertPrivatePath($path);
        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0750, true)) {
            $this->error('Az adatbáziskönyvtár nem hozható létre.');

            return self::FAILURE;
        }
        if (! file_exists($path)) {
            $handle = fopen($path, 'x');
            fclose($handle);
            chmod($path, 0640);
        }
        app(InviteStore::class)->connection();

        return $this->call('migrate', ['--database' => 'randi', '--path' => 'database/randi-migrations', '--force' => true]);
    }
}
