{!! view_render_event('bagisto.shop.checkout.onepage.payment_methods.before') !!}

<v-payment-methods
    :methods="paymentMethods"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <x-shop::shimmer.checkout.onepage.payment-method />
</v-payment-methods>

{!! view_render_event('bagisto.shop.checkout.onepage.payment_methods.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-payment-methods-template"
    >
        <div class="mb-6">
            <template v-if="! methods">
                <!-- Payment Method shimmer Effect -->
                <x-shop::shimmer.checkout.onepage.payment-method />
            </template>

            <template v-else>
                {!! view_render_event('bagisto.shop.checkout.onepage.payment_method.accordion.before') !!}

                <h3 class="kun-checkout-section-title mb-4">
                    @lang('shop::app.checkout.onepage.payment.payment-method')
                </h3>

                <div class="flex gap-4 flex-wrap">
                    <div
                        v-for="(payment, index) in methods"
                        class="kun-checkout-card px-5 flex items-center"
                        :class="{ 'kun-checkout-card--selected': selectedMethod === payment.method }"
                        @click="store(payment)"
                    >
                        {!! view_render_event('bagisto.shop.checkout.payment-method.before') !!}

                        <input
                            type="radio"
                            name="payment[method]"
                            :value="payment.payment"
                            :id="payment.method"
                            class="peer hidden"
                            v-model="selectedMethod"
                        >

                        <div class="flex flex-col gap-2 items-start">
                            {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.image.before') !!}

                            <img
                                class="max-h-6 max-w-[45px]"
                                :src="payment.image"
                                :alt="payment.method_title"
                                :title="payment.method_title"
                            />

                            {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.image.after') !!}

                            {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.title.before') !!}

                            <p class="kun-checkout-card-title">
                                @{{ payment.method_title }}
                            </p>

                            {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.title.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.checkout.payment-method.after') !!}
                    </div>
                </div>

                {!! view_render_event('bagisto.shop.checkout.onepage.payment_method.accordion.after') !!}
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-payment-methods', {
            template: '#v-payment-methods-template',

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
                    this.selectedMethod = selectedMethod.method;

                    this.$emit('processing', 'review');

                    this.$axios.post("{{ route('shop.checkout.onepage.payment_methods.store') }}", {
                            payment: selectedMethod
                        })
                        .then(response => {
                            this.$emit('processed', response.data.cart);

                            if (window.innerWidth <= 768) {
                                window.scrollTo({
                                    top: document.body.scrollHeight,
                                    behavior: 'smooth'
                                });
                            }
                        })
                        .catch(error => {
                            this.$emit('processing', 'payment');

                            if (error.response.data.redirect_url) {
                                window.location.href = error.response.data.redirect_url;
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
