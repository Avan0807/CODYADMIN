<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateSettingsRequest;
use Hash;

class AdminController extends Controller
{

    private function getUserStatistics()
    {
        return Cache::remember('user_statistics', 60, function () {
            return User::selectRaw("COUNT(*) as count, DAYNAME(created_at) as day_name, DAY(created_at) as day")
                ->where('created_at', '>', Carbon::today()->subDays(6))
                ->groupBy('day_name', 'day')
                ->orderBy('day')
                ->get();
        });
    }
    
    public function index()
    {
        $data = $this->getUserStatistics();
        $array[] = ['Name', 'Number'];
        foreach ($data as $key => $value) {
            $array[++$key] = [$value->day_name, $value->count];
        }
        return view('backend.index', ['users' => json_encode($array)]);
    }
    
    public function userPieChart()
    {
        $data = $this->getUserStatistics();
        $array[] = ['Name', 'Number'];
        foreach ($data as $key => $value) {
            $array[++$key] = [$value->day_name, $value->count];
        }
        return view('backend.index', ['course' => json_encode($array)]);
    }
    

    public function profile()
    {
        return view('backend.users.profile', ['profile' => Auth::user()]);
    }

    public function profileUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'email', 'phone', 'address']);
    
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
    
        $status = $user->update($data);
    
        if ($status) {
            Cache::forget('user_profile_' . $id);
            session()->flash('success', 'Đã cập nhật hồ sơ của bạn thành công');
        } else {
            session()->flash('error', 'Vui lòng thử lại!');
        }
    
        return redirect()->back();
    }
    

    public function settings()
    {
        $data = Cache::remember('settings_data', 3600, function () {
            return Settings::first();
        });
    
        return view('backend.setting', ['data' => $data]);
    }
    

    public function settingsUpdate(UpdateSettingsRequest $request)
    {
        $settings = Settings::first();
        $status = $settings->update($request->validated());
    
        if ($status) {
            Cache::forget('settings_data'); // Xóa cache để cập nhật dữ liệu mới
            session()->flash('success', 'Cài đặt đã được cập nhật thành công');
        } else {
            session()->flash('error', 'Vui lòng thử lại');
        }
        return redirect()->route('admin');
    }
    
}
