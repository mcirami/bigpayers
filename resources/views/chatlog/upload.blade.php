@extends('layouts.dashboard-shell')

@section('page-title', 'Log Sale')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Chat Log</p>
                    <h2 class="bp-section-title value_span9">Log sale for {{ $offer->offer_name }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Attach proof images to the pending conversion before it is recorded as a sale log.
                    </p>
                </div>

                <a href="/report/chat-log" class="bp-button-secondary">Chat reports</a>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Upload</p>
                        <h3 class="bp-section-title value_span9">Proof images</h3>
                    </div>

                    <button type="button" class="bp-button-secondary" onclick="addImageInput()">Add image</button>
                </div>

                <form action="/chat-log/upload" method="post" id="form" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="pendingConversionId" value="{{ $pendingConversion->id }}">

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="saleTimestamp">Sale Timestamp</label>
                        <input id="saleTimestamp" class="bp-form-input" type="text" value="{{ $pendingConversion->timestamp }}" disabled>
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label">Images</label>
                        <div id="imageContainer" class="space-y-3">
                            <input class="bp-form-input" type="file" name="images[]" accept="image/*">
                        </div>
                        <p class="bp-form-note">Upload screenshots or image proof for this pending conversion.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" name="button" value="Log Sale" class="bp-button-primary">Log sale</button>
                    </div>
                </form>
            </section>

            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Pending Conversion</p>
                    <h3 class="bp-section-title value_span9">Sale details</h3>
                </div>

                <div class="mt-6">
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">Offer</span>
                        <span class="bp-detail-value">{{ $offer->offer_name }}</span>
                    </div>
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">Pending ID</span>
                        <span class="bp-detail-value">{{ $pendingConversion->id }}</span>
                    </div>
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">Timestamp</span>
                        <span class="bp-detail-value">{{ $pendingConversion->timestamp }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        let counter = 1;

        function addImageInput() {
            if (counter >= 15) {
                return;
            }

            counter++;

            const container = document.getElementById('imageContainer');
            const wrapper = document.createElement('div');
            wrapper.id = 'img_' + counter;
            wrapper.className = 'flex flex-col gap-3 sm:flex-row sm:items-center';
            wrapper.innerHTML = '<input class="bp-form-input" type="file" name="images[]" accept="image/*"><button type="button" class="bp-button-secondary" onclick="removeImageInput(' + counter + ')">Remove</button>';
            container.appendChild(wrapper);
        }

        function removeImageInput(num) {
            const field = document.getElementById('img_' + num);

            if (field) {
                field.remove();
            }
        }
    </script>
@endsection
