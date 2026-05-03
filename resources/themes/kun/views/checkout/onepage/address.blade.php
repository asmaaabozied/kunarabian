{!! view_render_event('bagisto.shop.checkout.onepage.address.before') !!}

<div class="mb-6">
    <h3 class="kun-checkout-section-title mb-6">
        Shipping address
    </h3>

    <!-- If the customer is guest -->
    <template v-if="cart.is_guest">
        @include('shop::checkout.onepage.address.guest')
    </template>

    <!-- If the customer is logged in -->
    <template v-else>
        @include('shop::checkout.onepage.address.customer')
    </template>
</div>

{!! view_render_event('bagisto.shop.checkout.onepage.address.after') !!}
