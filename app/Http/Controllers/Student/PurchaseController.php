<?php

namespace App\Http\Controllers\Student;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Models\Course;
use App\Models\CourseMonth;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PurchaseController extends Controller
{
    /**
     * Submit a purchase request. Creates a PENDING record in course_user
     * (lifetime) or course_month_user (per-month). Duplicate approved/pending
     * requests are rejected by PurchaseRequest validation.
     */
    public function store(PurchaseRequest $request, Course $course)
    {
        $user = $request->user();

        if ($course->isPerMonth()) {
            $month = CourseMonth::findOrFail($request->integer('course_month_id'));
            $this->subscribe($user->courseMonths(), $month->id);

            return back()->with('status', "Your request for \"{$month->name}\" has been submitted and is pending approval.");
        }

        $this->subscribe($user->purchasedCourses(), $course->id);

        return back()->with('status', 'Your purchase request has been submitted and is pending approval.');
    }

    /**
     * Attach a fresh pending record, or flip a previously REJECTED record
     * back to pending (approved/pending duplicates never reach this point —
     * the FormRequest blocks them).
     */
    protected function subscribe(BelongsToMany $relation, int $id): void
    {
        $relatedKey = $relation->getRelated()->getQualifiedKeyName();

        if ($relation->where($relatedKey, $id)->exists()) {
            $relation->updateExistingPivot($id, ['status' => PurchaseStatus::Pending->value]);
        } else {
            $relation->attach($id, ['status' => PurchaseStatus::Pending->value]);
        }
    }
}
