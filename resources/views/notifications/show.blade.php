@extends('layouts.dashboard-shell')

@section('page-title', 'Notification')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Inbox Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $notificationItem->title }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Sent by {{ $notificationItem->author_user_name }} on {{ \Carbon\Carbon::createFromTimestamp($notificationItem->timestamp)->toFormattedDateString() }}.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/notifications" class="bp-button-secondary">Back to inbox</a>
                    @if ((int) $notificationItem->seen === 0)
                        <form method="post" action="/notifications/{{ $notificationItem->id }}/mark-read">
                            @csrf
                            <button type="submit" class="bp-button-secondary">Mark as read</button>
                        </form>
                    @endif
                    <form method="post" action="/notifications/{{ $notificationItem->id }}/delete" onsubmit="return confirm('Delete this notification from your inbox?');">
                        @csrf
                        <button type="submit" class="bp-button-primary">Delete</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="bp-card value_span8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="bp-section-kicker">Message</p>
                    <h3 class="bp-section-title value_span9">Full notification</h3>
                </div>
                <span class="bp-status-pill {{ (int) $notificationItem->seen === 1 ? 'bp-status-pill-active' : '' }}">
                    {{ (int) $notificationItem->seen === 1 ? 'Read' : 'Unread' }}
                </span>
            </div>

            <div class="mt-6 bp-notification-body">
                {!! nl2br(e($notificationItem->body)) !!}
            </div>
        </section>
    </div>
@endsection
