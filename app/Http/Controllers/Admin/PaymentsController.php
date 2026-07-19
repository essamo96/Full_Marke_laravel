<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Program;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class PaymentsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'payments';
        $this->path = 'payments';
    }

    public function getIndex(Request $request)
    {
        return view('admin.payments.view', self::$data + [
            'programs' => Program::orderBy('sort_order')->get()
        ]);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Payment::with(['student', 'paymentMethod', 'items.registration.subject.program']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('program_id')) {
            $query->whereHas('items.registration.subject', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('name')) {
            $search = $request->name;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name_ar', 'like', "%{$search}%")
                            ->orWhere('full_name_en', 'like', "%{$search}%")
                            ->orWhere('national_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function getList(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        return DataTables::of($query)
            ->addColumn('payment_number', function ($row) {
                return '<span class="text-dark fw-bold">' . ($row->payment_number ?? $row->id) . '</span>';
            })
            ->addColumn('student_info', function ($row) {
                if (!$row->student) return '-';
                $name = app()->getLocale() == 'ar' ? $row->student->full_name_ar : $row->student->full_name_en;
                $email = $row->student->email ?? '';
                
                $imageUrl = $row->student->image 
                    ? asset('storage/' . $row->student->image) 
                    : asset('assets/admin/media/avatars/blank.png');
                    
                return '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-45px me-5">
                        <img src="' . $imageUrl . '" alt=""/>
                    </div>
                    <div class="d-flex justify-content-start flex-column">
                        <span class="text-dark fw-bold mb-1 fs-6">' . $name . '</span>
                        <span class="text-muted fw-semibold d-block fs-7">' . $email . '</span>
                    </div>
                </div>';
            })
            ->addColumn('amount', function ($row) {
                return '<span class="text-success fw-bold fs-6">' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'confirmed') {
                    return '<span class="badge badge-light-success fs-7 fw-bold">' . __('app.status_confirmed') . '</span>';
                } elseif ($row->status === 'rejected') {
                    return '<span class="badge badge-light-danger fs-7 fw-bold" title="' . e($row->rejection_reason) . '">' . __('app.status_rejected') . '</span>';
                } else {
                    return '<span class="badge badge-light-warning fs-7 fw-bold">' . __('app.status_pending') . '</span>';
                }
            })
            ->addColumn('created_date', function ($row) {
                return '<span class="text-muted fw-semibold">' . $row->created_at->format('Y-m-d H:i') . '</span>';
            })
            ->addColumn('actions', function ($row) {
                $html = '';
                if ($row->receipt_image) {
                    $receiptUrl = \Illuminate\Support\Facades\URL::signedRoute('payments.receipt', ['payment' => $row->id]);
                    $html .= '<a href="javascript:void(0)" class="btn btn-sm btn-light-info view-receipt" data-image="' . $receiptUrl . '">' . __('app.receipt') . '</a> ';
                }
                if ($row->status === 'confirmed') {
                    $html .= '<button type="button" class="btn btn-sm btn-light-primary ms-1 view-invoice" data-url="' . route('payments.invoice', $row->id) . '"><i class="bi bi-file-earmark-text"></i> ' . __('app.invoice') . '</button>';
                }
                return $html;
            })
            ->rawColumns(['payment_number', 'student_info', 'amount', 'status_badge', 'created_date', 'actions'])
            ->make(true);
    }

    public function invoice(Payment $payment)
    {
        $payment->load(['student', 'items.registration.subject.program']);
        return view('admin.payments.invoice', compact('payment'));
    }

    public function exportExcel(Request $request)
    {
        $payments = $this->getFilteredQuery($request)->get();

        $fileName = 'payments_' . date('Y_m_d_H_i') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            __('app.payment_number'),
            __('app.student'),
            __('app.phone_number'),
            __('app.email'),
            __('app.amount'),
            __('app.status'),
            __('app.date')
        ];

        $callback = function() use($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                $studentName = app()->getLocale() == 'ar' ? ($payment->student->full_name_ar ?? '') : ($payment->student->full_name_en ?? '');
                
                $statusMap = [
                    'confirmed' => __('app.status_confirmed'),
                    'rejected'  => __('app.status_rejected'),
                    'pending'   => __('app.status_pending'),
                ];
                $statusText = $statusMap[$payment->status] ?? $payment->status;

                fputcsv($file, [
                    $payment->payment_number ?? $payment->id,
                    $studentName,
                    $payment->student->phone ?? '',
                    $payment->student->email ?? '',
                    $payment->amount,
                    $statusText,
                    $payment->created_at->format('Y-m-d H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function delete(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => 'Not implemented yet.'
        ]);
    }

    public function status(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => 'Not implemented yet.'
        ]);
    }
}
