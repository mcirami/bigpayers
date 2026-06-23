<script type="text/javascript">
    function handleSelect(elm) {
        window.location = "/<?=\App\Support\RequestContext::path() . '?' . http_build_query(\App\Support\RequestContext::queryExcept(['role']))?>&role=" + elm.value;
    }
</script>

@php
    $accountLabelPlural = $accountLabelPlural ?? ($accountTypeLabelPlural ?? \App\Services\BrandingLabels::accounts());
    $affiliateLabel = $affiliateLabel ?? ($affiliateTypeLabelPlural ?? \App\Services\BrandingLabels::affiliates());
@endphp

<label class="bp-form-field">
    <span class="bp-form-label">Role</span>
    <select onchange="handleSelect(this);" class="selectBox bp-form-input" id="role" name="role">

        @if($sessionUserType == \App\Privilege::ROLE_GOD)
            <option @if(\App\Support\RequestContext::query('role',3) == 1) selected @endif value='1'>Admins
            </option>
        @endif


        @if($canCreateManagers && $sessionUserType !== \App\Privilege::ROLE_MANAGER)
            <option @if(\App\Support\RequestContext::query('role',3) == 2) selected @endif value='2'>{{ $accountLabelPlural }}</option>
        @endif
        <option @if(\App\Support\RequestContext::query('role',3 ) == 3) selected @endif value='3'>{{ $affiliateLabel }}</option>
    </select>
</label>
