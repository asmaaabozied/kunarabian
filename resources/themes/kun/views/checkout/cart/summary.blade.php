<!-- Order Summary -->
<div class="kun-cart-summary">
    <!-- Top: Title + Totals + Promo -->
    <div>
        {!! view_render_event('bagisto.shop.checkout.cart.summary.title.before') !!}

        <h2 class="kun-cart-summary-title">
            @lang('kun-theme::app.checkout.cart.order-summary')
        </h2>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.title.after') !!}

        <!-- Cart Totals -->
        <div class="flex flex-col gap-5 mt-6">

            <!-- Sub Total -->
            {!! view_render_event('bagisto.shop.checkout.cart.summary.sub_total.before') !!}

            <template v-if="displayTax.subtotal == 'including_tax'">
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('kun-theme::app.checkout.cart.subtotal')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_sub_total_incl_tax }}</p>
                </div>
            </template>

            <template v-else-if="displayTax.subtotal == 'both'">
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('shop::app.checkout.cart.summary.sub-total-excl-tax')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_sub_total }}</p>
                </div>
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('shop::app.checkout.cart.summary.sub-total-incl-tax')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_sub_total_incl_tax }}</p>
                </div>
            </template>

            <template v-else>
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('kun-theme::app.checkout.cart.subtotal')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_sub_total }}</p>
                </div>
            </template>

            {!! view_render_event('bagisto.shop.checkout.cart.summary.sub_total.after') !!}

            <!-- Discount -->
            {!! view_render_event('bagisto.shop.checkout.cart.summary.discount_amount.before') !!}

            <div
                class="kun-cart-summary-row"
                v-if="cart.discount_amount && parseFloat(cart.discount_amount) > 0"
            >
                <p class="kun-cart-summary-label">@lang('shop::app.checkout.cart.summary.discount-amount')</p>
                <p class="kun-cart-summary-value kun-cart-summary-value--discount">
                    @{{ cart.formatted_discount_amount }}
                </p>
            </div>

            {!! view_render_event('bagisto.shop.checkout.cart.summary.discount_amount.after') !!}

            <!-- Shipping Rates -->
            {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.before') !!}

            <template v-if="displayTax.shipping == 'including_tax'">
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('kun-theme::app.checkout.cart.delivery-fee')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_shipping_amount_incl_tax }}</p>
                </div>
            </template>

            <template v-else-if="displayTax.shipping == 'both'">
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('shop::app.checkout.cart.summary.delivery-charges-excl-tax')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_shipping_amount }}</p>
                </div>
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('shop::app.checkout.cart.summary.delivery-charges-incl-tax')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_shipping_amount_incl_tax }}</p>
                </div>
            </template>

            <template v-else>
                <div class="kun-cart-summary-row">
                    <p class="kun-cart-summary-label">@lang('kun-theme::app.checkout.cart.delivery-fee')</p>
                    <p class="kun-cart-summary-value">@{{ cart.formatted_shipping_amount }}</p>
                </div>
            </template>

            {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.after') !!}

            <!-- Divider -->
            <hr class="kun-cart-summary-divider">

            <!-- Cart Grand Total -->
            {!! view_render_event('bagisto.shop.checkout.cart.summary.grand_total.before') !!}

            <div class="kun-cart-summary-row">
                <p class="kun-cart-summary-total-label">@lang('kun-theme::app.checkout.cart.total')</p>
                <p class="kun-cart-summary-total-value">@{{ cart.formatted_grand_total }}</p>
            </div>

            {!! view_render_event('bagisto.shop.checkout.cart.summary.grand_total.after') !!}
        </div>

        <!-- Promo Code -->
        {!! view_render_event('bagisto.shop.checkout.cart.summary.coupon.before') !!}

        <v-cart-coupon
            :cart="cart"
            @coupon-applied="getCart"
            @coupon-removed="getCart"
        ></v-cart-coupon>

        {!! view_render_event('bagisto.shop.checkout.cart.summary.coupon.after') !!}
    </div>

    <!-- Bottom: Proceed to Checkout -->
    {!! view_render_event('bagisto.shop.checkout.cart.summary.proceed_to_checkout.before') !!}

    <a
        href="{{ route('shop.checkout.onepage.index') }}"
        class="kun-cart-proceed-btn"
    >
        @lang('kun-theme::app.checkout.cart.proceed-to-checkout')
    </a>

    {!! view_render_event('bagisto.shop.checkout.cart.summary.proceed_to_checkout.after') !!}
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-cart-coupon-template"
    >
        <div class="mt-6">
            <!-- Applied Coupon Display -->
            <div
                class="kun-cart-promo"
                v-if="cart.coupon_code"
            >
                <div class="kun-cart-promo-input flex items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.17 15.3L8.7 19.83C10.56 21.69 13.58 21.69 15.45 19.83L19.84 15.44C21.7 13.58 21.7 10.56 19.84 8.69L15.3 4.17C14.35 3.22 13.04 2.71 11.7 2.78L6.7 3.02C4.7 3.11 3.11 4.7 3.01 6.69L2.77 11.69C2.71 13.04 3.22 14.35 4.17 15.3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 12C10.8807 12 12 10.8807 12 9.5C12 8.11929 10.8807 7 9.5 7C8.11929 7 7 8.11929 7 9.5C7 10.8807 8.11929 12 9.5 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="font-poppins text-base text-[var(--kun-slate-950)] font-medium">
                        "@{{ cart.coupon_code }}"
                    </span>
                </div>
                <button
                    type="button"
                    class="kun-cart-promo-btn bg-red-500 hover:bg-red-600"
                    @click="destroyCoupon"
                >
                    @lang('kun-theme::app.checkout.cart.remove')
                </button>
            </div>

            <!-- Coupon Input Form -->
            <form
                v-else
                @submit.prevent="applyCoupon"
                class="kun-cart-promo"
            >
                <div class="kun-cart-promo-input">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                        <path d="M4.17 15.3L8.7 19.83C10.56 21.69 13.58 21.69 15.45 19.83L19.84 15.44C21.7 13.58 21.7 10.56 19.84 8.69L15.3 4.17C14.35 3.22 13.04 2.71 11.7 2.78L6.7 3.02C4.7 3.11 3.11 4.7 3.01 6.69L2.77 11.69C2.71 13.04 3.22 14.35 4.17 15.3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 12C10.8807 12 12 10.8807 12 9.5C12 8.11929 10.8807 7 9.5 7C8.11929 7 7 8.11929 7 9.5C7 10.8807 8.11929 12 9.5 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="text"
                        v-model="couponCode"
                        placeholder="@lang('kun-theme::app.checkout.cart.promo-placeholder')"
                        class="flex-1 border-none bg-transparent outline-none font-poppins text-base text-[var(--kun-slate-950)]"
                    />
                </div>
                <button
                    type="submit"
                    class="kun-cart-promo-btn"
                    :disabled="isStoring || !couponCode"
                >
                    <template v-if="isStoring">...</template>
                    <template v-else>@lang('kun-theme::app.checkout.cart.apply')</template>
                </button>
            </form>
        </div>
    </script>

    <script type="module">
        app.component('v-cart-coupon', {
            template: '#v-cart-coupon-template',

            props: ['cart'],

            data() {
                return {
                    couponCode: '',
                    isStoring: false,
                }
            },

            methods: {
                applyCoupon() {
                    if (! this.couponCode) return;

                    this.isStoring = true;

                    this.$axios.post("{{ route('shop.api.checkout.cart.coupon.apply') }}", {
                            code: this.couponCode
                        })
                        .then((response) => {
                            this.isStoring = false;
                            this.couponCode = '';

                            this.$emit('coupon-applied');

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                        })
                        .catch((error) => {
                            this.isStoring = false;

                            if ([400, 422].includes(error.response?.request?.status)) {
                                this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.message });
                                this.couponCode = '';
                                return;
                            }

                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                        });
                },

                destroyCoupon() {
                    this.$axios.delete("{{ route('shop.api.checkout.cart.coupon.remove') }}", {
                            '_token': "{{ csrf_token() }}"
                        })
                        .then((response) => {
                            this.$emit('coupon-removed');

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                        })
                        .catch(error => console.log(error));
                },
            }
        })
    </script>
@endPushOnce
