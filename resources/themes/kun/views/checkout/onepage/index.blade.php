<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.checkout.onepage.index.checkout')"/>
    <meta name="keywords" content="@lang('shop::app.checkout.onepage.index.checkout')"/>
@endPush

<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.checkout.onepage.index.checkout')
    </x-slot>

    <!-- Page Content -->
    <div class="kun-section bg-[var(--kun-slate-50)] min-h-[60vh]">

        {!! view_render_event('bagisto.shop.checkout.onepage.breadcrumbs.before') !!}

        <!-- Breadcrumb -->
        <div class="kun-checkout-breadcrumb">
            <a href="{{ route('shop.home.index') }}">
                @lang('kun-theme::app.checkout.breadcrumb.homepage')
            </a>
            <svg class="separator" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3.5L10.5 8L6 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <a href="{{ route('shop.checkout.cart.index') }}">
                @lang('kun-theme::app.checkout.breadcrumb.shopping-cart')
            </a>
            <svg class="separator" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3.5L10.5 8L6 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="current">
                @lang('kun-theme::app.checkout.breadcrumb.check-out')
            </span>
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.breadcrumbs.after') !!}

        @guest('customer')
            @include('shop::checkout.login')
        @endguest

        <!-- Checkout Vue Component -->
        <v-checkout>
            <!-- Shimmer Effect -->
            <x-shop::shimmer.checkout.onepage />
        </v-checkout>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-checkout-template"
        >
            <template v-if="! cart">
                <!-- Shimmer Effect -->
                <x-shop::shimmer.checkout.onepage />
            </template>

            <template v-else>
                <!-- Step Progress Bar -->
                <div class="kun-stepper max-sm:hidden">
                    <div class="kun-stepper__step">
                        <div class="kun-stepper__dot"
                             :class="{
                                 'kun-stepper__dot--active': currentStep === 'address',
                                 'kun-stepper__dot--completed': ['shipping', 'payment', 'review'].includes(currentStep)
                             }">
                            <svg v-if="['shipping', 'payment', 'review'].includes(currentStep)" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8l3.5 3.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span v-else>1</span>
                            <span class="kun-stepper__label">@lang('kun-theme::app.checkout.steps.address')</span>
                        </div>
                    </div>
                    <div class="kun-stepper__line" :class="{ 'kun-stepper__line--completed': ['shipping', 'payment', 'review'].includes(currentStep) }"></div>
                    <div class="kun-stepper__step" v-if="cart.have_stockable_items">
                        <div class="kun-stepper__dot"
                             :class="{
                                 'kun-stepper__dot--active': currentStep === 'shipping',
                                 'kun-stepper__dot--completed': ['payment', 'review'].includes(currentStep)
                             }">
                            <svg v-if="['payment', 'review'].includes(currentStep)" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8l3.5 3.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span v-else>2</span>
                            <span class="kun-stepper__label">@lang('kun-theme::app.checkout.steps.shipping')</span>
                        </div>
                    </div>
                    <div class="kun-stepper__line" :class="{ 'kun-stepper__line--completed': ['payment', 'review'].includes(currentStep) }" v-if="cart.have_stockable_items"></div>
                    <div class="kun-stepper__step">
                        <div class="kun-stepper__dot"
                             :class="{
                                 'kun-stepper__dot--active': currentStep === 'payment',
                                 'kun-stepper__dot--completed': currentStep === 'review'
                             }">
                            <svg v-if="currentStep === 'review'" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8l3.5 3.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span v-else v-text="cart.have_stockable_items ? '3' : '2'"></span>
                            <span class="kun-stepper__label">@lang('kun-theme::app.checkout.steps.payment')</span>
                        </div>
                    </div>
                    <div class="kun-stepper__line" :class="{ 'kun-stepper__line--completed': currentStep === 'review' }"></div>
                    <div class="kun-stepper__step">
                        <div class="kun-stepper__dot"
                             :class="{ 'kun-stepper__dot--active': currentStep === 'review' }">
                            <span v-text="cart.have_stockable_items ? '4' : '3'"></span>
                            <span class="kun-stepper__label">@lang('kun-theme::app.checkout.steps.review')</span>
                        </div>
                    </div>
                </div>

                <div class="kun-checkout-grid py-12">
                    <!-- Left Column: Forms -->
                    <div>
                        <!-- Mobile Summary (shown first on mobile) -->
                        <div class="hidden max-md:block mb-8">
                            @include('shop::checkout.onepage.summary')
                        </div>

                        <!-- Address Section -->
                        <template v-if="['address', 'shipping', 'payment', 'review'].includes(currentStep)">
                            @include('shop::checkout.onepage.address')
                        </template>

                        <!-- Shipping Methods Section -->
                        <template v-if="cart.have_stockable_items && ['shipping', 'payment', 'review'].includes(currentStep)">
                            @include('shop::checkout.onepage.shipping')
                        </template>

                        <!-- Payment Methods Section -->
                        <template v-if="['payment', 'review'].includes(currentStep)">
                            @include('shop::checkout.onepage.payment')
                        </template>

                        <!-- Billing = Shipping Checkbox (visible after payment selected) -->
                        <template v-if="['review'].includes(currentStep)">
                            <div class="kun-checkout-checkbox mt-2">
                                <input
                                    type="checkbox"
                                    id="billing_same_as_shipping_display"
                                    checked
                                    disabled
                                >
                                <label for="billing_same_as_shipping_display">
                                    @lang('kun-theme::app.checkout.cart.billing-same-as-shipping')
                                </label>
                            </div>
                        </template>
                    </div>

                    <!-- Right Column: Cart Summary + Place Order -->
                    <div class="sticky top-8">
                        <!-- Desktop Summary -->
                        <div class="block max-md:hidden">
                            @include('shop::checkout.onepage.summary')
                        </div>

                        <!-- Place Order Button -->
                        <div
                            class="mt-6"
                            v-if="canPlaceOrder"
                        >
                            <template v-if="cart.payment_method == 'paypal_smart_button'">
                                {!! view_render_event('bagisto.shop.checkout.onepage.summary.paypal_smart_button.before') !!}

                                <v-paypal-smart-button></v-paypal-smart-button>

                                {!! view_render_event('bagisto.shop.checkout.onepage.summary.paypal_smart_button.after') !!}
                            </template>

                            <template v-else>
                                <button
                                    type="button"
                                    class="kun-checkout-btn"
                                    :disabled="isPlacingOrder"
                                    @click="placeOrder"
                                >
                                    <span v-if="isPlacingOrder" class="kun-spinner"></span>
                                    @lang('kun-theme::app.checkout.cart.confirm-order')
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-checkout', {
                template: '#v-checkout-template',

                data() {
                    return {
                        cart: null,

                        displayTax: {
                            prices: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_prices') }}",

                            subtotal: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_subtotal') }}",

                            shipping: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_shipping_amount') }}",
                        },

                        isPlacingOrder: false,

                        currentStep: 'address',

                        shippingMethods: null,

                        paymentMethods: null,

                        canPlaceOrder: false,
                    }
                },

                mounted() {
                    this.getCart();
                },

                methods: {
                    getCart() {
                        this.$axios.get("{{ route('shop.checkout.onepage.summary') }}")
                            .then(response => {
                                this.cart = response.data.data;

                                this.scrollToCurrentStep();
                            })
                            .catch(error => {});
                    },

                    stepForward(step) {
                        this.currentStep = step;

                        if (step == 'review') {
                            this.canPlaceOrder = true;

                            return;
                        }

                        this.canPlaceOrder = false;

                        if (this.currentStep == 'shipping') {
                            this.shippingMethods = null;
                        } else if (this.currentStep == 'payment') {
                            this.paymentMethods = null;
                        }
                    },

                    stepProcessed(data) {
                        if (this.currentStep == 'shipping') {
                            this.shippingMethods = data;
                        } else if (this.currentStep == 'payment') {
                            this.paymentMethods = data;
                        }

                        this.getCart();
                    },

                    scrollToCurrentStep() {
                        let container = document.getElementById('steps-container');

                        if (! container) {
                            return;
                        }

                        container.scrollIntoView({
                            behavior: 'smooth',
                            block: 'end'
                        });
                    },

                    placeOrder() {
                        this.isPlacingOrder = true;

                        this.$axios.post('{{ route('shop.checkout.onepage.orders.store') }}')
                            .then(response => {
                                if (response.data.data.redirect) {
                                    window.location.href = response.data.data.redirect_url;
                                } else {
                                    window.location.href = '{{ route('shop.checkout.onepage.success') }}';
                                }

                                this.isPlacingOrder = false;
                            })
                            .catch(error => {
                                this.isPlacingOrder = false

                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            });
                    }
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
