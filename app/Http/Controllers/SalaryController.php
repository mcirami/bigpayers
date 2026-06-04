<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Salary;
use App\User;
use App\Support\CurrentUserSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\User\Salary as LegacySalary;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $legacySalaries = new LegacySalary();

        if ($request->filled('id') && $request->filled('payout')) {
            $legacySalaries->payAffiliate((int) $request->query('id'), $request->query('payout'), (string) $request->query('reason', ''));

            return redirect('/salaries')->with('message', 'Salary payout recorded.');
        }

        $affiliates = collect($legacySalaries->weekReport()->fetchAll(\PDO::FETCH_ASSOC))
            ->filter(fn ($affiliate) => (int) ($affiliate['status'] ?? 0) === 1)
            ->values();
        $paidCount = $affiliates->filter(fn ($affiliate) => $affiliate['payout'] !== null)->count();

        return view('salary.index', [
            'affiliates' => $affiliates,
            'paidCount' => $paidCount,
            'unpaidCount' => $affiliates->count() - $paidCount,
            'canEditSalaries' => CurrentUserSession::can('edit_salaries'),
            'canPaySalaries' => CurrentUserSession::can('pay_salaries'),
        ]);
    }

    public function pay(Request $request)
    {
        $legacySalaries = new LegacySalary();
        $rows = collect($request->input('salaries', []));

        if ($request->filled('pay_user')) {
            $userId = (int) $request->input('pay_user');
            $row = $rows->get($userId, []);

            if (!empty($row['payout'])) {
                $legacySalaries->payAffiliate($userId, $row['payout'], (string) ($row['reason'] ?? ''));
            }

            return redirect('/salaries')->with('message', 'Salary payout recorded.');
        }

        $payRows = $rows
            ->filter(fn ($row) => is_array($row) && !empty($row['salary_id']) && isset($row['payout']) && $row['payout'] !== '')
            ->map(fn ($row) => [
                'salary_id' => (int) $row['salary_id'],
                'payout' => (double) $row['payout'],
                'reason' => (string) ($row['reason'] ?? ''),
            ])
            ->all();

        if (!empty($payRows)) {
            $legacySalaries->payAllAffiliates($payRows);
        }

        return redirect('/salaries')->with('message', 'Salary payouts recorded.');
    }

    public function manage()
    {
        $legacySalaries = new LegacySalary();
        $legacySalaries->fetchAffiliateSalaries();

        return view('salary.manage', [
            'affiliates' => collect($legacySalaries->affiliateList),
        ]);
    }


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




        return redirect()->route('salary.show.update', $id);
    }

    public function showCreate($id)
    {
        $user = User::withRole(Privilege::ROLE_AFFILIATE)->myUsers()->findOrFail($id);


        return view('salary.create', compact('user'));
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

        if (!$salary) {
            return redirect()->route('salary.show', $id);
        }


        return view('salary.update', compact('salary', 'user'));
    }


}
