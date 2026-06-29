<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Group;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $items = CartItem::forUser($student->id, 'student')->with(['subject.program', 'group'])->get();

        $totalFee = $items->sum(fn (CartItem $i) => (float) $i->subject->fee);
        $totalMinPayment = $items->sum(fn (CartItem $i) => (float) ($i->subject->min_payment ?? $i->subject->fee));

        return view('student.cart.index', compact('items', 'totalFee', 'totalMinPayment'));
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $alreadyRegistered = Registration::where('student_id', $student->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('registration_status', ['active', 'pending'])
            ->exists();

        if ($alreadyRegistered) {
            return back()->withErrors(['subject_id' => __('app.already_registered')]);
        }

        CartItem::firstOrCreate([
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $request->subject_id,
        ], [
            'group_id' => $request->group_id,
        ]);

        return back()->with('success', __('app.added_to_cart'));
    }

    public function updateGroup(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwnership($cartItem);

        $request->validate(['group_id' => 'required|exists:groups,id']);

        $group = Group::findOrFail($request->group_id);

        if (! $group->hasAvailableCapacity()) {
            return back()->withErrors(['group_id' => __('app.group_full')]);
        }

        $cartItem->update(['group_id' => $group->id]);

        return back()->with('success', __('app.update_success'));
    }

    public function destroy(CartItem $cartItem)
    {
        $this->authorizeOwnership($cartItem);

        $cartItem->delete();

        return back()->with('success', __('app.delete_success'));
    }

    protected function authorizeOwnership(CartItem $cartItem): void
    {
        $student = Auth::guard('student')->user();

        if ($cartItem->user_id !== $student->id || $cartItem->user_type !== 'student') {
            abort(403);
        }
    }
}
