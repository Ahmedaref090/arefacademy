    public function pay(Request $request, Course $course, FawryPaymentService $fawry)
    {
        abort_unless($course->
