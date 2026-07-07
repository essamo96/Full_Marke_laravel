<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PaymentMethodsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'payment_methods';
        $this->path = 'payment_methods';
    }

    public function getIndex()
    {
        $methods = PaymentMethod::orderBy('sort_order')->paginate(15);

        return view('admin.payment_methods.view', self::$data + ['methods' => $methods]);
    }

    public function getAdd()
    {
        return view('admin.payment_methods.add', self::$data + ['info' => null]);
    }

    public function postAdd(PaymentMethodRequest $request)
    {
        PaymentMethod::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('payment_methods.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $method = PaymentMethod::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('payment_methods.view')->with('danger', __('app.not_found'));
        }

        return view('admin.payment_methods.add', self::$data + ['info' => $method]);
    }

    public function postEdit(PaymentMethodRequest $request, $id)
    {
        try {
            $method = PaymentMethod::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('payment_methods.view')->with('danger', __('app.not_found'));
        }

        $method->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('payment_methods.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $method = PaymentMethod::findOrFail(Crypt::decrypt($request->id));
            $method->update(['is_active' => ! $method->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $method = PaymentMethod::findOrFail(Crypt::decrypt($request->id));

            if ($method->payments()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $method->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
