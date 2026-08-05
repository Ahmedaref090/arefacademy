<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * All purchase requests (lifetime + per-month), newest first,
     * so the admin can approve/reject them.
     */
    public function index()
    {
        $courseRequests = DB::table('course_user')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->select(
                'course_user.id',
                'course_user.status',
                'course_user.created_at',
                'users.name as student_name',
                'courses.title as course_title'
            )
            ->orderByDesc('course_user.created_at')
            ->paginate(15, ['*'], 'courses_page');

        $monthRequests = DB::table('course_month_user')
            ->join('users', 'users.id', '=', 'course_month_user.user_id')
            ->join('course_months', 'course_months.id', '=', 'course_month_user.course_month_id')
            ->join('courses', 'courses.id', '=', 'course_months.course_id')
            ->select(
                'course_month_user.id',
                'course_month_user.status',
                'course_month_user.created_at',
                'users.name as student_name',
                'courses.title as course_title',
                'course_months.name as month_name'
            )
            ->orderByDesc('course_month_user.created_at')
            ->paginate(15, ['*'], 'months_page');

        return view('admin.subscriptions.index', compact('courseRequests', 'monthRequests'));
    }

    public function approveCourse(int $id)
    {
        return $this->setStatus('course_user', $id, PurchaseStatus::Approved);
    }

    public function rejectCourse(int $id)
    {
        return $this->setStatus('course_user', $id, PurchaseStatus::Rejected);
    }

    public function approveMonth(int $id)
    {
        return $this->setStatus('course_month_user', $id, PurchaseStatus::Approved);
    }

    public function rejectMonth(int $id)
    {
        return $this->setStatus('course_month_user', $id, PurchaseStatus::Rejected);
    }

    protected function setStatus(string $table, int $id, PurchaseStatus $status)
    {
        DB::table($table)->where('id', $id)->update([
            'status' => $status->value,
            'updated_at' => now(),
        ]);

        return back()->with('status', "Request {$status->value}.");
    }
}
