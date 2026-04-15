@extends("layouts.master")
@section('content')

    <!--right_panel-->
    <div class="right_panel" id="root">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Add Sale</span></div>
            <div class="white_box value_span8">

                <form action="/sales/add" method="post" id="form" enctype="multipart/form-data">
                    {{csrf_field()}}


                    <div class="left_con01">
                        <p>
                            <label class="value_span9">Affiliate</label>
                            <select name="affiliate" v-model="selectedAffiliate" @change="handleAffiliateChange()"
                                    :disabled="affiliates.length === 0">
                                <option v-for="affiliate in affiliatesSorted" :value="affiliate.id"
                                        v-text="affiliate.name + ' - ' + affiliate.id">
                                </option>
                            </select>
                            <input type="text" v-model="affiliateSearchFilter" placeholder="Search affiliates..."
                                   style="margin-top:10px;">
                        </p>


                        <p>
                            <label class="value_span9">Date</label>
                            <input type="text" name="date" id="date" value="<?= date("Y-m-d H:i:s"); ?>">
                            <span class="small_txt value_span10">timestamps stored in utc</span>
                        </p>


                        <span class="btn_yellow"> <input type="submit" name="button"
                                                         class="value_span6-2 value_span2 value_span1-2"
                                                         value="Create Sale"/></span>

                    </div>

                    <div class="right_con01">
                        <p>
                            <label class="value_span9">Offer</label>
                            <select name="offer" id="offerSelect" :disabled="offers.length === 0">
                                <option v-for="offer in offersSorted" :value="offer.id"
                                        v-text="offer.name + ' - ' + offer.id">
                                </option>
                            </select>
                            <input type="text" v-model="offerSearchFilter" placeholder="Search offers..."
                                   style="margin-top:10px;">
                        </p>

                        <p>
                            <label class="value_span9">
                                <input type="checkbox" class="fixCheckBox" id="customPayoutCheckBox"
                                       v-model="customPayoutEnabled">Custom
                                Payout</label>
                            <input :disabled="!customPayoutEnabled"
                                   type="number" name="customPayout" id="customPayout"
                                   step="0.10"
                                   value="0.00">
                        </p>
                    </div>
                </form>
            </div>
        </div>


        @endsection


        @section('footer')
            <script type="text/javascript">

                $(document).ready(function () {
                    $('#date').datetimepicker({dateFormat: 'yy-mm-dd', timeFormat: 'hh:mm:ss'});
                });

            </script>

            <script>
                new Vue({
                    el: '#root',

                    data: {
                        selectedAffiliate: 0,
                        affiliates: [],
                        offers: [],
                        affiliateSearchFilter: '',
                        offerSearchFilter: '',

                        customPayoutEnabled: false,

                    },

                    mounted() {
                        axios.get('/sales/affiliates').then(result => {
                            this.affiliates = result.data;
                        });
                    },

                    computed: {

                        affiliatesSorted() {
                            return this.affiliates.filter(item => {
                                return item.name.toLowerCase().indexOf(this.affiliateSearchFilter.toLowerCase()) !== -1 || item.id.toString().indexOf(this.affiliateSearchFilter) !== -1;
                            });
                        },

                        offersSorted() {
                            return this.offers.filter(item => {
                                return item.name.toLowerCase().indexOf(this.offerSearchFilter.toLowerCase()) !== -1 || item.id.toString().indexOf(this.offerSearchFilter) !== -1;
                            });
                        },

                    },

                    methods: {
                        handleAffiliateChange() {
                            this.offers = [];
                            axios.get('/sales/affiliate-offers/' + this.selectedAffiliate).then(result => {
                                this.offers = result.data;
                            });
                        },
                    },

                });
            </script>
@endsection
