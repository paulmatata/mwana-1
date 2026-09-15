<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePrincipalRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrincipalController extends Controller
{
    /**
     * Create a principal account for a school. The password is auto-generated
     * and shown once to the super admin to hand over to the principal - Mwana
     * never emails/SMSs credentials on its own in this phase.
     */
    public function store(StorePrincipalRequest $request, School $school)
    {
        $tempPassword = Str::password(12, symbols: false);

        $principal = User::create([
            'role' => 'principal',
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($tempPassword),
            'school_id' => $school->id,
            'status' => 'active',
        ]);

        return redirect()
            ->route('super-admin.schools.show', $school)
            ->with('success', "Principal account created for {$principal->name}.")
            ->with('generated_password', $tempPassword)
            ->with('generated_for', $principal->name);
    }

    public function toggleStatus(School $school, User $principal)
    {
        abort_unless($principal->school_id === $school->id && $principal->role === 'principal', 404);

        $principal->update([
            'status' => $principal->status === 'active' ? 'suspended' : 'active',
        ]);

        $message = $principal->status === 'active'
            ? "{$principal->name} has been reactivated."
            : "{$principal->name}'s access has been suspended.";

        return back()->with('success', $message);
    }

    public function resetPassword(School $school, User $principal)
    {
        abort_unless($principal->school_id === $school->id && $principal->role === 'principal', 404);

        $tempPassword = Str::password(12, symbols: false);

        $principal->update(['password' => Hash::make($tempPassword)]);

        return back()
            ->with('success', "Password reset for {$principal->name}.")
            ->with('generated_password', $tempPassword)
            ->with('generated_for', $principal->name);
    }
}
