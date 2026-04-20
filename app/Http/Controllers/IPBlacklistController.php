<?php

namespace App\Http\Controllers;

use App\Privilege;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LeadMax\TrackYourStats\System\IPBlackList;
use LeadMax\TrackYourStats\System\Session;
use PDO;

class IPBlacklistController extends Controller
{
    public function index()
    {
        $this->ensureGodAccess();

        $entries = collect(IPBlackList::selectIPs()->fetchAll(PDO::FETCH_OBJ))
            ->map(function ($entry) {
                return (object) [
                    'id' => (int) $entry->id,
                    'start' => long2ip((int) $entry->start),
                    'end' => long2ip((int) $entry->end),
                    'timestamp' => (int) $entry->timestamp,
                    'createdLabel' => Carbon::createFromTimestamp((int) $entry->timestamp)->toFormattedDateString(),
                ];
            })
            ->values();

        return view('tools.ip-blacklist.index', [
            'entries' => $entries,
            'latestCreatedLabel' => optional($entries->first())->createdLabel ?? '—',
        ]);
    }

    public function create()
    {
        $this->ensureGodAccess();

        return view('tools.ip-blacklist.form', [
            'mode' => 'create',
            'formAction' => '/ip-blacklist/create',
            'pageHeading' => 'Add IP range',
            'submitLabel' => 'Create range',
            'introCopy' => 'Create a blacklist range used to block known bad traffic before it gets deeper into the system.',
            'values' => [
                'start' => old('start', ''),
                'end' => old('end', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureGodAccess();

        [$start, $end] = $this->validateRange($request);

        IPBlackList::createNewBlacklist($start, $end);

        return redirect('/ip-blacklist')->with('message', 'IP blacklist range created successfully.');
    }

    public function edit($id)
    {
        $this->ensureGodAccess();

        $entry = $this->findEntryOrFail($id);

        return view('tools.ip-blacklist.form', [
            'mode' => 'edit',
            'formAction' => "/ip-blacklist/{$entry->id}/edit",
            'pageHeading' => 'Edit IP range',
            'submitLabel' => 'Update range',
            'introCopy' => 'Adjust the stored start and end bounds for this blacklist range without using the legacy edit screen.',
            'values' => [
                'start' => old('start', $entry->start),
                'end' => old('end', $entry->end),
            ],
            'entry' => $entry,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->ensureGodAccess();
        $this->findEntryOrFail($id);

        [$start, $end] = $this->validateRange($request);

        IPBlackList::updateBlackList($id, $start, $end);

        return redirect('/ip-blacklist')->with('message', 'IP blacklist range updated successfully.');
    }

    public function destroy($id)
    {
        $this->ensureGodAccess();
        $this->findEntryOrFail($id);

        IPBlackList::deleteBlackList($id);

        return redirect('/ip-blacklist')->with('message', 'IP blacklist range deleted successfully.');
    }

    private function ensureGodAccess(): void
    {
        abort_unless(Session::userType() === Privilege::ROLE_GOD, 403, 'Incorrect user type');
    }

    private function validateRange(Request $request): array
    {
        $validated = $request->validate([
            'start' => ['required', 'ip'],
            'end' => ['required', 'ip'],
        ]);

        $startLong = ip2long((string) $validated['start']);
        $endLong = ip2long((string) $validated['end']);

        if ($startLong === false || $endLong === false) {
            throw ValidationException::withMessages([
                'start' => 'Enter a valid IPv4 start range.',
                'end' => 'Enter a valid IPv4 end range.',
            ]);
        }

        if ($startLong > $endLong) {
            throw ValidationException::withMessages([
                'end' => 'The end range must be greater than or equal to the start range.',
            ]);
        }

        return [(string) $validated['start'], (string) $validated['end']];
    }

    private function findEntryOrFail($id): object
    {
        $entry = IPBlackList::selectOne($id)->fetch(PDO::FETCH_OBJ);

        abort_if(!$entry, 404);

        return (object) [
            'id' => (int) $entry->id,
            'start' => long2ip((int) $entry->start),
            'end' => long2ip((int) $entry->end),
            'timestamp' => (int) $entry->timestamp,
            'createdLabel' => Carbon::createFromTimestamp((int) $entry->timestamp)->toFormattedDateString(),
        ];
    }
}
