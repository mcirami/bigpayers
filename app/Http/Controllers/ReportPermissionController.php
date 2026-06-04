<?php

namespace App\Http\Controllers;

use App\Support\CurrentUserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportPermissionController extends Controller
{
    public function index()
    {
        return view('admin.report-permissions', $this->buildViewData());
    }

    public function update(Request $request)
    {
        $permissionColumns = $this->permissionColumns();
        $updates = $request->input('permissions', []);

        foreach ($updates as $userId => $permissions) {
            if (!is_numeric($userId) || !is_array($permissions)) {
                continue;
            }

            $payload = [];
            foreach ($permissionColumns as $column) {
                $payload[$column] = !empty($permissions[$column]) ? 1 : 0;
            }

            DB::table('report_permissions')->updateOrInsert(
                ['user_id' => (int) $userId],
                $payload
            );
        }

        return redirect('/admin/report-permissions')->with('message', 'Report permissions updated successfully.');
    }

    private function buildViewData(): array
    {
        $permissionColumns = $this->permissionColumns();
        $bounds = CurrentUserSession::data();

        $affiliates = DB::table('rep')
            ->join('privileges', function ($join) {
                $join->on('privileges.rep_idrep', '=', 'rep.idrep')
                    ->where('privileges.is_rep', '=', 1);
            })
            ->leftJoin('report_permissions', 'report_permissions.user_id', '=', 'rep.idrep')
            ->where('rep.lft', '>', $bounds->lft)
            ->where('rep.rgt', '<', $bounds->rgt)
            ->orderBy('rep.user_name')
            ->get(array_merge([
                'rep.idrep as user_id',
                'rep.user_name',
            ], array_map(fn ($column) => "report_permissions.{$column}", $permissionColumns)));

        return [
            'pageTitle' => 'Report Permissions',
            'permissionColumns' => $permissionColumns,
            'affiliates' => $affiliates,
        ];
    }

    private function permissionColumns(): array
    {
        return array_values(array_diff(
            Schema::getColumnListing('report_permissions'),
            ['user_id']
        ));
    }
}
