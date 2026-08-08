<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Revoke a single registered device. The row is deleted so the student's
     * device list stays clean. There is no device-count cap — this is purely
     * a monitoring/cleanup action.
     */
    public function destroy(Request $request, User $user, UserDevice $device)
    {
        abort_unless($user->isStudent(), 404);
        abort_unless($device->user_id === $user->id, 403);

        $device->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', __('Device removed.'));
    }

    /**
     * Manually block a student's account. Once blocked, the student can no
     * longer log in and sees the locale-aware "maximum devices" message.
     * Existing sessions are destroyed so the block takes effect immediately.
     */
    public function block(Request $request, User $user)
    {
        abort_unless($user->isStudent(), 403);

        $user->blockLogin();

        $this->terminateSessions($user);

        return $this->respond($request, __('Login blocked for :name.', ['name' => $user->name]));
    }

    /**
     * Lift the manual login block so the student can sign in again.
     */
    public function unblock(Request $request, User $user)
    {
        abort_unless($user->isStudent(), 403);

        $user->unblockLogin();

        return $this->respond($request, __('Login re-enabled for :name.', ['name' => $user->name]));
    }

    /**
     * Terminate every active web session of this student (the sessions are
     * stored in the database), forcing a fresh login attempt that the
     * login-block check will reject.
     */
    protected function terminateSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    protected function respond(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
