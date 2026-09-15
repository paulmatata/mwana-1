<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\BulkStoreFeeChargesRequest;
use App\Http\Requests\Principal\StoreFeeChargeRequest;
use App\Http\Requests\Principal\StoreFeePaymentRequest;
use App\Models\FeeTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * One row per student who has any fee history at all, showing their
     * current balance (their latest transaction's running_balance) - the
     * ledger detail for any one student lives on the dedicated ledger() page.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $students = Student::where('school_id', $schoolId)
            ->whereHas('feeTransactions')
            ->with('schoolClass')
            ->addSelect(['current_balance' => FeeTransaction::selectRaw('running_balance')
                ->whereColumn('student_id', 'students.id')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(1),
            ])
            ->when($request->filled('class_id'), fn ($q) => $q->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('admission_no', 'like', "%{$term}%"));
            })
            // Highest balance owed first - that's who a principal needs to chase up.
            ->orderByDesc('current_balance')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        return view('principal.fees.index', compact('students', 'classes'));
    }

    /**
     * A single student's full statement: every transaction in order, with the
     * running balance after each one - this is the actual "history" view.
     */
    public function ledger(Request $request, Student $student)
    {
        abort_unless($student->school_id === $request->user()->school_id, 404);

        $student->load('schoolClass');
        $transactions = $student->feeTransactions()->with(['academicTerm', 'recordedBy'])->get();

        return view('principal.fees.ledger', compact('student', 'transactions'));
    }

    public function createCharge(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $students = Student::where('school_id', $schoolId)->where('status', 'active')->orderBy('name')->get();
        $terms = Term::where('school_id', $schoolId)->orderByDesc('year')->orderBy('name')->get();

        return view('principal.fees.create-charge', compact('students', 'terms'));
    }

    public function storeCharge(StoreFeeChargeRequest $request)
    {
        FeeTransaction::record([
            'school_id' => $request->user()->school_id,
            'student_id' => $request->validated('student_id'),
            'term_id' => $request->validated('term_id'),
            'type' => 'debit',
            'amount' => $request->validated('amount'),
            'description' => $request->validated('description'),
            'transaction_date' => $request->validated('transaction_date') ?? now()->format('Y-m-d'),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('principal.fees.index')->with('success', 'Charge recorded.');
    }

    public function createPayment(Request $request, Student $student)
    {
        abort_unless($student->school_id === $request->user()->school_id, 404);

        $terms = Term::where('school_id', $request->user()->school_id)->orderByDesc('year')->orderBy('name')->get();

        return view('principal.fees.create-payment', compact('student', 'terms'));
    }

    public function storePayment(StoreFeePaymentRequest $request, Student $student)
    {
        abort_unless($student->school_id === $request->user()->school_id, 404);

        FeeTransaction::record([
            'school_id' => $request->user()->school_id,
            'student_id' => $student->id,
            'term_id' => $request->validated('term_id'),
            'type' => 'credit',
            'amount' => $request->validated('amount'),
            'description' => $request->validated('description') ?: 'Payment received',
            'transaction_date' => $request->validated('transaction_date') ?? now()->format('Y-m-d'),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('principal.fees.ledger', $student)->with('success', 'Payment recorded.');
    }

    public function destroy(Request $request, FeeTransaction $transaction)
    {
        abort_unless($transaction->school_id === $request->user()->school_id, 404);

        $student = $transaction->student;
        $transaction->delete(); // model event recalculates the student's running balances

        return redirect()->route('principal.fees.ledger', $student)->with('success', 'Transaction removed and balance recalculated.');
    }

    /**
     * Bulk charge posting: apply the same debit to every active student in a
     * class, or the whole school, in one action.
     */
    public function bulkForm(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $terms = Term::where('school_id', $schoolId)->orderByDesc('year')->orderBy('name')->get();

        return view('principal.fees.bulk', compact('classes', 'terms'));
    }

    public function bulkStore(BulkStoreFeeChargesRequest $request)
    {
        $schoolId = $request->user()->school_id;

        $studentsQuery = Student::where('school_id', $schoolId)->where('status', 'active');

        if ($request->validated('school_class_id')) {
            $studentsQuery->where('school_class_id', $request->validated('school_class_id'));
        }

        $students = $studentsQuery->get();

        foreach ($students as $student) {
            FeeTransaction::record([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'term_id' => $request->validated('term_id'),
                'type' => 'debit',
                'amount' => $request->validated('amount'),
                'description' => $request->validated('description'),
                'transaction_date' => $request->validated('transaction_date') ?? now()->format('Y-m-d'),
                'recorded_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('principal.fees.index')
            ->with('success', "Charge posted to {$students->count()} student(s).");
    }
}
