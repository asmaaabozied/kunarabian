{!! view_render_event('bagisto.shop.checkout.onepage.shipping_methods.before') !!}

<v-shipping-methods
    :methods="shippingMethods"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <!-- Shipping Method Shimmer Effect -->
    <x-shop::shimmer.checkout.onepage.shipping-method />
</v-shipping-methods>

{!! view_render_event('bagisto.shop.checkout.onepage.shipping_methods.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-shipping-methods-template"
    >
        <div class="mb-6">
            <template v-if="! methods">
                <!-- Shipping Method Shimmer Effect -->
                <x-shop::shimmer.checkout.onepage.shipping-method />
            </template>

            <template v-else>
                <h3 class="kun-checkout-section-title mb-4">
                    @lang('shop::app.checkout.onepage.shipping.shipping-method')
                </h3>

                <div class="flex gap-4 flex-wrap">
                    <template v-for="method in methods">
                        {!! view_render_event('bagisto.shop.checkout.onepage.shipping_method.before') !!}

                        <div
                            v-for="rate in method.rates"
                            class="kun-checkout-card px-8 py-6"
                            :class="{ 'kun-checkout-card--selected': selectedMethod === rate.method }"
                            @click="store(rate.method)"
                        >
                            <input
                                type="radio"
                                name="shipping_method"
                                :id="rate.method"
                                :value="rate.method"
                                class="peer hidden"
                                v-model="selectedMethod"
                            >

                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="kun-checkout-card-title">
                                        @{{ rate.method_title }}
                                    </p>
                                    <p class="kun-checkout-card-desc mt-1">
                                        @{{ rate.method_description }}
                                    </p>
                                </div>
                                <p class="kun-checkout-card-price">
                                    @{{ rate.base_formatted_price }}
                                </p>
                            </div>
                        </div>

                        {!! view_render_event('bagisto.shop.checkout.onepage.shipping_method.after') !!}
                    </template>
                </div>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-shipping-methods', {
            template: '#v-shipping-methods-template',

            props: {
                methods: {
                    type: Object,
                    required: true,
                    default: () => null,
                },
            },

            emits: ['processing', 'processed'],

            data() {
                return {
                    selectedMethod: null,
                };
            },

            methods: {
                store(selectedMethod) {
                    this.selectedMethod = selectedMethod;

                    this.$emit('processing', 'payment');

                    this.$axios.post("{{ route('shop.checkout.onepage.shipping_methods.store') }}", {
                            shipping_method: selectedMethod,
                        })
                        .then(response => {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                this.$emit('processed', response.data.payment_methods);
                            }
                        })
                        .catch(error => {
                            this.$emit('processing', 'shipping');

                            if (error.response.data.redirect_url) {
                                window.location.href = error.response.data.redirect_url;
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
