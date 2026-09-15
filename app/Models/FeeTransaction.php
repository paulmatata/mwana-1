<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'term_id',
        'type',
        'amount',
        'running_balance',
        'description',
        'transaction_date',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (self $transaction) {
            static::recalculateBalances($transaction->student_id);
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    /**
     * Appends a new transaction for a student and recalculates that student's
     * entire running-balance ledger in chronological order afterward. Doing a
     * full recalculation (rather than just adding to the last known balance)
     * means a backdated transaction - "record this payment from last week" -
     * still produces a correct balance for every transaction after it, not
     * just the new one. A student's transaction count stays small enough
     * (a handful per term) that this is cheap.
     */
    public static function record(array $data): self
    {
        $transaction = static::create([
            ...$data,
            'running_balance' => 0, // placeholder, corrected immediately below
        ]);

        static::recalculateBalances($data['student_id']);

        return $transaction->fresh();
    }

    public static function recalculateBalances(int $studentId): void
    {
        $balance = 0;

        static::where('student_id', $studentId)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->each(function (self $transaction) use (&$balance) {
                $balance += $transaction->isDebit() ? (float) $transaction->amount : -(float) $transaction->amount;
                $transaction->update(['running_balance' => $balance]);
            });
    }
}
