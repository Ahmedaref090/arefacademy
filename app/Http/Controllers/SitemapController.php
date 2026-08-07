<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Render the XML sitemap on the fly. Content URLs are injected from the
     * actual route table, so it always reflects what's really live. This is
     * a lightweight, DB-backed approach — no storage file to go stale.
     */
    public function index(): Sitemap
    {
        $sitemap = Sitemap::create();

        // Static pages.
        foreach (['home'] as $route) {
            $sitemap->add(Url::create(route($route))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0));
        }

        // Course detail pages — the main crawlable content. Only published
        // courses appear so private/draft content is never indexed.
        Course::query()
            ->where('is_published', true)
            ->latest('updated_at')
            ->get()
            ->each(function (Course $course) use ($sitemap) {
                $sitemap->add(Url::create(route('courses.show', $course))
                    ->setLastModificationDate($course->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
                    // hreflang alternates for the two supported locales.
                    ->addAlternate(route('courses.show', $course), 'en')
                    ->addAlternate(route('courses.show', $course), 'ar'));
            });

        // Expose the alternate/locale variants of the root as well.
        $sitemap->add(Url::create(url('/')));
        $sitemap->add(Url::create(url('/'))->addAlternate(url('/'), 'ar'));

        return $sitemap;
    }

    /**
     * (Optional) cache-free daily rebuild target for a console command —
     * writes sitemap.xml into the public disk (see sitemap:generate below).
     * Kept separate from the route so the two approaches stay independent.
     */
    public static function writeToPublic(): void
    {
        $sitemap = Sitemap::create();

        $sitemap->add(Url::create(route('home'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0));

        Course::query()
            ->where('is_published', true)
            ->latest('updated_at')
            ->get()
            ->each(fn (Course $course) => $sitemap->add(
                Url::create(route('courses.show', $course))
                    ->setLastModificationDate($course->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            ));

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
