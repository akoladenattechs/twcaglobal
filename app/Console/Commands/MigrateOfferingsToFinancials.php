<?php

namespace App\Console\Commands;

use App\Models\FinancialTransaction;
use App\Models\Offering;
use Illuminate\Console\Command;

class MigrateOfferingsToFinancials extends Command
{
    protected $signature = 'financials:migrate-offerings';

    protected $description = 'Migrate existing offerings records into the new financial_transactions table as inflows';

    public function handle(): int
    {
        $offerings = Offering::all();
        $count = 0;

        foreach ($offerings as $offering) {
            // Map old offering_type to new category
            $categoryMap = [
                'tithe' => 'tithe',
                'offering' => 'offering',
                'special_offering' => 'special_offering',
                'building_fund' => 'building_fund',
                'other' => 'other_income',
            ];

            $category = $categoryMap[$offering->offering_type] ?? 'other_income';

            FinancialTransaction::create([
                'type' => 'inflow',
                'category' => $category,
                'amount' => $offering->amount,
                'payment_method' => $offering->payment_method,
                'transaction_date' => $offering->service_date,
                'description' => $offering->notes,
                'recorded_by' => $offering->recorded_by,
                'status' => 'approved',
                'approved_by' => $offering->recorded_by,
                'approved_at' => $offering->created_at,
                'notes' => 'Migrated from offerings table (original ID: '.$offering->id.')',
                'created_at' => $offering->created_at,
                'updated_at' => $offering->updated_at,
            ]);

            $count++;
        }

        $this->info("Successfully migrated {$count} offering records into financial_transactions.");

        return Command::SUCCESS;
    }
}
