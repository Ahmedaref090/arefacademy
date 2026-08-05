<?php

namespace App\Http\Requests;

use App\Enums\PurchaseStatus;
use App\Models\Course;
use App\Models\CourseMonth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        /** @var Course $course */
        $course = $this->route('course');

        return [
            // Per-month courses REQUIRE the selected month, and it must belong
            // to this course. Lifetime courses must not send one at all.
            'course_month_id' => [
                $course->isPerMonth() ? 'required' : 'prohibited',
                'integer',
                Rule::exists('course_months', 'id')->where('course_id', $course->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'course_month_id.required' => 'Please select the month you want to subscribe to.',
        ];
    }

    /**
     * Double-subscription prevention: reject the request when a purchase
     * record already exists for this student with status approved/pending.
     * (A "rejected" record is allowed through — the controller resubmits it
     * as pending.)
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Course $course */
            $course = $this->route('course');
            $user = $this->user();

            if ($course->isPerMonth()) {
                $month = CourseMonth::findOrFail($this->integer('course_month_id'));
                $status = $user->purchaseStatusForMonth($month);

                if (in_array($status, [PurchaseStatus::Approved, PurchaseStatus::Pending], true)) {
                    $validator->errors()->add(
                        'course_month_id',
                        'You have already purchased this month or your request is pending.'
                    );
                }
            } else {
                $status = $user->purchaseStatusForCourse($course);

                if (in_array($status, [PurchaseStatus::Approved, PurchaseStatus::Pending], true)) {
                    $validator->errors()->add(
                        'course',
                        'You have already purchased this course or your request is pending.'
                    );
                }
            }
        });
    }
}
