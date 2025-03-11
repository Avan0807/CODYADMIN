<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiCampaignNotificationsController extends Controller
{
    /**
     * API: Lấy thông báo chiến dịch cho người dùng đang đăng nhập (user hoặc doctor).
     */
    public function getNotificationsForAuthenticatedUser(Request $request)
    {
        $user = Auth::guard('user')->user();
        $doctor = Auth::guard('doctor')->user();

        if ($user) {
            $notifications = CampaignNotification::where('target_audience', 'user')
                                                 ->orWhere('target_audience', 'both')
                                                 ->latest()
                                                 ->get();
        } elseif ($doctor) {
            $notifications = CampaignNotification::where('target_audience', 'doctor')
                                                 ->orWhere('target_audience', 'both')
                                                 ->latest()
                                                 ->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($notifications);
    }

    /**
     * API: Lấy 5 thông báo chiến dịch mới nhất cho người dùng đang đăng nhập.
     */
    public function getLatestFiveForAuthenticatedUser()
    {
        $user = Auth::guard('user')->user();
        $doctor = Auth::guard('doctor')->user();

        if ($user) {
            $notifications = CampaignNotification::where('target_audience', 'user')
                                                 ->orWhere('target_audience', 'both')
                                                 ->latest()
                                                 ->take(5)
                                                 ->get();
        } elseif ($doctor) {
            $notifications = CampaignNotification::where('target_audience', 'doctor')
                                                 ->orWhere('target_audience', 'both')
                                                 ->latest()
                                                 ->take(5)
                                                 ->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($notifications);
    }

    /**
     * API: Lấy chi tiết một thông báo chiến dịch theo ID.
     */
    public function getNotificationDetail($id)
    {
        $notification = CampaignNotification::findOrFail($id);
        return response()->json($notification);
    }
}
