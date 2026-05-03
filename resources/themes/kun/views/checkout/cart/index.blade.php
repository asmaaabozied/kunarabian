<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.checkout.cart.index.cart')"/>
    <meta name="keywords" content="@lang('shop::app.checkout.cart.index.cart')"/>
@endPush

<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.checkout.cart.index.cart')
    </x-slot>

    <!-- Page Content -->
    <div class="kun-section bg-[var(--kun-slate-50)] min-h-[60vh]">

        {!! view_render_event('bagisto.shop.checkout.cart.breadcrumbs.before') !!}

        <!-- Breadcrumb -->
        <div class="kun-checkout-breadcrumb">
            <a href="{{ route('shop.home.index') }}">
                @lang('kun-theme::app.checkout.breadcrumb.homepage')
            </a>
            <svg class="separator" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3.5L10.5 8L6 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="current">
                @lang('kun-theme::app.checkout.breadcrumb.shopping-cart')
            </span>
        </div>

        {!! view_render_event('bagisto.shop.checkout.cart.breadcrumbs.after') !!}

        @php
            $errors = \Webkul\Checkout\Facades\Cart::getErrors();
        @endphp

        @if (! empty($errors) && $errors['error_code'] === 'MINIMUM_ORDER_AMOUNT')
            <div class="mt-5 py-3 px-5 bg-[#FFF3CD] text-[#383D41] rounded-lg font-poppins text-sm">
                {{ $errors['message'] }}: {{ $errors['amount'] }}
            </div>
        @endif

        <v-cart ref="vCart">
            <!-- Cart Shimmer Effect -->
            <x-shop::shimmer.checkout.cart :count="3" />
        </v-cart>

        @if (core()->getConfigData('sales.checkout.shopping_cart.cross_sell'))
            {!! view_render_event('bagisto.shop.checkout.cart.cross_sell_carousel.before') !!}

            <x-shop::products.carousel
                :title="trans('shop::app.checkout.cart.index.cross-sell.title')"
                :src="route('shop.api.checkout.cart.cross-sell.index')"
            >
            </x-shop::products.carousel>

            {!! view_render_event('bagisto.shop.checkout.cart.cross_sell_carousel.after') !!}
        @endif
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-cart-template"
        >
            <div>
                <!-- Cart Shimmer Effect -->
                <template v-if="isLoading">
                    <x-shop::shimmer.checkout.cart :count="3" />
                </template>

                <!-- Cart Information -->
                <template v-else>
                    <div
                        class="kun-cart-grid pt-12 pb-12"
                        v-if="cart?.items?.length"
                    >
                        <!-- Left Column: Cart Items -->
                        <div class="flex flex-col justify-between min-h-[678px]">
                            <div class="kun-cart-items-container">
                                {!! view_render_event('bagisto.shop.checkout.cart.cart_mass_actions.before') !!}

                                <!-- Select All Row -->
                                <div class="kun-cart-select-all">
                                    <label class="kun-cart-checkbox-wrap">
                                        <input
                                            type="checkbox"
                                            class="kun-cart-checkbox"
                                            v-model="allSelected"
                                            @change="selectAll"
                                        >
                                        <span class="kun-cart-checkbox-label">
                                            @{{ selectedItemsCount }} @lang('kun-theme::app.checkout.cart.items-selected', ['count' => ''])
                                        </span>
                                    </label>

                                    <div v-if="selectedItemsCount" class="flex items-center gap-3">
                                        <span
                                            class="cursor-pointer text-sm text-blue-600 font-poppins"
                                            role="button"
                                            tabindex="0"
                                            @click="removeSelectedItems"
                                        >
                                            @lang('shop::app.checkout.cart.index.remove')
                                        </span>

                                        @if (auth()->guard()->check())
                                            <span class="border-r-2 border-[var(--kun-slate-200)] h-4"></span>

                                            <span
                                                class="cursor-pointer text-sm text-blue-600 font-poppins"
                                                role="button"
                                                tabindex="0"
                                                @click="moveToWishlistSelectedItems"
                                            >
                                                @lang('shop::app.checkout.cart.index.move-to-wishlist')
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {!! view_render_event('bagisto.shop.checkout.cart.cart_mass_actions.after') !!}

                                {!! view_render_event('bagisto.shop.checkout.cart.item.listing.before') !!}

                                <!-- Cart Item Listing -->
                                <div
                                    class="kun-cart-item"
                                    v-for="item in cart?.items"
                                    :key="item.id"
                                >
                                    <label class="kun-cart-checkbox-wrap self-center">
                                        <input
                                            type="checkbox"
                                            class="kun-cart-checkbox"
                                            v-model="item.selected"
                                            @change="updateAllSelected"
                                        >
                                    </label>

                                    {!! view_render_event('bagisto.shop.checkout.cart.item_image.before') !!}

                                    <a :href="`{{ route('shop.product_or_category.index', '') }}/${item.product_url_key}`">
                                        <div class="kun-cart-item-image">
                                            <img
                                                :src="item.base_image.small_image_url"
                                                :alt="item.name"
                                                width="124"
                                                height="124"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                    </a>

                                    {!! view_render_event('bagisto.shop.checkout.cart.item_image.after') !!}

                                    <div class="flex-1 flex items-center justify-between min-h-[118px]">
                                        <div class="flex flex-col justify-between h-[118px]">
                                            <div>
                                                {!! view_render_event('bagisto.shop.checkout.cart.item_name.before') !!}

                                                <a :href="`{{ route('shop.product_or_category.index', '') }}/${item.product_url_key}`" class="no-underline text-inherit">
                                                    <p class="kun-cart-item-name">
                                                        @{{ item.name }}
                                                    </p>
                                                </a>

                                                {!! view_render_event('bagisto.shop.checkout.cart.item_name.after') !!}

                                                {!! view_render_event('bagisto.shop.checkout.cart.item_details.before') !!}

                                                <div class="kun-cart-item-attrs" v-if="item.options.length">
                                                    <template v-for="attribute in item.options">
                                                        <p class="kun-cart-item-attr" v-if="attribute?.attribute_type !== 'file'">
                                                            @{{ attribute.attribute_name }}: <span>@{{ attribute.option_label }}</span>
                                                        </p>
                                                        <p class="kun-cart-item-attr" v-else>
                                                            @{{ attribute.attribute_name }}:
                                                            <a :href="attribute.file_url" target="_blank" :download="attribute.file_name" class="text-blue-600">
                                                                @{{ attribute.file_name }}
                                                            </a>
                                                        </p>
                                                    </template>
                                                </div>

                                                {!! view_render_event('bagisto.shop.checkout.cart.item_details.after') !!}
                                            </div>

                                            {!! view_render_event('bagisto.shop.checkout.cart.formatted_total.before') !!}

                                            <p class="kun-cart-item-price">
                                                <template v-if="displayTax.prices == 'including_tax'">
                                                    @{{ item.formatted_total_incl_tax }}
                                                </template>
                                                <template v-else-if="displayTax.prices == 'both'">
                                                    @{{ item.formatted_total_incl_tax }}
                                                </template>
                                                <template v-else>
                                                    @{{ item.formatted_total }}
                                                </template>
                                            </p>

                                            {!! view_render_event('bagisto.shop.checkout.cart.formatted_total.after') !!}
                                        </div>

                                        <div class="flex flex-col items-end justify-between h-[124px] w-[126px]">
                                            <!-- Trash Icon -->
                                            <button
                                                class="kun-cart-trash"
                                                type="button"
                                                @click="removeItem(item.id)"
                                                :aria-label="'@lang('shop::app.checkout.cart.index.remove')'"
                                            >
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M21 5.98C17.67 5.65 14.32 5.48 10.98 5.48C9 5.48 7.02 5.58 5.04 5.78L3 5.98" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M18.85 9.14L18.2 19.21C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10.33 16.5H13.66" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M9.5 12.5H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>

                                            {!! view_render_event('bagisto.shop.checkout.cart.quantity_changer.before') !!}

                                            <!-- Quantity Pill -->
                                            <div class="kun-cart-qty" v-if="item.can_change_qty">
                                                <button type="button" @click="decrementQty(item)">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6 12H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                                <span class="kun-cart-qty-value">@{{ getItemQty(item) }}</span>
                                                <button type="button" @click="incrementQty(item)">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6 12H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M12 18V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            {!! view_render_event('bagisto.shop.checkout.cart.quantity_changer.after') !!}
                                        </div>
                                    </div>
                                </div>

                                {!! view_render_event('bagisto.shop.checkout.cart.item.listing.after') !!}
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.cart.controls.before') !!}

                            <!-- Bottom Action Bar -->
                            <div class="kun-cart-actions">
                                <div class="kun-cart-actions-inner">
                                    {!! view_render_event('bagisto.shop.checkout.cart.continue_shopping.before') !!}

                                    <a
                                        class="kun-cart-btn-outline"
                                        href="{{ route('shop.home.index') }}"
                                    >
                                        @lang('kun-theme::app.checkout.cart.continue-shopping')
                                    </a>

                                    {!! view_render_event('bagisto.shop.checkout.cart.continue_shopping.after') !!}

                                    {!! view_render_event('bagisto.shop.checkout.cart.update_cart.before') !!}

                                    <button
                                        type="button"
                                        class="kun-cart-btn-filled"
                                        :disabled="isStoring"
                                        @click="update()"
                                    >
                                        <template v-if="isStoring">
                                            <svg class="animate-spin mr-2" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.416" stroke-dashoffset="10" stroke-linecap="round">
                                                    <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" from="0 12 12" to="360 12 12"/>
                                                </circle>
                                            </svg>
                                        </template>
                                        Update Cart
                                    </button>

                                    {!! view_render_event('bagisto.shop.checkout.cart.update_cart.after') !!}
                                </div>
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.cart.controls.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.checkout.cart.summary.before') !!}

                        <!-- Right Column: Order Summary -->
                        <div class="sticky top-8">
                            @include('shop::checkout.cart.summary')
                        </div>

                        {!! view_render_event('bagisto.shop.checkout.cart.summary.after') !!}
                    </div>

                    <!-- Empty Cart Section -->
                    <div
                        class="grid place-content-center justify-items-center text-center py-32"
                        v-else
                    >
                        <img
                            src="{{ bagisto_asset('images/thank-you.png') }}"
                            alt="@lang('shop::app.checkout.cart.index.empty-product')"
                            loading="lazy"
                            decoding="async"
                            class="max-w-[100px] max-h-[100px]"
                        />

                        <p class="font-poppins text-xl mt-4 text-[var(--kun-slate-950)]">
                            @lang('shop::app.checkout.cart.index.empty-product')
                        </p>
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component("v-cart", {
                template: '#v-cart-template',

                data() {
                    return  {
                        cart: [],

                        allSelected: false,

                        applied: {
                            quantity: {},
                        },

                        displayTax: {
                            prices: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_prices') }}",

                            subtotal: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_subtotal') }}",

                            shipping: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_shipping_amount') }}",
                        },

                        isLoading: true,

                        isStoring: false,
                    }
                },

                mounted() {
                    this.getCart();
                },

                computed: {
                    selectedItemsCount() {
                        return this.cart.items ? this.cart.items.filter(item => item.selected).length : 0;
                    },
                },

                methods: {
                    getCart() {
                        this.$axios.get('{{ route('shop.api.checkout.cart.index') }}')
                            .then(response => {
                                this.cart = response.data.data;

                                this.isLoading = false;

                                if (response.data.message) {
                                    this.$emitter.emit('add-flash', { type: 'info', message: response.data.message });
                                }
                            })
                            .catch(error => {});
                    },

                    setCart(cart) {
                        this.cart = cart;
                    },

                    selectAll() {
                        for (let item of this.cart.items) {
                            item.selected = this.allSelected;
                        }
                    },

                    updateAllSelected() {
                        this.allSelected = this.cart.items.every(item => item.selected);
                    },

                    getItemQty(item) {
                        return this.applied.quantity[item.id] ?? item.quantity;
                    },

                    incrementQty(item) {
                        let current = this.getItemQty(item);
                        this.applied.quantity[item.id] = current + 1;
                    },

                    decrementQty(item) {
                        let current = this.getItemQty(item);
                        if (current > 1) {
                            this.applied.quantity[item.id] = current - 1;
                        }
                    },

                    setItemQuantity(itemId, quantity) {
                        this.applied.quantity[itemId] = quantity;
                    },

                    update() {
                        this.isStoring = true;

                        this.$axios.put('{{ route('shop.api.checkout.cart.update') }}', { qty: this.applied.quantity })
                            .then(response => {
                                if (response.data.message) {
                                    this.cart = response.data.data;

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                }

                                this.isStoring = false;

                            })
                            .catch(error => {
                                this.isStoring = false;
                            });
                    },

                    removeItem(itemId) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.$axios.post('{{ route('shop.api.checkout.cart.destroy') }}', {
                                        '_method': 'DELETE',
                                        'cart_item_id': itemId,
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;

                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    removeSelectedItems() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.cart.items.flatMap(item => item.selected ? item.id : []);

                                this.$axios.post('{{ route('shop.api.checkout.cart.destroy_selected') }}', {
                                        '_method': 'DELETE',
                                        'ids': selectedItemsIds,
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;

                                        this.$emitter.emit('update-mini-cart', response.data.data );

                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    moveToWishlistSelectedItems() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.cart.items.flatMap(item => item.selected ? item.id : []);

                                const selectedItemsQty = this.cart.items.filter(item => item.selected).map(item => this.applied.quantity[item.id] ?? item.quantity);

                                this.$axios.post('{{ route('shop.api.checkout.cart.move_to_wishlist') }}', {
                                        'ids': selectedItemsIds,
                                        'qty': selectedItemsQty
                                    })
                                    .then(response => {
                                        this.cart = response.data.data;

                                        this.$emitter.emit('update-mini-cart', response.data.data );

                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    })
                                    .catch(error => {});
                            }
                        });
                    },
                }
            });
        </script>
    @endpushOnce
</x-shop::layouts>
