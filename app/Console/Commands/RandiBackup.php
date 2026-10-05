<?php

namespace App\Console\Commands;

use App\Services\Randi\InviteStore;
use Illuminate\Console\Command;
use PDO;

class RandiBackup extends Command
{
    protected $signature = 'randi:backup {destination : Új, abszolút, nem publikus .sqlite fájl}';

    protected $description = 'Konzisztens SQLite-mentés VACUUM INTO használatával';

    public function handle(InviteStore $store): int
    {
        $path = $this->argument('destination');
        InviteStore::assertPrivatePath($path);
        if (file_exists($path) || ! is_dir(dirname($path))) {
            $this->error('Létező privát könyvtárban új fájlnevet adj meg; mentést nem írunk felül.');

            return self::FAILURE;
        }
        $store->connection()->statement('VACUUM INTO ?', [$path]);
        chmod($path, 0640);
        $backup = new PDO('sqlite:'.$path);
        if ($backup->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
            $this->error('A mentés integritásvizsgálata sikertelen.');

            return self::FAILURE;
        }
        $this->info('Konzisztens mentés elkészült, integrity_check: ok.');

        return self::SUCCESS;
    }
}
