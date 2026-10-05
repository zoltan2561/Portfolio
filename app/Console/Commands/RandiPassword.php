<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RandiPassword extends Command
{
    protected $signature = 'randi:password';

    protected $description = 'Adminjelszó hash készítése rejtett, interaktív bevitelből';

    public function handle(): int
    {
        $password = $this->secret('Új adminjelszó (legalább 12 karakter)');
        if (! is_string($password) || mb_strlen($password) < 12 || $password !== $this->secret('Jelszó újra')) {
            $this->error('Legalább 12 karakteres, kétszer azonos jelszó szükséges.');

            return self::FAILURE;
        }
        $this->line("RANDI_ADMIN_PASSWORD_HASH='".password_hash($password, PASSWORD_DEFAULT)."'");
        $this->info('A sort tedd a privát .env fájlba. Ez a parancs nem módosította a konfigurációt.');

        return self::SUCCESS;
    }
}
