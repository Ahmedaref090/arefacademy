<?php

namespace App\Providers;

use App\Enums\PaymentStatus;
use App\Translators\MissingKeyLoggerTranslator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;

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
        $this->registerMissingKeyTranslator();

        // Make the public-disk URL request-aware. Without this, Storage::url()
        // uses the raw APP_URL and emits /storage/... which 404s under any
        // host, port, or subdirectory docroot (php artisan serve, sub-folder
        // Apache setup, etc.). The disk URL is resolved lazily, so updating
        // it here fixes every Storage::disk('public')->url() call site.
        config(['filesystems.disks.public.url' => URL::to('/storage')]);

        // Build the student's notification feed for the main layout,
        // keeping database queries out of the Blade template.
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $notifications = collect();

            if ($user && $user->isStudent()) {
                foreach ($user->payments()->with('course')->where('status', PaymentStatus::Approved)->latest('paid_at')->limit(3)->get() as $p) {
                    $notifications->push([
                        'icon' => '💳',
                        'text' => __('Payment confirmed — ":title" is unlocked', ['title' => $p->course->title]),
                        'url' => route('courses.show', $p->course),
                        'time' => $p->paid_at,
                    ]);
                }

                foreach ($user->submissions()->with('assignment')->whereNotNull('graded_at')->latest('graded_at')->limit(3)->get() as $s) {
                    $notifications->push([
                        'icon' => '📝',
                        'text' => __('":title" graded: :score/:max', [
                            'title' => $s->assignment->title,
                            'score' => $s->score,
                            'max' => $s->assignment->max_score,
                        ]),
                        'url' => route('lessons.show', $s->assignment->lesson_id),
                        'time' => $s->graded_at,
                    ]);
                }

                $notifications = $notifications->sortByDesc('time')->take(5)->values();
            }

            $view->with('notifications', $notifications);
        });
    }

    /**
     * Replace the framework's deferred translator with a subclass that logs
     * missing translation keys in local/dev, so gaps surface immediately.
     */
    protected function registerMissingKeyTranslator(): void
    {
        // The framework binds 'translator' through a deferred provider that
        // re-binds (overwriting ours) on first resolve — even if a binding
        // already exists. Removing it from the deferred list prevents that.
        $this->app->removeDeferredServices(['translator', 'translation.loader']);

        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader(
                $app['files'],
                [
                    $app->basePath('vendor/laravel/framework/src/Illuminate/Translation/lang'),
                    $app->langPath(),
                ]
            );
        });

        $this->app->singleton('translator', function ($app) {
            $translator = new MissingKeyLoggerTranslator($app['translation.loader'], $app->getLocale());
            $translator->setFallback($app->getFallbackLocale());

            return $translator;
        });
    }
}
