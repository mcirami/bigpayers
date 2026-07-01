@php
    $showManageOffers = $canManageOffers ?? false;
    $showManageSubIds = $canManageSubIds ?? false;
    $showLoginAsUser = $canLoginAsUser ?? false;
@endphp

<div class="{{ ($currentWorkspace ?? '') === 'offers' ? 'bp-offer-action-row' : 'flex flex-wrap items-center gap-3' }}">
    <a
        href="{{ ($currentWorkspace ?? '') === 'offers' && isset($managedUser) ? "/user/{$managedUser->idrep}/edit" : '/user/manage' }}"
        class="bp-button-secondary {{ ($currentWorkspace ?? '') === 'offers' ? 'bp-offer-action-button' : '' }}"
    >
        {{ ($currentWorkspace ?? '') === 'offers' ? 'Back to user' : 'Back to users' }}
    </a>

    @if($showManageOffers && isset($managedUser))
        <a
            href="/user/offers/{{ $managedUser->idrep }}"
            class="{{ ($currentWorkspace ?? '') === 'offers' ? 'bp-button-primary bp-offer-action-button' : 'bp-button-secondary' }}"
        >
            Manage offers
        </a>
    @endif

    @if($showManageSubIds && isset($managedUser))
        <a href="/user/{{ $managedUser->idrep }}/edit#sub-id-tools" class="bp-button-secondary {{ ($currentWorkspace ?? '') === 'offers' ? 'bp-offer-action-button' : '' }}">Manage sub IDs</a>
    @endif

    @if($showLoginAsUser && isset($managedUser))
        <a href="/login/{{ $managedUser->idrep }}" target="_blank" rel="noopener" class="bp-button-primary {{ ($currentWorkspace ?? '') === 'offers' ? 'bp-offer-action-button' : '' }}">Login as user</a>
    @endif
</div>
