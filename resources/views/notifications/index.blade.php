@extends('layouts.dashboard-shell')

@section('page-title', 'Notifications')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Inbox Workspace</p>
                    <h2 class="bp-section-title value_span9">Notification center</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review system messages, open the full detail view, and clear inbox items without dropping back into the legacy notification pages.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_NOTIFICATIONS))
                        <a href="/notifications/create" class="bp-button-primary">Create notification</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Inbox</p>
                <p class="bp-stat-value">{{ $notificationsList->count() }}</p>
                <p class="bp-stat-note">Active notifications currently available in your inbox.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Unread</p>
                <p class="bp-stat-value">{{ $unreadCount }}</p>
                <p class="bp-stat-note">Messages still waiting for acknowledgement.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Read</p>
                <p class="bp-stat-value">{{ $readCount }}</p>
                <p class="bp-stat-note">Messages you have already reviewed and kept in the inbox.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Inbox Table</p>
                    <h3 class="bp-section-title value_span9">Message list</h3>
                </div>
                <p class="bp-table-meta">Unread rows are highlighted so important system updates are easier to spot at a glance.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table class="table table-striped table_01 large_table" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">Title</th>
                        <th class="value_span9">Preview</th>
                        <th class="value_span9">Date</th>
                        <th class="value_span9">Author</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($notificationsList as $notification)
                        <tr class="{{ (int) $notification->seen === 0 ? 'bp-notification-row-unread' : '' }}">
                            <td>{{ $notification->title }}</td>
                            <td>{{ \Illuminate\Support\Str::limit(strip_tags($notification->body), 80) }}</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($notification->timestamp)->toFormattedDateString() }}</td>
                            <td>{{ $notification->author_user_name }}</td>
                            <td>
                                <span class="bp-status-pill {{ (int) $notification->seen === 1 ? 'bp-status-pill-active' : '' }}">
                                    {{ (int) $notification->seen === 1 ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td>
                                <div class="bp-table-actions">
                                    <a href="/notifications/{{ $notification->id }}" class="bp-action-link">View</a>
                                    @if ((int) $notification->seen === 0)
                                        <form method="post" action="/notifications/{{ $notification->id }}/mark-read">
                                            @csrf
                                            <button type="submit" class="bp-action-link">Mark read</button>
                                        </form>
                                    @endif
                                    <form method="post" action="/notifications/{{ $notification->id }}/delete" onsubmit="return confirm('Delete this notification from your inbox?');">
                                        @csrf
                                        <button type="submit" class="bp-action-link value_span11 value_span2">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="bp-table-empty">No notifications are available right now.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script>
        $('#mainTable').tablesorter({
            sortList: [[2, 1]],
            widgets: ['staticRow']
        });
    </script>
@endsection
