@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-form-template"
    >
        <div>
            <x-shop::form.control-group class="hidden">
                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.id'"
                    ::value="address.id"
                />
            </x-shop::form.control-group>

            <!-- Company Name -->
            <div class="kun-checkout-field">
                <label class="kun-checkout-label">
                    @lang('shop::app.checkout.onepage.address.company-name')
                </label>

                <x-shop::form.control-group.control
                    type="text"
                    class="kun-checkout-input"
                    ::name="controlName + '.company_name'"
                    ::value="address.company_name"
                    :placeholder="trans('shop::app.checkout.onepage.address.company-name')"
                />
            </div>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.company_name.after') !!}

            <!-- First Name + Last Name -->
            <div class="kun-checkout-row-2">
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.first-name')<span class="required-mark">*</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.first_name'"
                        ::value="address.first_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.first-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.first-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.first_name'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.first_name.after') !!}

                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.last-name')<span class="required-mark">*</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.last_name'"
                        ::value="address.last_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.last-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.last-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.last_name'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.last_name.after') !!}
            </div>

            <!-- Email -->
            <div class="kun-checkout-field">
                <label class="kun-checkout-label">
                    @lang('shop::app.checkout.onepage.address.email')<span class="required-mark">*</span>
                </label>

                <x-shop::form.control-group.control
                    type="email"
                    class="kun-checkout-input"
                    ::name="controlName + '.email'"
                    ::value="address.email"
                    rules="required|email"
                    :label="trans('shop::app.checkout.onepage.address.email')"
                    placeholder="address@example.com"
                />

                <x-shop::form.control-group.error ::name="controlName + '.email'" />
            </div>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.email.after') !!}

            <!-- Vat ID -->
            <template v-if="controlName=='billing'">
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.vat-id')
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.vat_id'"
                        ::value="address.vat_id"
                        :label="trans('shop::app.checkout.onepage.address.vat-id')"
                        :placeholder="trans('shop::app.checkout.onepage.address.vat-id')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.vat_id'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.vat_id.after') !!}
            </template>

            <!-- Street Address -->
            <div class="kun-checkout-field">
                <label class="kun-checkout-label">
                    @lang('shop::app.checkout.onepage.address.street-address')<span class="required-mark">*</span>
                </label>

                <x-shop::form.control-group.control
                    type="text"
                    class="kun-checkout-input"
                    ::name="controlName + '.address.[0]'"
                    ::value="address.address[0]"
                    rules="required|address"
                    :label="trans('shop::app.checkout.onepage.address.street-address')"
                    :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                />

                <x-shop::form.control-group.error
                    ::name="controlName + '.address.[0]'"
                />

                @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                        <x-shop::form.control-group.control
                            type="text"
                            class="kun-checkout-input mt-2"
                            ::name="controlName + '.address.[{{ $i }}]'"
                            rules="address"
                            :label="trans('shop::app.checkout.onepage.address.street-address')"
                            :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                        />

                        <x-shop::form.control-group.error
                            ::name="controlName + '.address.[{{ $i }}]'"
                        />
                    @endfor
                @endif
            </div>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.address.after') !!}

            <!-- Country + State + City (3-col) -->
            <div class="kun-checkout-row-3">
                <!-- Country -->
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.country'){{ core()->isCountryRequired() ? '' : '' }}<span class="required-mark">{{ core()->isCountryRequired() ? '*' : '' }}</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="select"
                        class="kun-checkout-select"
                        ::name="controlName + '.country'"
                        ::value="address.country"
                        v-model="selectedCountry"
                        rules="{{ core()->isCountryRequired() ? 'required' : '' }}"
                        :label="trans('shop::app.checkout.onepage.address.country')"
                        :placeholder="trans('shop::app.checkout.onepage.address.country')"
                    >
                        <option value="">
                            @lang('shop::app.checkout.onepage.address.select-country')
                        </option>

                        <option
                            v-for="country in countries"
                            :value="country.code"
                        >
                            @{{ country.name }}
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="controlName + '.country'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.country.after') !!}

                <!-- State -->
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.state')<span class="required-mark">{{ core()->isStateRequired() ? '*' : '' }}</span>
                    </label>

                    <template v-if="states">
                        <template v-if="haveStates">
                            <x-shop::form.control-group.control
                                type="select"
                                class="kun-checkout-select"
                                ::name="controlName + '.state'"
                                rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                                ::value="address.state"
                                :label="trans('shop::app.checkout.onepage.address.state')"
                                :placeholder="trans('shop::app.checkout.onepage.address.state')"
                            >
                                <option value="">
                                    @lang('shop::app.checkout.onepage.address.select-state')
                                </option>

                                <option
                                    v-for='(state, index) in states[selectedCountry]'
                                    :value="state.code"
                                >
                                    @{{ state.default_name }}
                                </option>
                            </x-shop::form.control-group.control>
                        </template>

                        <template v-else>
                            <x-shop::form.control-group.control
                                type="text"
                                class="kun-checkout-input"
                                ::name="controlName + '.state'"
                                ::value="address.state"
                                rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                                :label="trans('shop::app.checkout.onepage.address.state')"
                                :placeholder="trans('shop::app.checkout.onepage.address.state')"
                            />
                        </template>
                    </template>

                    <x-shop::form.control-group.error ::name="controlName + '.state'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.state.after') !!}

                <!-- City -->
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.city')<span class="required-mark">*</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.city'"
                        ::value="address.city"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.city')"
                        :placeholder="trans('shop::app.checkout.onepage.address.city')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.city'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.city.after') !!}
            </div>

            <!-- Zip/Postal Code + Phone Number (2-col) -->
            <div class="kun-checkout-row-2">
                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.postcode')<span class="required-mark">{{ core()->isPostCodeRequired() ? '*' : '' }}</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.postcode'"
                        ::value="address.postcode"
                        rules="{{ core()->isPostCodeRequired() ? 'required' : '' }}|postcode"
                        :label="trans('shop::app.checkout.onepage.address.postcode')"
                        :placeholder="trans('shop::app.checkout.onepage.address.postcode')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.postcode'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.postcode.after') !!}

                <div class="kun-checkout-field">
                    <label class="kun-checkout-label">
                        @lang('shop::app.checkout.onepage.address.telephone')<span class="required-mark">*</span>
                    </label>

                    <x-shop::form.control-group.control
                        type="text"
                        class="kun-checkout-input"
                        ::name="controlName + '.phone'"
                        ::value="address.phone"
                        rules="required|phone"
                        :label="trans('shop::app.checkout.onepage.address.telephone')"
                        :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.phone'" />
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.phone.after') !!}
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-address-form', {
            template: '#v-checkout-address-form-template',

            props: {
                controlName: {
                    type: String,
                    required: true,
                },

                address: {
                    type: Object,

                    default: () => ({
                        id: 0,
                        company_name: '',
                        first_name: '',
                        last_name: '',
                        email: '',
                        address: [],
                        country: '',
                        state: '',
                        city: '',
                        postcode: '',
                        phone: '',
                    }),
                },
            },

            data() {
                return {
                    selectedCountry: this.address.country,

                    countries: [],

                    states: null,
                }
            },

            computed: {
                haveStates() {
                    return !! this.states[this.selectedCountry]?.length;
                },
            },

            mounted() {
                this.getCountries();

                this.getStates();
            },

            methods: {
                getCountries() {
                    this.$axios.get("{{ route('shop.api.core.countries') }}")
                        .then(response => {
                            this.countries = response.data.data;
                        })
                        .catch(() => {});
                },

                getStates() {
                    this.$axios.get("{{ route('shop.api.core.states') }}")
                        .then(response => {
                            this.states = response.data.data;
                        })
                        .catch(() => {});
                },
            }
        });
    </script>
@endPushOnce
