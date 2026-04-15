<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateGodPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update-god';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure god users have all permissions enabled.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissionColumns = collect(Schema::getColumnListing('permissions'))
            ->reject(function ($column) {
                return in_array($column, ['id', 'aff_id']);
            })
            ->values();

        $godIds = DB::table('rep')
            ->join('privileges', 'privileges.rep_idrep', '=', 'rep.idrep')
            ->where('privileges.is_god', 1)
            ->pluck('rep.idrep');

        if ($godIds->isEmpty()) {
            $this->info('No god users found.');
            return 0;
        }

        foreach ($godIds as $repId) {
            $permissions = DB::table('permissions')->where('aff_id', $repId)->first();

            if (!$permissions) {
                $this->warn("No permissions record found for rep ID {$repId}.");
                continue;
            }

            $updates = [];
            foreach ($permissionColumns as $column) {
                if (empty($permissions->{$column}) || (int)$permissions->{$column} !== 1) {
                    $updates[$column] = 1;
                }
            }

            if (empty($updates)) {
                $this->info("Rep ID {$repId} already has all permissions enabled.");
                continue;
            }

            DB::table('permissions')->where('aff_id', $repId)->update($updates);
            $this->info("Updated permissions for rep ID {$repId}.");
        }

        return 0;
    }
}
