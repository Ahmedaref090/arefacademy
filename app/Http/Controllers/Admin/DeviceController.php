<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * JSON list of the student's registered devices, used by the
     * "Registered Devices" card on the admin student profile page.
     */
    public function index(Request $request, User $user): JsonResponse
    {
        abort_unless($user->isStudent(), 404);

        $devices = $user->devices()
            ->latest('last_active_at')
            ->get()
            ->map(fn (UserDevice $device) => [
                'id' => $device->id,
                'device_type' => $device->deviceType(),
                'platform' => $device->platform(),
                'browser' => $device->browser(),
                'ip' => $device->last_seen_ip,
                'last_active' => $device->last_active_at
                    ? __('since :date', ['date' => $device->last_active_at->format('Y-m-d H:i')])
                    : __('Never'),
            ]);

        return response()->json($devices);
    }

    /**
     * Revoke a single device session. The row is deleted, which immediately
     * lowers the student's registered-device count by one, freeing a slot
     * so they can log in from a new device without the 3-device limit error.
     */
    public function destroy(Request $request, User $user, UserDevice $device)
    {
        abort_unless($user->isStudent(), 404);
        abort_unless($device->user_id === $user->id, 403);

        $device->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', __('Device removed — one device slot freed up.'));
    }
}
