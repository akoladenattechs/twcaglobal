<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsletterCampaign;
use App\Models\Newsletter;
use Illuminate\Console\Command;

class NewsletterSendScheduled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all scheduled newsletters that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $due = Newsletter::dueForSending()->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled newsletters due for sending.');

            return Command::SUCCESS;
        }

        foreach ($due as $newsletter) {
            $this->info("Queueing newsletter #{$newsletter->id}: {$newsletter->subject}");

            $newsletter->update(['status' => 'sending']);

            SendNewsletterCampaign::dispatch($newsletter);
        }

        $this->info('All due scheduled newsletters queued.');

        return Command::SUCCESS;
    }
}
