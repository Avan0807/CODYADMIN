<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Hash;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
class AdminController extends Controller
{
    public function index(){
        // Sửa lại method getUserRegistrationData như DashboardController
        $users = $this->getUserRegistrationData();
        
        return view('backend.index')->with('users', $users);
    }

    /**
     * Get user registration data for pie chart (copy từ DashboardController)
     */
    private function getUserRegistrationData()
    {
        $users = [];
        $users[] = ['Day', 'Users'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('D'); // Mon, Tue, Wed...
            $count = User::whereDate('created_at', $date->toDateString())->count();
            $users[] = [$dayName, (int)$count];
        }
        
        return json_encode($users);
    }

    // Giữ nguyên các method khác...
    public function profile(){
        $profile=Auth()->user();
        return view('backend.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        // return $request->all();
        $user=User::findOrFail($id);
        $data=$request->all();
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Đã cập nhật hồ sơ của bạn thành công');
        }
        else{
            request()->session()->flash('error','Vui lòng thử lại!');
        }
        return redirect()->back();
    }

    public function settings(){
        $data=Settings::first();
        return view('backend.setting')->with('data',$data);
    }

    public function settingsUpdate(Request $request){
        // return $request->all();
        $this->validate($request,[
            'short_des'=>'required|string',
            'description'=>'required|string',
            'photo'=>'required',
            'logo'=>'required',
            'address'=>'required|string',
            'email'=>'required|email',
            'phone'=>'required|string',
        ]);
        $data=$request->all();
        // return $data;
        $settings=Settings::first();
        // return $settings;
        $status=$settings->fill($data)->save();
        if($status){
            request()->session()->flash('success','Cài đặt đã được cập nhật thành công');
        }
        else{
            request()->session()->flash('error','Vui lòng thử lại');
        }
        return redirect()->route('admin');
    }

    public function changePassword(){
        return view('backend.layouts.changePassword');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
   
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
   
        return redirect()->route('admin')->with('success','Mật khẩu đã được thay đổi thành công');
    }

    // Pie chart
    public function userPieChart(Request $request){
        // dd($request->all());
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDays(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('course', json_encode($array));
    }

    // public function activity(){
    //     return Activity::all();
    //     $activity= Activity::all();
    //     return view('backend.layouts.activity')->with('activities',$activity);
    // }
}
