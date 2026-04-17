@php
    $showManageOffers = ($canManageOffers ?? false)
        || (
            isset($managedUser)
            && \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_AFFILIATES)
            && $managedUser->getRole() === \App\Privilege::ROLE_AFFILIATE
        );

    $showManageSubIds = ($canManageSubIds ?? false)
        || (
            isset($managedUser)
            && \LeadMax\TrackYourStats\System\Session::userType() === \App\Privilege::ROLE_GOD
            && $managedUser->getRole() === \App\Privilege::ROLE_AFFILIATE
        );

    $showLoginAsUser = ($canLoginAsUser ?? false)
        || (
            isset($managedUser)
            && \LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_AFFILIATE
            && $managedUser->idrep !== \LeadMax\TrackYourStats\System\Session::userID()
        );
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
        <a href="#" class="bp-button-primary {{ ($currentWorkspace ?? '') === 'offers' ? 'bp-offer-action-button' : '' }}" onclick="adminLogin({{ $managedUser->idrep }}); return false;">Login as user</a>
    @endif
</div>
