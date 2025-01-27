<?php

namespace App\Console\Commands;

use App\Jobs\ProcessRawMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessPendingMessages extends Command
{
    protected $signature = 'messages:process-pending';
    protected $description = 'Process all pending raw messages';

    public function handle()
    {
        $pendingMessages = DB::table('raw_messages')
            ->where('processed', false)
            ->get();

        $this->info("Found {$pendingMessages->count()} pending messages");

        foreach ($pendingMessages as $message) {
            ProcessRawMessage::dispatch($message->id);
            $this->info("Dispatched processing for message {$message->id}");
        }

        $this->info('All pending messages have been queued for processing');
    }
}