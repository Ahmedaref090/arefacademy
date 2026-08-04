<?php

namespace App\Providers;

use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Build the student's notification feed for the main layout,
        // keeping database queries out of the Blade template.
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $notifications = collect();

            if ($user && $user->isStudent()) {
                foreach ($user->payments()->with('course')->where('status', PaymentStatus::Paid)->latest('paid_at')->limit(3)->get() as $p) {
                    $notifications->push([
                        'icon' => '💳',
                        'text' => 'Payment confirmed — "' . $p->course->title . '" is unlocked',
                        'url' => route('courses.show', $p->course),
                        'time' => $p->paid_at,
                    ]);
                }

                foreach ($user->submissions()->with('assignment')->whereNotNull('graded_at')->latest('graded_at')->limit(3)->get() as $s) {
                    $notifications->push([
                        'icon' => '📝',
                        'text' => '"' . $s->assignment->title . '" graded: ' . $s->score . '/' . $s->assignment->max_score,
                        'url' => route('lessons.show', $s->assignment->lesson_id),
                        'time' => $s->graded_at,
                    ]);
                }

                $notifications = $notifications->sortByDesc('time')->take(5)->values();
            }

            $view->with('notifications', $notifications);
        });
    }
}
