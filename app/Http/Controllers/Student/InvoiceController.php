<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Dedicated "Invoices & Subscriptions" page for the authenticated student.
     */
    public function index(Request $request)
    {
        $payments = $request->user()->payments()
            ->with('course', 'courseMonth', 'courseMonths')
            ->latest()
            ->get();

        return view('student.invoices', compact('payments'));
    }
}
