<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Top-level directories on the private disk that may be served
     * through this controller. Anything outside these is rejected,
     * so the route can't be abused to read arbitrary private files.
     */
    protected const ALLOWED_DIRECTORIES = [
        'receipts',
        'submissions',
        'attachments',
    ];

    /**
     * Stream a private file to the browser.
     *
     * Authorization is handled by the route's 'auth' + 'admin'
     * middleware — only admins can reach this method.
     */
    public function show(string $path): StreamedResponse
    {
        $path = ltrim($path, '/');

        $allowed = array_map(fn (string $dir) => $dir.'/', self::ALLOWED_DIRECTORIES);
        abort_unless(Str::startsWith($path, $allowed), 403);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
