<div class="bp-form-field bp-toolbar-toggle">
    <span class="bp-form-label">Status</span>

    @if(request()->query('showInactive') == 1)
        <button class="bp-button-secondary bp-toolbar-toggle-button"
                onclick='window.location = "/{{request()->path() . '?' . http_build_query(array_merge(request()->except(['d_from','d_to','dateSelect','showInActive']), ['showInactive' => 0]))}}" + processDates()'>
            Show Active
        </button>
    @else
        <button class="bp-button-secondary bp-toolbar-toggle-button"
                onclick='window.location = "/{{request()->path()  . '?' . http_build_query(array_merge(request()->except(['d_from','d_to','dateSelect','showInActive']), ['showInactive' => 1]))}}" + processDates()'>
            Show Inactive
        </button>
    @endif
</div>
