<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

     public function index()
     {
         $patients = \App\Models\Patient::paginate(10); // Phân trang với 10 dòng mỗi trang
         return view('doctor.patients.index', compact('patients'));
     }
     
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('doctor.patients.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_check_in' => 'required|date',
            'patient_name' => 'required|string|max:255',
            'doctor_assigned' => 'required|string|max:255',
            'disease' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'room_no' => 'required|string|max:50',
        ]);
    
        \App\Models\Patient::create($request->all());
    
        return redirect()->route('patients.index')->with('success', 'Bệnh nhân đã được thêm thành công!');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $patient = \App\Models\Patient::findOrFail($id);
        return view('doctor.patients.edit', compact('patient'));
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'date_check_in' => 'required|date',
            'patient_name' => 'required|string|max:255',
            'doctor_assigned' => 'required|string|max:255',
            'disease' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'room_no' => 'required|string|max:50',
        ]);
    
        $patient = \App\Models\Patient::findOrFail($id);
        $patient->update($request->all());
    
        return redirect()->route('patients.index')->with('success', 'Thông tin bệnh nhân đã được cập nhật!');
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function destroy($id)
    {
        $patient = \App\Models\Patient::findOrFail($id);
        $patient->delete();
    
        return redirect()->route('patients.index')->with('success', 'Bệnh nhân đã được xóa!');
    }
    
}
