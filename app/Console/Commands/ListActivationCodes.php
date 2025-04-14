<?php

namespace App\Console\Commands;

use App\Models\ActivationCode;
use Illuminate\Console\Command;

class ListActivationCodes extends Command
{
    protected $signature = 'activation-codes:list';
    protected $description = 'List all activation codes';

    public function handle()
    {
        $codes = ActivationCode::all();

        if ($codes->isEmpty()) {
            $this->info('No activation codes found.');
            return;
        }

        $this->table(
            ['Code', 'Amount'],
            $codes->map(function ($code) {
                return [
                    'code' => $code->code,
                    'amount' => $code->amount
                ];
            })
        );
    }
}
