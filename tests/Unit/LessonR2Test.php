<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonR2Test extends TestCase
{
    use RefreshDatabase;

    /** Point the r2 disk at a local (fake) disk so tests need no network. */
    protected function fakeR2Disk(string $fakeDriver = 'local'): void
    {
        $dir = sys_get_temp_dir().'/r2_'.str()->random(6);
        Config::set('filesystems.disks.r2', [
            'driver' => $fakeDriver,
            'root' => $dir,
        ]);
        Storage::forgetDisk('r2');
    }

    public function test_is_stored_on_r2_detects_r2_object_keys(): void
    {
        $this->fakeR2Disk();
        Storage::disk('r2')->put('videos/a.mp4', 'data');

        $lesson = Lesson::factory()->create([
            'course_id' => Course::factory(),
            'video_path' => 'videos/a.mp4',
        ]);

        $this->assertTrue($lesson->isStoredOnR2());
        $this->assertStringContainsString('r2', $lesson->videoSrc() ?? '', '');
    }

    public function test_public_disk_paths_are_not_treated_as_r2(): void
    {
        $this->fakeR2Disk();
        Storage::disk('r2')->put('storage/videos/b.mp4', 'data');

        $lesson = Lesson::factory()->create([
            'course_id' => Course::factory(),
            'video_path' => '/storage/videos/b.mp4',
        ]);

        $this->assertFalse($lesson->isStoredOnR2());
        $this->assertStringContainsString('/storage', $lesson->videoSrc() ?? '');
    }

    public function test_deleting_lesson_removes_video_from_r2(): void
    {
        $this->fakeR2Disk();
        $key = 'videos/delete-me.mp4';
        Storage::disk('r2')->put($key, 'data');
        $this->assertTrue(Storage::disk('r2')->exists($key));

        $lesson = Lesson::factory()->create([
            'course_id' => Course::factory(),
            'video_path' => $key,
        ]);

        $lesson->delete();

        $this->assertFalse(Storage::disk('r2')->exists($key));
    }

    public function test_deleting_lesson_without_r2_video_does_not_error(): void
    {
        $this->fakeR2Disk();

        $lesson = Lesson::factory()->create([
            'course_id' => Course::factory(),
            'video_path' => 'storage-relative-only',
        ]);

        // No exception should be thrown even though the file isn't on R2.
        $lesson->delete();
        $this->assertTrue(true);
    }
}
