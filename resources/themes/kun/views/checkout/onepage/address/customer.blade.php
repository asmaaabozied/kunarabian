{!! view_render_event('bagisto.shop.checkout.onepage.address.customer.before') !!}

<!-- Customer Address Vue Component -->
<v-checkout-address-customer
    :cart="cart"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <!-- Billing Address Shimmer -->
    <x-shop::shimmer.checkout.onepage.address />
</v-checkout-address-customer>

{!! view_render_event('bagisto.shop.checkout.onepage.address.customer.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-customer-template"
    >
        <template v-if="isLoading">
            <x-shop::shimmer.checkout.onepage.address />
        </template>

        <template v-else>
            <!-- Saved Addresses -->
            <template v-if="! activeAddressForm && customerSavedAddresses.billing.length">
                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, addAddressToCart)">
                        <!-- Saved Customer Addresses Cards -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div
                                class="relative cursor-pointer border border-[color:var(--kun-slate-300)] rounded-2xl p-0 overflow-hidden"
                                :style="selectedAddresses.billing_address_id == address.id ? 'border-color: var(--kun-slate-800); background: var(--kun-slate-100);' : ''"
                                v-for="address in customerSavedAddresses.billing"
                            >
                                <!-- Actions -->
                                <div class="absolute top-4 flex gap-2 ltr:right-4 rtl:left-4">
                                    <x-shop::form.control-group class="!mb-0 flex items-center gap-2.5">
                                        <x-shop::form.control-group.control
                                            type="radio"
                                            name="billing.id"
                                            ::id="`billing_address_id_${address.id}`"
                                            ::for="`billing_address_id_${address.id}`"
                                            ::value="address.id"
                                            v-model="selectedAddresses.billing_address_id"
                                            rules="required"
                                            label="{{ trans('shop::app.checkout.onepage.address.billing-address') }}"
                                        />
                                    </x-shop::form.control-group>

                                    <span
                                        class="icon-edit cursor-pointer text-xl"
                                        @click="
                                            selectedAddressForEdit = address;
                                            activeAddressForm = 'billing';
                                            saveAddress = address.address_type == 'customer'
                                        "
                                    ></span>
                                </div>

                                <!-- Details -->
                                <label
                                    class="block cursor-pointer p-5"
                                    :for="`billing_address_id_${address.id}`"
                                >
                                    <p class="font-[family-name:var(--kun-font-body)] text-base font-medium text-[color:var(--kun-slate-950)]">
                                        @{{ address.first_name + ' ' + address.last_name }}
                                        <template v-if="address.company_name">
                                            (@{{ address.company_name }})
                                        </template>
                                    </p>

                                    <p class="mt-2 font-[family-name:var(--kun-font-body)] text-sm text-[color:var(--kun-slate-500)] leading-[22px]">
                                        <template v-if="address.address">
                                            @{{ address.address.join(', ') }},
                                        </template>
                                        @{{ address.city }},
                                        @{{ address.state }}, @{{ address.country }},
                                        @{{ address.postcode }}
                                    </p>
                                </label>
                            </div>

                            <!-- New Address Card -->
                            <div
                                class="flex items-center justify-center cursor-pointer border border-dashed border-[color:var(--kun-slate-300)] rounded-2xl p-5 min-h-[100px]"
                                @click="activeAddressForm = 'billing'"
                                v-if="! cart.billing_address"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span class="icon-plus text-2xl border border-[color:var(--kun-slate-950)] rounded-full p-2"></span>
                                    <p class="font-[family-name:var(--kun-font-body)] text-base text-[color:var(--kun-slate-950)]">
                                        @lang('shop::app.checkout.onepage.address.add-new-address')
                                    </p>
                                </div>
                            </div>
                        </div>

                        <x-shop::form.control-group.error name="billing.id" />

                        <!-- Shipping Address Block -->
                        <template v-if="cart.have_stockable_items">
                            <!-- Use for Shipping Checkbox -->
                            <div class="kun-checkout-checkbox mt-4 mb-4">
                                <input
                                    type="checkbox"
                                    name="billing[use_for_shipping]"
                                    id="use_for_shipping"
                                    value="1"
                                    :checked="!! useBillingAddressForShipping"
                                    @change="useBillingAddressForShipping = ! useBillingAddressForShipping"
                                >
                                <label for="use_for_shipping">
                                    @lang('shop::app.checkout.onepage.address.same-as-billing')
                                </label>
                            </div>

                            <!-- Shipping Address Selection -->
                            <div
                                class="mt-6"
                                v-if="! useBillingAddressForShipping"
                            >
                                <h3 class="kun-checkout-section-title mb-4">
                                    @lang('shop::app.checkout.onepage.address.shipping-address')
                                </h3>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div
                                        class="relative cursor-pointer border border-[color:var(--kun-slate-300)] rounded-2xl p-0 overflow-hidden"
                                        :style="selectedAddresses.shipping_address_id == address.id ? 'border-color: var(--kun-slate-800); background: var(--kun-slate-100);' : ''"
                                        v-for="address in customerSavedAddresses.shipping"
                                    >
                                        <div class="absolute top-4 flex gap-2 ltr:right-4 rtl:left-4">
                                            <x-shop::form.control-group class="!mb-0 flex items-center gap-2.5">
                                                <x-shop::form.control-group.control
                                                    type="radio"
                                                    name="shipping.id"
                                                    ::id="`shipping_address_id_${address.id}`"
                                                    ::for="`shipping_address_id_${address.id}`"
                                                    ::value="address.id"
                                                    v-model="selectedAddresses.shipping_address_id"
                                                    rules="required"
                                                    label="{{ trans('shop::app.checkout.onepage.address.shipping-address') }}"
                                                />
                                            </x-shop::form.control-group>

                                            <span
                                                class="icon-edit cursor-pointer text-xl"
                                                @click="
                                                    selectedAddressForEdit = address;
                                                    activeAddressForm = 'shipping';
                                                    saveAddress = address.address_type == 'customer'
                                                "
                                            ></span>
                                        </div>

                                        <label
                                            class="block cursor-pointer p-5"
                                            :for="`shipping_address_id_${address.id}`"
                                        >
                                            <p class="font-[family-name:var(--kun-font-body)] text-base font-medium text-[color:var(--kun-slate-950)]">
                                                @{{ address.first_name + ' ' + address.last_name }}
                                                <template v-if="address.company_name">
                                                    (@{{ address.company_name }})
                                                </template>
                                            </p>

                                            <p class="mt-2 font-[family-name:var(--kun-font-body)] text-sm text-[color:var(--kun-slate-500)] leading-[22px]">
                                                <template v-if="address.address">
                                                    @{{ address.address.join(', ') }},
                                                </template>
                                                @{{ address.city }},
                                                @{{ address.state }}, @{{ address.country }},
                                                @{{ address.postcode }}
                                            </p>
                                        </label>
                                    </div>

                                    <!-- New Shipping Address Card -->
                                    <div
                                        class="flex items-center justify-center cursor-pointer border border-dashed border-[color:var(--kun-slate-300)] rounded-2xl p-5 min-h-[100px]"
                                        @click="selectedAddressForEdit = null; activeAddressForm = 'shipping'"
                                        v-if="! cart.shipping_address"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <span class="icon-plus text-2xl border border-[color:var(--kun-slate-950)] rounded-full p-2"></span>
                                            <p class="font-[family-name:var(--kun-font-body)] text-base text-[color:var(--kun-slate-950)]">
                                                @lang('shop::app.checkout.onepage.address.add-new-address')
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <x-shop::form.control-group.error name="shipping.id" />
                            </div>
                        </template>

                        <!-- Proceed Button -->
                        <div class="flex justify-end mt-4">
                            <button
                                type="submit"
                                class="kun-checkout-btn w-auto py-3.5 px-11"
                                :disabled="isStoring"
                            >
                                <template v-if="isStoring">
                                    <svg class="mr-2" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.416" stroke-dashoffset="10" stroke-linecap="round">
                                            <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" from="0 12 12" to="360 12 12"/>
                                        </circle>
                                    </svg>
                                </template>
                                @lang('shop::app.checkout.onepage.address.proceed')
                            </button>
                        </div>
                    </form>
                </x-shop::form>
            </template>

            <!-- Create/Edit Address Form -->
            <template v-else>
                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, updateOrCreateAddress)">
                        <!-- Address Header with Back link -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="kun-checkout-section-title">
                                <template v-if="activeAddressForm == 'billing'">
                                    @lang('shop::app.checkout.onepage.address.billing-address')
                                </template>
                                <template v-else>
                                    @lang('shop::app.checkout.onepage.address.shipping-address')
                                </template>
                            </h3>

                            <span
                                class="cursor-pointer font-[family-name:var(--kun-font-body)] text-sm text-[color:var(--kun-slate-500)]"
                                v-show="customerSavedAddresses.billing.length && ['billing', 'shipping'].includes(activeAddressForm)"
                                @click="selectedAddressForEdit = null; activeAddressForm = null"
                            >
                                <span class="icon-arrow-left text-xl"></span>
                                @lang('shop::app.checkout.onepage.address.back')
                            </span>
                        </div>

                        <v-checkout-address-form
                            :control-name="activeAddressForm"
                            :address="selectedAddressForEdit || undefined"
                        ></v-checkout-address-form>

                        <!-- Save Address Checkbox -->
                        <div class="kun-checkout-checkbox mb-4">
                            <input
                                type="checkbox"
                                :name="activeAddressForm + '[save_address]'"
                                id="save_address"
                                value="1"
                                v-model="saveAddress"
                                @change="saveAddress = ! saveAddress"
                            >
                            <label for="save_address">
                                @lang('shop::app.checkout.onepage.address.save-address')
                            </label>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="kun-checkout-btn w-auto py-3.5 px-11"
                                :disabled="isStoring"
                            >
                                <template v-if="isStoring">
                                    <svg class="mr-2" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.416" stroke-dashoffset="10" stroke-linecap="round">
                                            <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" from="0 12 12" to="360 12 12"/>
                                        </circle>
                                    </svg>
                                </template>
                                @lang('shop::app.checkout.onepage.address.save')
                            </button>
                        </div>
                    </form>
                </x-shop::form>
            </template>
        </template>
    </script>

    <script type="module">
        app.component('v-checkout-address-customer', {
            template: '#v-checkout-address-customer-template',

            props: ['cart'],

            emits: ['processing', 'processed'],

            data() {
                return {
                    customerSavedAddresses: {
                        'billing': [],
                        'shipping': [],
                    },

                    useBillingAddressForShipping: true,

                    activeAddressForm: null,

                    selectedAddressForEdit: null,

                    saveAddress: false,

                    selectedAddresses: {
                        billing_address_id: null,
                        shipping_address_id: null,
                    },

                    isLoading: true,

                    isStoring: false,
                }
            },

            created() {
                if (this.cart.billing_address) {
                    this.useBillingAddressForShipping = this.cart.billing_address.use_for_shipping;
                }
            },

            mounted() {
                this.getCustomerSavedAddresses();
            },

            methods: {
                getCustomerSavedAddresses() {
                    this.$axios.get('{{ route('shop.api.customers.account.addresses.index') }}')
                        .then(response => {
                            this.initializeAddresses('billing', structuredClone(response.data.data));
                            this.initializeAddresses('shipping', structuredClone(response.data.data));

                            if (! this.customerSavedAddresses.billing.length) {
                                this.activeAddressForm = 'billing';
                            }

                            this.isLoading = false;
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                },

                initializeAddresses(type, addresses) {
                    this.customerSavedAddresses[type] = addresses;

                    let cartAddress = this.cart[type + '_address'];

                    if (! cartAddress) {
                        addresses.forEach(address => {
                            if (address.default_address) {
                                this.selectedAddresses[type + '_address_id'] = address.id;
                            }
                        });

                        return addresses;
                    }

                    if (cartAddress.parent_address_id) {
                        addresses.forEach(address => {
                            if (address.id == cartAddress.parent_address_id) {
                                this.selectedAddresses[type + '_address_id'] = address.id;
                            }
                        });
                    } else {
                        this.selectedAddresses[type + '_address_id'] = cartAddress.id;
                        addresses.unshift(cartAddress);
                    }

                    return addresses;
                },

                updateOrCreateAddress(params, { setErrors }) {
                    this.$emit('processing', 'address');

                    params = params[this.activeAddressForm];

                    let address = this.customerSavedAddresses[this.activeAddressForm].find(address => {
                        return address.id == params.id;
                    });

                    if (! address) {
                        if (params.save_address) {
                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => {
                                    this.addAddressToList(response.data.data);
                                })
                                .catch((error) => {});
                        } else {
                            this.addAddressToList(params);
                        }

                        return;
                    }

                    if (params.save_address) {
                        if (address.address_type == 'customer') {
                            this.updateCustomerAddress(params.id, params, { setErrors })
                                .then((response) => {
                                    this.updateAddressInList(response.data.data);
                                })
                                .catch((error) => {});
                        } else {
                            this.removeAddressFromList(params);

                            this.createCustomerAddress(params, { setErrors })
                                .then((response) => {
                                    this.addAddressToList(response.data.data);
                                })
                                .catch((error) => {});
                        }
                    } else {
                        this.updateAddressInList(params);
                    }
                },

                addAddressToList(address) {
                    this.cart[this.activeAddressForm + '_address'] = address;

                    this.customerSavedAddresses[this.activeAddressForm].unshift(address);

                    this.selectedAddresses[this.activeAddressForm + '_address_id'] = address.id;

                    this.activeAddressForm = null;
                },

                updateAddressInList(params) {
                    this.customerSavedAddresses[this.activeAddressForm].forEach((address, index) => {
                        if (address.id == params.id) {
                            params = {
                                ...address,
                                ...params,
                            };

                            this.cart[this.activeAddressForm + '_address'] = params;

                            this.customerSavedAddresses[this.activeAddressForm][index] = params;

                            this.selectedAddresses[this.activeAddressForm + '_address_id'] = params.id;

                            this.activeAddressForm = null;
                        }
                    });
                },

                removeAddressFromList(params) {
                    this.customerSavedAddresses[this.activeAddressForm] = this.customerSavedAddresses[this.activeAddressForm].filter(address => address.id != params.id);
                },

                createCustomerAddress(params, { setErrors }) {
                    this.isStoring = true;

                    return this.$axios.post('{{ route('shop.api.customers.account.addresses.store') }}', params)
                        .then((response) => {
                            this.isStoring = false;
                            return response;
                        })
                        .catch(error => {
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                let errors = {};

                                Object.keys(error.response.data.errors).forEach(key => {
                                    errors[this.activeAddressForm + '.' + key] = error.response.data.errors[key];
                                });

                                setErrors(errors);
                            }

                            return Promise.reject(error);
                        });
                },

                updateCustomerAddress(id, params, { setErrors }) {
                    this.isStoring = true;

                    return this.$axios.put('{{ route('shop.api.customers.account.addresses.update') }}/' + id, params)
                        .then((response) => {
                            this.isStoring = false;
                            return response;
                        })
                        .catch(error => {
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                let errors = {};

                                Object.keys(error.response.data.errors).forEach(key => {
                                    errors[this.activeAddressForm + '.' + key] = error.response.data.errors[key];
                                });

                                setErrors(errors);
                            }

                            return Promise.reject(error);
                        });
                },

                addAddressToCart(params, { setErrors }) {
                    let payload = {
                        billing: {
                            ...this.getSelectedAddress('billing', params.billing.id),
                            use_for_shipping: this.useBillingAddressForShipping
                        },
                    };

                    if (params.shipping !== undefined) {
                        payload.shipping = this.getSelectedAddress('shipping', params.shipping.id);
                    }

                    this.isStoring = true;

                    this.moveToNextStep();

                    this.$axios.post('{{ route('shop.checkout.onepage.addresses.store') }}', payload)
                        .then((response) => {
                            this.isStoring = false;

                            if (response.data.data.redirect_url) {
                                window.location.href = response.data.data.redirect_url;
                            } else {
                                if (this.cart.have_stockable_items) {
                                    this.$emit('processed', response.data.data.shippingMethods);
                                } else {
                                    this.$emit('processed', response.data.data.payment_methods);
                                }
                            }
                        })
                        .catch(error => {
                            this.isStoring = false;

                            this.$emit('processing', 'address');

                            if (error.response.status == 422) {
                                const billingRegex = /^billing\./;

                                if (Object.keys(error.response.data.errors).some(key => billingRegex.test(key))) {
                                    setErrors({
                                        'billing.id': error.response.data.message
                                    });
                                } else {
                                    setErrors({
                                        'shipping.id': error.response.data.message
                                    });
                                }
                            }
                        });
                },

                getSelectedAddress(type, id) {
                    let address = Object.assign({}, this.customerSavedAddresses[type].find(address => address.id == id));

                    if (id == 0) {
                        address.id = null;
                    }

                    return {
                        ...address,
                        default_address: 0,
                    };
                },

                moveToNextStep() {
                    if (this.cart.have_stockable_items) {
                        this.$emit('processing', 'shipping');
                    } else {
                        this.$emit('processing', 'payment');
                    }
                },
            }
        });
    </script>
@endPushOnce
