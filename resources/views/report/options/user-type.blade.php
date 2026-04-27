<script type="text/javascript">
    function handleSelect(elm) {
        window.location = "/<?=request()->path() . '?' . http_build_query(request()->except(['role']))?>&role=" + elm.value;
    }
</script>

@php
    $accountLabelPlural = $accountLabelPlural ?? ($accountTypeLabelPlural ?? config('branding.account.plural'));
    $affiliateLabel = $affiliateLabel ?? ($affiliateTypeLabelPlural ?? config('branding.affiliate.plural'));
@endphp

<label class="bp-form-field">
    <span class="bp-form-label">Role</span>
    <select onchange="handleSelect(this);" class="selectBox bp-form-input" id="role" name="role">


        @if(\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD)
            <option @if(request('role',3) == 1) selected @endif value='1'>Admins
            </option>
        @endif


        @if(\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers") && \LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_MANAGER)
            <option @if(request('role',3) == 2) selected @endif value='2'>{{ $accountLabelPlural }}</option>
        @endif
        <option @if(request('role',3 ) == 3) selected @endif value='3'>{{ $affiliateLabel }}</option>
    </select>
</label>
