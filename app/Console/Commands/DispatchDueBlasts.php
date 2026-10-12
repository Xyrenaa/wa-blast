<?php

namespace App\Console\Commands;

use App\Jobs\SendBlastMessage;
use App\Models\Blast;
use Illuminate\Console\Command;

class DispatchDueBlasts extends Command
{
    protected $signature = 'blasts:dispatch-due';

    protected $description = 'Mulai blast terjadwal yang waktunya sudah tiba';

    public function handle(): int
    {
        $due = Blast::where('status', 'scheduled')->where('scheduled_at', '<=', now())->get();

        foreach ($due as $blast) {
            // Klaim dulu supaya tidak dimulai dua kali
            $claimed = Blast::where('id', $blast->id)->where('status', 'scheduled')->update(['status' => 'queued']);
            if ($claimed) {
                SendBlastMessage::dispatch($blast->id);
                $this->info("Blast #{$blast->id} dimulai.");
            }
        }

        return self::SUCCESS;
    }
}
