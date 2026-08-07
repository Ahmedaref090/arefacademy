<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    /** Contact/support page available to signed-in students. */
    public function index()
    {
        return view('student.contact');
    }
}
