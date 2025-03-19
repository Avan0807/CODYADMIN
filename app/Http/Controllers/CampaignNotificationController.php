<?php

namespace App\Http\Controllers;

use App\Models\CampaignNotification;
use Illuminate\Http\Request;

class CampaignNotificationController extends Controller
{
    /**
     * Hiển thị danh sách thông báo chiến dịch.
     */
    public function index()
    {
        $campaign_notifications = CampaignNotification::latest()->get();
        return view('backend.campaign_notifications.index', compact('campaign_notifications'));
    }

    /**
     * Hiển thị form tạo mới thông báo chiến dịch.
     */
    public function create()
    {
        return view('backend.campaign_notifications.create');
    }

    /**
     * Lưu thông báo chiến dịch mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required',
            'target_audience' => 'required|in:doctor,user,both'
        ]);

        CampaignNotification::create([
            'title' => $request->title,
            'message' => $request->message,
            'target_audience' => strtolower($request->target_audience) // Đảm bảo chữ thường đúng với ENUM
        ]);

        return redirect()->route('campaign_notifications.index')->with('success', 'Thông báo chiến dịch đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết một thông báo chiến dịch.
     */
    public function show($id)
    {
        $campaign_notification = CampaignNotification::findOrFail($id);
        return view('backend.campaign_notifications.show', compact('campaign_notification'));
    }

    /**
     * Hiển thị form chỉnh sửa thông báo chiến dịch.
     */
    public function edit($id)
    {
        $campaign_notification = CampaignNotification::findOrFail($id);
        return view('backend.campaign_notifications.edit', compact('campaign_notification'));
    }

    /**
     * Cập nhật thông báo chiến dịch trong cơ sở dữ liệu.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required',
            'target_audience' => 'required|in:doctor,user,both'
        ]);

        $campaign_notification = CampaignNotification::findOrFail($id);
        $campaign_notification->update($request->all());

        return redirect()->route('campaign_notifications.index')->with('success', 'Thông báo chiến dịch đã được cập nhật.');
    }

    /**
     * Xóa thông báo chiến dịch khỏi cơ sở dữ liệu.
     */
    public function destroy($id)
    {
        $campaign_notification = CampaignNotification::findOrFail($id);
        $campaign_notification->delete();

        return redirect()->route('campaign_notifications.index')->with('success', 'Thông báo chiến dịch đã bị xóa.');
    }
}
