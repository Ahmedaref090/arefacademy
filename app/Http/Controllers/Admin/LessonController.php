<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        return view('admin.lessons.create', [
            'course' => $course,
            'lesson' => new Lesson(),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $data = $this->validateLesson($request);
        $data['is_free'] = $request->boolean('is_free');

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('videos', 'public');
        }

        $lesson = $course->lessons()->create($data);
        $this->syncAttachments($request, $lesson);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Lesson added.');
    }

    public function edit(Lesson $lesson)
    {
        $lesson->load('course', 'attachments', 'quizzes.questions', 'assignments');

        return view('admin.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $data = $this->validateLesson($request);
        $data['is_free'] = $request->boolean('is_free');

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('videos', 'public');
        }

        $lesson->update($data);
        $this->syncAttachments($request, $lesson);

        return back()->with('status', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson)
    {
        $course = $lesson->course;
        $lesson->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Lesson deleted.');
    }

    public function destroyAttachment(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Attachment deleted.');
    }

    protected function validateLesson(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:512000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
        ]);
    }

    protected function syncAttachments(Request $request, Lesson $lesson): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $lesson->attachments()->create([
                'title' => $file->getClientOriginalName(),
                'file_path' => $file->store('attachments', 'public'),
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
