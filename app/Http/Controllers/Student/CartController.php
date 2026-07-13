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
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
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

        // Group selection is optional — a student may clear it back to "no
        // group / later" just as freely as picking one.
        $request->validate(['group_id' => 'nullable|exists:groups,id']);

        if (! $request->filled('group_id')) {
            $cartItem->update(['group_id' => null]);

            return back()->with('success', __('app.update_success'));
        }

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

    public function sync(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'items' => 'required|array',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.group_id' => 'nullable|exists:groups,id',
        ]);

        // Clear existing cart items
        CartItem::where('user_id', $student->id)->where('user_type', 'student')->delete();

        // Add new items
        foreach ($request->items as $item) {
            $alreadyRegistered = Registration::where('student_id', $student->id)
                ->where('subject_id', $item['subject_id'])
                ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
                ->exists();

            if (!$alreadyRegistered) {
                CartItem::firstOrCreate([
                    'user_id' => $student->id,
                    'user_type' => 'student',
                    'subject_id' => $item['subject_id'],
                ], [
                    'group_id' => $item['group_id'] ?? null,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'redirect' => route('student.checkout'),
        ]);
    }
}
