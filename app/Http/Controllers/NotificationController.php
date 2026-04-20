<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LeadMax\TrackYourStats\System\Mail;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = $this->notificationsQuery()->get();

        return view('notifications.index', [
            'notificationsList' => $notifications,
            'unreadCount' => $notifications->where('seen', 0)->count(),
            'readCount' => $notifications->where('seen', 1)->count(),
        ]);
    }

    public function show($id)
    {
        $notification = $this->findUserNotificationOrFail($id);

        return view('notifications.show', [
            'notificationItem' => $notification,
        ]);
    }

    public function markRead($id)
    {
        $this->findUserNotificationOrFail($id);

        DB::table('user_has_notification')
            ->where('notification_id', '=', $id)
            ->where('user_id', '=', Session::userID())
            ->update(['seen' => 1]);

        return redirect("/notifications/{$id}")->with('message', 'Notification marked as read.');
    }

    public function destroy($id)
    {
        $this->findUserNotificationOrFail($id);

        DB::table('user_has_notification')
            ->where('notification_id', '=', $id)
            ->where('user_id', '=', Session::userID())
            ->update(['deleted' => 1]);

        return redirect('/notifications')->with('message', 'Notification deleted.');
    }

    public function create()
    {
        $this->authorizeCreateNotifications();

        return view('notifications.create', $this->buildCreateViewData());
    }

    public function store(Request $request)
    {
        $this->authorizeCreateNotifications();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'userList' => 'required|array|min:1',
            'userList.*' => 'integer',
            'sendEmails' => 'nullable|boolean',
        ]);

        $allowedRecipientIds = $this->getSelectableRecipients()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requestedRecipientIds = collect($validated['userList'])->map(fn ($id) => (int) $id)->unique()->values();

        $invalidRecipientSelected = $requestedRecipientIds->first(fn ($id) => !in_array($id, $allowedRecipientIds, true));
        if ($invalidRecipientSelected !== null) {
            return back()->withErrors(['userList' => 'Choose valid recipients for this notification.'])->withInput();
        }

        $notificationId = DB::transaction(function () use ($validated, $requestedRecipientIds) {
            $notificationId = DB::table('notifications')->insertGetId([
                'title' => trim($validated['title']),
                'body' => trim($validated['body']),
                'timestamp' => date('U'),
                'author' => Session::userID(),
            ]);

            $rows = $requestedRecipientIds->map(fn ($userId) => [
                'notification_id' => $notificationId,
                'user_id' => $userId,
            ])->all();

            DB::table('user_has_notification')->insert($rows);

            return $notificationId;
        });

        if ($request->boolean('sendEmails')) {
            $this->sendNotificationEmails(
                $requestedRecipientIds->all(),
                trim($validated['title']),
                trim($validated['body'])
            );
        }

        return redirect('/notifications')->with('message', 'Notification sent successfully.');
    }

    private function notificationsQuery()
    {
        return DB::table('user_has_notification')
            ->join('notifications', 'notifications.id', '=', 'user_has_notification.notification_id')
            ->join('rep as author', 'author.idrep', '=', 'notifications.author')
            ->where('user_has_notification.user_id', '=', Session::userID())
            ->where('user_has_notification.deleted', '=', 0)
            ->orderByDesc('notifications.timestamp')
            ->select([
                'notifications.id',
                'notifications.title',
                'notifications.body',
                'notifications.timestamp',
                'user_has_notification.seen',
                'author.user_name as author_user_name',
            ]);
    }

    private function findUserNotificationOrFail($id)
    {
        $notification = $this->notificationsQuery()->where('notifications.id', '=', $id)->first();

        abort_if(!$notification, 404);

        return $notification;
    }

    private function authorizeCreateNotifications(): void
    {
        abort_unless(Session::permissions()->can(Permissions::CREATE_NOTIFICATIONS), 403);
    }

    private function buildCreateViewData(): array
    {
        $recipientGroups = [];

        if (Session::permissions()->can(Permissions::CREATE_ADMINS)) {
            $recipientGroups[] = [
                'label' => 'Admins',
                'type' => 'Admin',
                'users' => User::query()
                    ->withRole(Privilege::ROLE_ADMIN)
                    ->orderBy('rep.user_name')
                    ->get(['rep.idrep as id', 'rep.user_name as name']),
            ];
        }

        if (Session::permissions()->can(Permissions::CREATE_MANAGERS)) {
            $recipientGroups[] = [
                'label' => config('branding.account.plural'),
                'type' => config('branding.account.singular'),
                'users' => User::query()
                    ->withRole(Privilege::ROLE_MANAGER)
                    ->orderBy('rep.user_name')
                    ->get(['rep.idrep as id', 'rep.user_name as name']),
            ];
        }

        $recipientGroups[] = [
            'label' => config('branding.affiliate.plural'),
            'type' => config('branding.affiliate.singular'),
            'users' => User::query()
                ->withRole(Privilege::ROLE_AFFILIATE)
                ->myUsers()
                ->orderBy('rep.user_name')
                ->get(['rep.idrep as id', 'rep.user_name as name']),
        ];

        $recipientCount = collect($recipientGroups)
            ->flatMap(fn ($group) => $group['users']->pluck('id'))
            ->unique()
            ->count();

        return [
            'recipientGroups' => $recipientGroups,
            'recipientCount' => $recipientCount,
        ];
    }

    private function getSelectableRecipients()
    {
        return collect($this->buildCreateViewData()['recipientGroups'])
            ->flatMap(fn ($group) => $group['users']->map(fn ($user) => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'type' => $group['type'],
            ]))
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    private function sendNotificationEmails(array $recipientIds, string $title, string $body): void
    {
        $emails = DB::table('rep')
            ->whereIn('idrep', $recipientIds)
            ->pluck('email')
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        $author = Session::userData()->user_name;
        $host = request()->getHost();
        $htmlBody = "<html><h3>Notification from {$author} @ {$host}</h3><br/>" . nl2br(e($body)) . '</html>';

        foreach ($emails as $email) {
            (new Mail($email, $title, $htmlBody))->send();
        }
    }
}
