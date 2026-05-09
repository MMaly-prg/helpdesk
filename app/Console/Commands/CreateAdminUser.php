<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature   = 'helpdesk:create-admin {--name=} {--email=} {--password=}';
    protected $description = 'Utwórz konto administratora systemu.';

    public function handle(): int
    {
        $name     = $this->option('name')     ?? $this->ask('Imię i nazwisko');
        $email    = $this->option('email')    ?? $this->ask('Adres email');
        $password = $this->option('password') ?? $this->secret('Hasło (min. 8 znaków)');

        if (strlen($password) < 8) {
            $this->error('Hasło musi mieć co najmniej 8 znaków.');
            return Command::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Użytkownik z emailem {$email} już istnieje.");
            return Command::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'role'     => 'admin',
            'is_active'=> true,
        ]);

        $this->info("✅ Administrator utworzony: {$user->name} <{$user->email}>");
        return Command::SUCCESS;
    }
}
