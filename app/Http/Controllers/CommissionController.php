<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class CommissionController extends Controller
{
    public function index()
    {
        $commissions = DB::table('affiliate_orders')
            ->join('doctors', 'affiliate_orders.doctor_id', '=', 'doctors.id')
            ->select('doctors.id as doctor_id', 'doctors.name as doctor_name', DB::raw('SUM(affiliate_orders.commission) as total_commission'))
            ->where('affiliate_orders.status', 'delivered')
            ->groupBy('doctors.id', 'doctors.name')
            ->orderBy('total_commission', 'DESC')
            ->get();

        return view('backend.commissions.index', compact('commissions'));
    }

    public function show($doctor_id)
    {
        $orders = DB::table('affiliate_orders')
            ->join('orders', 'affiliate_orders.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id') // Thêm join với bảng users
            ->select(
                'orders.id as order_id',
                'users.name as user_name', // Lấy tên từ bảng users
                'orders.phone',
                'affiliate_orders.commission'
            )
            ->where('affiliate_orders.doctor_id', $doctor_id)
            ->where('affiliate_orders.status', 'delivered')
            ->orderBy('orders.id', 'DESC')
            ->get();
    
        return view('backend.commissions.detail', compact('orders'));
    }
    
}
