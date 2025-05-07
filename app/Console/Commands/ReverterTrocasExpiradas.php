<?php

namespace App\Console\Commands;

use App\Jobs\ReverterTrocasExpiradasJob;
use Illuminate\Console\Command;

class ReverterTrocasExpiradas extends Command
{
    
    protected $signature = 'trocas:reverter';
    protected $description = 'Dispara job para reverter trocas expiradas';

    public function handle()
    {
        ReverterTrocasExpiradasJob::dispatch();
        $this->info('Job de reversão de trocas expiradas disparado com sucesso!');
    }
}
