<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the site locale (ar default, en optional) and remember it.
     */
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['ar', 'en']), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back();
    }
}
