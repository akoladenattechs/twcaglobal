<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;

class NewsletterCleanupPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:cleanup-pending {--hours=48 : Hours after which unconfirmed (pending) subscribers are deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending newsletter subscribers who never confirmed within the given hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));

        $count = NewsletterSubscriber::purgeStalePending($hours);

        $this->info("Deleted {$count} pending subscriber(s) unconfirmed for more than {$hours} hours.");

        return Command::SUCCESS;
    }
}
