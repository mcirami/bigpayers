<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Salary;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class SalaryController extends Controller
{


    public function create($id, Request $request)
    {
        $user = User::withRole(Privilege::ROLE_AFFILIATE)->myUsers()->findOrFail($id);

        $this->validate($request, [
            'salary' => 'required|numeric',
            'status' => 'required|numeric',
        ]);

        $salary = new Salary;
        $salary->salary = $request->input('salary');
        $salary->status = $request->input('status');
        $salary->timestamp = Carbon::now()->timestamp;
        $salary->last_update = Carbon::now()->timestamp;

        $user->salary()->save($salary);




        return redirect()->route('salary.update', $id);
    }

    public function showCreate($id)
    {
        $user = User::withRole(Privilege::ROLE_AFFILIATE)->myUsers()->findOrFail($id);


        return view('salary.show', compact('user'));
    }


    public function update($id, Request $request)
    {
        $user = User::withRole(Privilege::ROLE_AFFILIATE)->myUsers()->findOrFail($id);

        $salary = $user->salary;

        $this->validate($request, [
            'salary' => 'required|numeric',
            'status' => 'required|numeric',
        ]);

        $salary->salary = $request->input('salary');
        $salary->status = $request->input('status');
        $salary->last_update = Carbon::now()->timestamp;

        $salary->save();


        return back()->with(['messages' => ['Success']]);
    }

    public function showUpdate($id)
    {
        $user = User::withRole(Privilege::ROLE_AFFILIATE)->myUsers()->findOrFail($id);

        $salary = $user->salary;


        return view('salary.update', compact('salary', 'user'));
    }


}
