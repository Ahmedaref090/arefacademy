<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        return view('admin.lessons.create', [
            'course' => $course,
            'lesson' => new Lesson(),
            // Dropdown of months — only populated for per-month courses.
            'months' => $course->months,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $data = $this->validateLesson($request, $course);
        $data['is_free'] = $request->boolean('is_free');

        $lesson = $course->lessons()->create($data);
        $this->syncAttachments($request, $lesson);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Lesson added.');
    }

    public function edit(Lesson $lesson)
    {
        $lesson->load('course.months', 'attachments', 'quizzes.questions', 'assignments');

        return view('admin.lessons.edit', [
            'lesson' => $lesson,
            'months' => $lesson->course->months,
        ]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $data = $this->validateLesson($request, $lesson->course);
        $data['is_free'] = $request->boolean('is_free');

        $lesson->update($data);
        $this->syncAttachments($request, $lesson);

        return back()->with('status', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson)
    {
        $course = $lesson->course;

        // Note: the video file is NOT deleted — videos are placed on the
        // server manually and may be referenced by other lessons.
        foreach ($lesson->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Lesson deleted.');
    }

    public function destroyAttachment(Attachment $attachment)
    {
        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Attachment deleted.');
    }

    protected function validateLesson(Request $request, Course $course): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            // Videos are NOT uploaded through the app (multi-GB files break
            // HTTP uploads). The admin places the file on the server manually
            // (FTP/SSH) inside storage/app/public and types its path here,
            // e.g. "videos/v1.mp4". Directory traversal (..) is rejected.
            'video_path' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
            // Per-month courses: the lesson MUST be assigned to one of the
            // course's own months. Lifetime courses: must stay empty.
            'course_month_id' => [
                $course->isPerMonth() ? 'required' : 'nullable',
                'integer',
                Rule::exists('course_months', 'id')->where('course_id', $course->id),
            ],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
        ]);
    }

    /**
     * Attachments are stored on the PRIVATE disk — they are paid content,
     * served only through the authorized download route.
     */
    protected function syncAttachments(Request $request, Lesson $lesson): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $lesson->attachments()->create([
                'title' => $file->getClientOriginalName(),
                'file_path' => $file->store('attachments', 'local'),
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
