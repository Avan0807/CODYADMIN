<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class PhoneSupportController extends Controller
{
    public function index()
    {
        $supports = DB::table('phone_support')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('backend.phone_support.index', compact('supports'));
    }
}
