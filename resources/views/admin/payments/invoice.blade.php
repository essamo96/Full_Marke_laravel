<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة - {{ $payment->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .items-table th {
            background-color: #f8f9fa;
        }
        .total-section {
            text-align: left;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>أكاديمية الدروس الخصوصية</h1>
        <h2>فاتورة دفع</h2>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td><strong>رقم الفاتورة:</strong> {{ $payment->invoice_number }}</td>
                <td><strong>تاريخ الإصدار:</strong> {{ $payment->confirmed_at ? $payment->confirmed_at->format('Y-m-d') : $payment->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><strong>اسم الطالب:</strong> {{ app()->getLocale() == 'ar' ? $payment->student->full_name_ar : $payment->student->full_name_en }}</td>
                <td><strong>رقم الهاتف:</strong> {{ $payment->student->phone }}</td>
            </tr>
            <tr>
                <td><strong>طريقة الدفع:</strong> {{ $payment->paymentMethod ? (app()->getLocale() == 'ar' ? $payment->paymentMethod->name_ar : $payment->paymentMethod->name_en) : 'بنكي' }}</td>
                <td><strong>الحالة:</strong> مؤكد</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>المادة الدراسية</th>
                <th>قيمة التوزيع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->items as $item)
                <tr>
                    <td>{{ app()->getLocale() == 'ar' ? $item->registration->subject->name_ar : $item->registration->subject->name_en }}</td>
                    <td>{{ number_format($item->allocated_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p>إجمالي المدفوع: {{ number_format($payment->amount, 2) }}</p>
    </div>
</body>
</html>
