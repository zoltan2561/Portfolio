<?php

namespace App\Console\Commands;

use App\Services\Randi\InviteStore;
use Illuminate\Console\Command;

class RandiDemo extends Command
{
    protected $signature = 'randi:demo-invite';

    protected $description = 'Személyes fejlesztői próbalink (kizárólag local/testing környezetben)';

    public function handle(InviteStore $store): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Ez a parancs csak fejlesztői környezetben használható.');

            return self::FAILURE;
        }
        $created = $store->create(['recipient_name' => 'Próba', 'sender_name' => 'Zoli', 'intro_message' => 'Ez egy fejlesztői próba, nem elküldendő meghívó.', 'expires_days' => 1]);
        $this->line($created['link']);
        $this->info('A token csak most olvasható. A próbaadat később az adminban törölhető.');

        return self::SUCCESS;
    }
}
