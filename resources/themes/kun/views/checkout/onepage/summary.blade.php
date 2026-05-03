<!-- Cart Summary -->
<div class="kun-checkout-summary flex flex-col justify-between min-h-[600px]">
    <!-- Top: Title + Cart Items -->
    <div>
        <h2 class="kun-checkout-summary-title">
            @lang('kun-theme::app.checkout.cart.cart-summary')
        </h2>

        <!-- Cart Items -->
        <div>
            <div
                class="kun-checkout-item"
                v-for="item in cart.items"
            >
                {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_image.before') !!}

                <img
                    class="kun-checkout-item-image"
                    :src="item.base_image.small_image_url"
                    :alt="item.name"
                    width="82"
                    height="82"
                />

                {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_image.after') !!}

                <div class="flex-1 flex flex-col justify-between min-h-[82px]">
                    {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_name.before') !!}

                    <div>
                        <p class="kun-checkout-item-name">
                            @{{ item.name }}
                        </p>

                        <div class="flex gap-1 mt-0.5">
                            <template v-for="attribute in item.additional?.attributes || []">
                                <p class="kun-checkout-item-attr">
                                    @{{ attribute.attribute_name }}: <span>@{{ attribute.option_label }}</span>
                                </p>
                            </template>
                        </div>
                    </div>

                    {!! view_render_event('bagisto.shop.checkout.onepage.summary.item_name.after') !!}

                    <p class="kun-checkout-item-price">
                        <template v-if="displayTax.prices == 'including_tax'">
                            @{{ item.formatted_price_incl_tax }} x@{{ item.quantity }}
                        </template>

                        <template v-else>
                            @{{ item.formatted_price }} x@{{ item.quantity }}
                        </template>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom: Cart Totals -->
    <div>
        <!-- Sub Total -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.sub_total.before') !!}

        <template v-if="displayTax.subtotal == 'including_tax'">
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('kun-theme::app.checkout.cart.subtotal')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_sub_total_incl_tax }}</p>
            </div>
        </template>

        <template v-else-if="displayTax.subtotal == 'both'">
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('shop::app.checkout.onepage.summary.sub-total-excl-tax')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_sub_total }}</p>
            </div>
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('shop::app.checkout.onepage.summary.sub-total-incl-tax')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_sub_total_incl_tax }}</p>
            </div>
        </template>

        <template v-else>
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('kun-theme::app.checkout.cart.subtotal')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_sub_total }}</p>
            </div>
        </template>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.sub_total.after') !!}

        <!-- Discount -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.discount_amount.before') !!}

        <div
            class="kun-checkout-summary-row"
            v-if="cart.discount_amount && parseFloat(cart.discount_amount) > 0"
        >
            <p class="kun-checkout-summary-label">@lang('shop::app.checkout.onepage.summary.discount-amount')</p>
            <p class="kun-checkout-summary-value kun-checkout-summary-value--discount">
                @{{ cart.formatted_discount_amount }}
            </p>
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.discount_amount.after') !!}

        <!-- Apply Coupon -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.coupon.before') !!}

        @include('shop::checkout.coupon')

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.coupon.after') !!}

        <!-- Shipping Rates -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.before') !!}

        <template v-if="displayTax.shipping == 'including_tax'">
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('kun-theme::app.checkout.cart.delivery-fee')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_shipping_amount_incl_tax }}</p>
            </div>
        </template>

        <template v-else-if="displayTax.shipping == 'both'">
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('shop::app.checkout.onepage.summary.delivery-charges-excl-tax')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_shipping_amount }}</p>
            </div>
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('shop::app.checkout.onepage.summary.delivery-charges-incl-tax')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_shipping_amount_incl_tax }}</p>
            </div>
        </template>

        <template v-else>
            <div class="kun-checkout-summary-row">
                <p class="kun-checkout-summary-label">@lang('kun-theme::app.checkout.cart.delivery-fee')</p>
                <p class="kun-checkout-summary-value">@{{ cart.formatted_shipping_amount }}</p>
            </div>
        </template>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.delivery_charges.after') !!}

        <!-- Taxes -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.tax.before') !!}

        <div class="kun-checkout-summary-row">
            <p class="kun-checkout-summary-label">@lang('kun-theme::app.checkout.cart.tax')</p>
            <p class="kun-checkout-summary-value">@{{ cart.formatted_tax_total }}</p>
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.tax.after') !!}

        <!-- Divider -->
        <hr class="kun-checkout-summary-divider">

        <!-- Cart Grand Total -->
        {!! view_render_event('bagisto.shop.checkout.onepage.summary.grand_total.before') !!}

        <div class="kun-checkout-summary-row">
            <p class="kun-checkout-summary-total-label">@lang('kun-theme::app.checkout.cart.total')</p>
            <p class="kun-checkout-summary-total-value">@{{ cart.formatted_grand_total }}</p>
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.summary.grand_total.after') !!}
    </div>
</div>
