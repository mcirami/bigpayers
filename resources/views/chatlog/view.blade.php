@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'View Sale Log')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Chat Log</p>
                    <h2 class="bp-section-title value_span9">Sale log #{{ $saleLogId }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review uploaded proof images and attach additional files for this sale log.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/report/sale-log" class="bp-button-secondary">Back to sale log</a>
                    <a href="/report/chat-log" class="bp-button-primary">Chat reports</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Sale Log ID</p>
                <p class="bp-stat-value">{{ $saleLogId }}</p>
                <p class="bp-stat-note">Identifier attached to the logged conversion.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Images</p>
                <p class="bp-stat-value">{{ count($images) }}</p>
                <p class="bp-stat-note">Stored proof files currently attached to this log.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Upload</p>
                    <h3 class="bp-section-title value_span9">Add images</h3>
                </div>
                <button type="button" class="bp-button-secondary" onclick="addSaleLogImageInput()">Add image</button>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="/chat-log/view/{{ $saleLogId }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <div id="imageInputs" class="space-y-3">
                    <input class="bp-form-input" type="file" name="images[]" accept="image/*" required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bp-button-primary">Upload images</button>
                </div>
            </form>
        </section>

        <section class="bp-card value_span8">
            <div>
                <p class="bp-section-kicker">Proof Images</p>
                <h3 class="bp-section-title value_span9">Attached files</h3>
            </div>

            @if(count($images) > 0)
                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($images as $fileName)
                        @php
                            $imageUrl = "/sale_log/{$subDomain}/{$saleLogId}/{$fileName}";
                        @endphp
                        <article class="bp-link-card">
                            <a href="{{ $imageUrl }}" target="_blank" rel="noopener">
                                <img src="{{ $imageUrl }}" alt="Sale log image {{ $loop->iteration }}" class="h-40 w-full rounded-md object-cover">
                            </a>
                            <form method="post" action="/chat-log/view/{{ $saleLogId }}/delete" class="mt-3">
                                @csrf
                                <input type="hidden" name="fileName" value="{{ $fileName }}">
                                <button type="submit" class="bp-button-secondary">Delete</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-6 bp-inline-note">
                    <strong>No images attached</strong>
                    <span>Upload one or more proof images to complete this sale log.</span>
                </div>
            @endif
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        var saleLogImageInputCount = 1;

        function addSaleLogImageInput() {
            saleLogImageInputCount++;

            if (saleLogImageInputCount > 15) {
                return;
            }

            $("#imageInputs").append(
                "<input class=\"bp-form-input\" type=\"file\" name=\"images[]\" accept=\"image/*\">"
            );
        }
    </script>
@endsection
