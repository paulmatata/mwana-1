<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('fee_records')) {
            return; // nothing to migrate (fresh install)
        }

        $records = DB::table('fee_records')->orderBy('student_id')->orderBy('due_date')->orderBy('id')->get();

        $termIdCache = [];
        $runningBalances = []; // student_id => current running balance

        foreach ($records as $record) {
            $termId = null;

            if ($record->term && $record->year) {
                $cacheKey = $record->school_id.'|'.$record->term.'|'.$record->year;

                if (! isset($termIdCache[$cacheKey])) {
                    $termId = DB::table('terms')->where([
                        'school_id' => $record->school_id,
                        'name' => $record->term,
                        'year' => $record->year,
                    ])->value('id');

                    if (! $termId) {
                        $termId = DB::table('terms')->insertGetId([
                            'school_id' => $record->school_id,
                            'name' => $record->term,
                            'year' => $record->year,
                            'is_current' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $termIdCache[$cacheKey] = $termId;
                }

                $termId = $termIdCache[$cacheKey];
            }

            $balance = $runningBalances[$record->student_id] ?? 0;
            $transactionDate = $record->due_date ?? DB::table('fee_records')->where('id', $record->id)->value('created_at') ?? now()->format('Y-m-d');
            $label = trim(($record->term ?? '').' '.($record->year ?? ''));
            $label = $label !== '' ? $label : 'fees';

            // The original charge, carried over as a debit.
            if ((float) $record->amount_due > 0) {
                $balance += (float) $record->amount_due;

                DB::table('fee_transactions')->insert([
                    'school_id' => $record->school_id,
                    'student_id' => $record->student_id,
                    'term_id' => $termId,
                    'type' => 'debit',
                    'amount' => $record->amount_due,
                    'running_balance' => $balance,
                    'description' => "Migrated charge - {$label}".($record->notes ? ' ('.$record->notes.')' : ''),
                    'transaction_date' => $transactionDate,
                    'recorded_by' => $record->recorded_by,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }

            // Any amount already recorded as paid, carried over as a credit right after.
            if ((float) $record->amount_paid > 0) {
                $balance -= (float) $record->amount_paid;

                DB::table('fee_transactions')->insert([
                    'school_id' => $record->school_id,
                    'student_id' => $record->student_id,
                    'term_id' => $termId,
                    'type' => 'credit',
                    'amount' => $record->amount_paid,
                    'running_balance' => $balance,
                    'description' => "Migrated payment - {$label}",
                    'transaction_date' => $transactionDate,
                    'recorded_by' => $record->recorded_by,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }

            $runningBalances[$record->student_id] = $balance;
        }

        DB::getSchemaBuilder()->dropIfExists('fee_records');
    }

    public function down(): void
    {
        // Not reversible - fee_records is gone and fee_transactions is the system
        // of record from this point forward. Restore from a database backup if needed.
    }
};
