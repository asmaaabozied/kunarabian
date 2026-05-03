<v-product-card
    {{ $attributes }}
    :product="product"
>
</v-product-card>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-card-template"
    >
        <!-- Grid Card -->
        <div
            class="kun-product-card"
            v-if="mode != 'list'"
        >
            <!-- Image Area -->
            <div class="kun-product-image">
                <a :href="productUrl" class="block w-full h-full">
                    <img
                        :src="product.base_image?.medium_image_url || product.base_image?.original_image_url"
                        :alt="product.name"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                </a>

                <!-- Tag Pills -->
                <div class="absolute top-4 left-4 flex items-center gap-2 flex-wrap">
                    <span v-if="product.brand?.name" class="kun-tag kun-tag--brand">
                        @{{ product.brand.name }}
                    </span>
                    <span v-if="product.on_sale" class="kun-tag kun-tag--sale opacity-80">
                        @lang('shop::app.components.products.card.sale')
                    </span>
                    <span v-if="product.is_new" class="kun-tag kun-tag--new opacity-80">
                        @lang('shop::app.components.products.card.new')
                    </span>
                </div>

                <!-- Views Counter -->
                <div class="kun-views absolute bottom-4 left-4" v-if="product.reviews?.total">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="#94977B" stroke-width="1.2"/>
                        <circle cx="8" cy="8" r="2" stroke="#94977B" stroke-width="1.2"/>
                    </svg>
                    <span>@{{ formatViews(product.reviews.total) }} views</span>
                </div>
            </div>

            <!-- Info Area -->
            <div class="kun-product-info">
                <p class="kun-product-vendor" v-if="product.brand?.name">
                    @{{ product.brand.name }}
                </p>

                <h3 class="font-poppins m-0">
                    <a :href="productUrl" class="kun-product-name-link">
                        @{{ product.name }}
                    </a>
                </h3>

                <!-- Star Rating -->
                <div class="kun-rating" v-if="product.ratings && product.ratings.average">
                    <template v-for="star in 5">
                        <svg width="20" height="20" viewBox="0 0 16 16"
                            :fill="star <= starRating ? '#F59E0B' : '#D9CCA9'">
                            <path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/>
                        </svg>
                    </template>
                    <span class="text-[#0F172B] text-base leading-6">@{{ product.ratings.average }}</span>
                    <span class="text-[#0F172B] text-xs leading-5">(@{{ formatViews(product.ratings.total) }})</span>
                </div>

                <p class="kun-product-desc" v-if="product.short_description">
                    @{{ product.short_description }}
                </p>

                <!-- Price -->
                <div class="flex items-center gap-1 mt-auto pt-1">
                    <span class="kun-product-price" v-html="product.price_html"></span>
                </div>

                <!-- Actions -->
                <div class="kun-product-actions mt-2 !px-0">
                    @if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                        <button
                            class="kun-btn-cart font-poppins"
                            :class="{ 'kun-btn-cart--success': addedToCart }"
                            :disabled="! product.is_saleable || isAddingToCart"
                            @click="addToCart()"
                        >
                            <span v-if="isAddingToCart" class="kun-spinner kun-spinner--sm"></span>
                            <svg v-else-if="addedToCart" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M7.083 11.875c0 1.6 1.317 2.917 2.917 2.917s2.917-1.317 2.917-2.917" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.342 1.667L4.325 4.692" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.658 1.667l3.017 3.025" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M1.667 6.542c0-1.542.825-1.667 1.85-1.667h12.966c1.025 0 1.85.125 1.85 1.667 0 1.791-.825 1.666-1.85 1.666H3.517c-1.025 0-1.85.125-1.85-1.666z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M2.917 8.333l1.175 7.2c.266 1.617.908 2.8 3.291 2.8h5.025c2.592 0 2.975-1.133 3.275-2.7l1.4-7.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span v-if="addedToCart">@lang('shop::app.components.products.card.added')</span>
                            <span v-else>@lang('shop::app.components.products.card.add-to-cart')</span>
                        </button>
                    @endif

                    @if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
                        <button
                            class="kun-btn-wishlist"
                            :class="{ 'kun-btn-wishlist--animate': wishlistAnimating }"
                            :aria-pressed="product.is_wishlist ? 'true' : 'false'"
                            aria-label="@lang('shop::app.components.products.card.add-to-wishlist')"
                            @click="addToWishlist()"
                            @animationend="wishlistAnimating = false"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M10.517 17.342a.862.862 0 01-1.034 0C7.067 15.592 1.667 11.717 1.667 6.667A4.167 4.167 0 015.833 2.5c1.5 0 2.817.792 3.55 1.975.117.2.117.2.617.025.733-1.183 2.05-2 3.55-2a4.167 4.167 0 014.117 4.167c0 5.05-5.4 8.925-7.15 10.675z"
                                    stroke="#EF4444"
                                    :fill="product.is_wishlist ? '#EF4444' : 'none'"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- List Card -->
        <div
            class="kun-product-card !flex-row"
            v-else
        >
            <!-- Image -->
            <div class="relative flex-shrink-0 w-[280px] min-h-[220px]">
                <a :href="productUrl" class="block w-full h-full">
                    <img
                        :src="product.base_image?.medium_image_url || product.base_image?.original_image_url"
                        :alt="product.name"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                </a>

                <div class="absolute top-4 left-4 flex items-center gap-2 flex-wrap">
                    <span v-if="product.on_sale" class="kun-tag kun-tag--sale opacity-80">
                        @lang('shop::app.components.products.card.sale')
                    </span>
                    <span v-if="product.is_new" class="kun-tag kun-tag--new opacity-80">
                        @lang('shop::app.components.products.card.new')
                    </span>
                </div>
            </div>

            <!-- Info -->
            <div class="flex flex-col flex-1 p-5 gap-1.5">
                <p class="kun-product-vendor" v-if="product.brand?.name">
                    @{{ product.brand.name }}
                </p>

                <h3 class="font-poppins m-0">
                    <a :href="productUrl" class="kun-product-name-link !whitespace-normal">
                        @{{ product.name }}
                    </a>
                </h3>

                <div class="kun-rating" v-if="product.ratings && product.ratings.average">
                    <template v-for="star in 5">
                        <svg width="20" height="20" viewBox="0 0 16 16"
                            :fill="star <= starRating ? '#F59E0B' : '#D9CCA9'">
                            <path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/>
                        </svg>
                    </template>
                    <span class="text-[#0F172B] text-base">@{{ product.ratings.average }}</span>
                    <span class="text-[#0F172B] text-xs">(@{{ formatViews(product.ratings.total) }})</span>
                </div>

                <p class="kun-product-desc" v-if="product.short_description">
                    @{{ product.short_description }}
                </p>

                <div class="flex items-center gap-1 pt-1">
                    <span class="kun-product-price" v-html="product.price_html"></span>
                </div>

                <div class="flex items-center gap-2 mt-auto pt-3">
                    @if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                        <button
                            class="kun-btn-cart font-poppins !flex-initial px-6"
                            :disabled="! product.is_saleable || isAddingToCart"
                            @click="addToCart()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M7.083 11.875c0 1.6 1.317 2.917 2.917 2.917s2.917-1.317 2.917-2.917" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.342 1.667L4.325 4.692" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.658 1.667l3.017 3.025" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M1.667 6.542c0-1.542.825-1.667 1.85-1.667h12.966c1.025 0 1.85.125 1.85 1.667 0 1.791-.825 1.666-1.85 1.666H3.517c-1.025 0-1.85.125-1.85-1.666z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M2.917 8.333l1.175 7.2c.266 1.617.908 2.8 3.291 2.8h5.025c2.592 0 2.975-1.133 3.275-2.7l1.4-7.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            @lang('shop::app.components.products.card.add-to-cart')
                        </button>
                    @endif

                    @if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
                        <button
                            class="kun-btn-wishlist"
                            :aria-pressed="product.is_wishlist ? 'true' : 'false'"
                            aria-label="@lang('shop::app.components.products.card.add-to-wishlist')"
                            @click="addToWishlist()"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M10.517 17.342a.862.862 0 01-1.034 0C7.067 15.592 1.667 11.717 1.667 6.667A4.167 4.167 0 015.833 2.5c1.5 0 2.817.792 3.55 1.975.117.2.117.2.617.025.733-1.183 2.05-2 3.55-2a4.167 4.167 0 014.117 4.167c0 5.05-5.4 8.925-7.15 10.675z"
                                    stroke="#EF4444"
                                    :fill="product.is_wishlist ? '#EF4444' : 'none'"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    @endif

                    @if (core()->getConfigData('catalog.products.settings.compare_option'))
                        <button
                            class="kun-btn-wishlist !bg-slate-100"
                            aria-label="@lang('shop::app.components.products.card.add-to-compare')"
                            @click="addToCompare(product.id)"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 3l4 4-4 4"/>
                                <path d="M20 7H4"/>
                                <path d="M8 21l-4-4 4-4"/>
                                <path d="M4 17h16"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-product-card', {
            template: '#v-product-card-template',

            props: ['mode', 'product'],

            data() {
                return {
                    isCustomer: '{{ auth()->guard("customer")->check() }}',
                    isAddingToCart: false,
                    addedToCart: false,
                    wishlistAnimating: false,
                }
            },

            computed: {
                productUrl() {
                    return `{{ route('shop.product_or_category.index', '') }}/${this.product.url_key}`;
                },

                starRating() {
                    return Math.round(this.product.ratings?.average || 0);
                },
            },

            methods: {
                formatViews(num) {
                    if (! num) return '0';
                    if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                    return num.toString();
                },

                addToWishlist() {
                    if (this.isCustomer) {
                        this.$axios.post(`{{ route('shop.api.customers.account.wishlist.store') }}`, {
                                product_id: this.product.id
                            })
                            .then(response => {
                                this.product.is_wishlist = ! this.product.is_wishlist;
                                this.wishlistAnimating = true;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.data.message });
                            })
                            .catch(error => {});
                    } else {
                        window.location.href = "{{ route('shop.customer.session.index')}}";
                    }
                },

                addToCompare(productId) {
                    if (this.isCustomer) {
                        this.$axios.post('{{ route("shop.api.compare.store") }}', {
                                'product_id': productId
                            })
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.data.message });
                            })
                            .catch(error => {
                                if ([400, 422].includes(error.response.status)) {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.data.message });

                                    return;
                                }

                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message});
                            });

                        return;
                    }

                    let items = this.getStorageValue() ?? [];

                    if (items.length) {
                        if (! items.includes(productId)) {
                            items.push(productId);

                            localStorage.setItem('compare_items', JSON.stringify(items));

                            this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.components.products.card.add-to-compare-success')" });
                        } else {
                            this.$emitter.emit('add-flash', { type: 'warning', message: "@lang('shop::app.components.products.card.already-in-compare')" });
                        }
                    } else {
                        localStorage.setItem('compare_items', JSON.stringify([productId]));

                        this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.components.products.card.add-to-compare-success')" });
                    }
                },

                getStorageValue(key) {
                    let value = localStorage.getItem('compare_items');

                    if (! value) {
                        return [];
                    }

                    return JSON.parse(value);
                },

                addToCart() {
                    this.isAddingToCart = true;
                    this.addedToCart = false;

                    this.$axios.post('{{ route("shop.api.checkout.cart.store") }}', {
                            'quantity': 1,
                            'product_id': this.product.id,
                        })
                        .then(response => {
                            if (response.data.message) {
                                this.$emitter.emit('update-mini-cart', response.data.data );
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                this.addedToCart = true;
                                setTimeout(() => { this.addedToCart = false; }, 2000);
                            } else {
                                this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                            }

                            this.isAddingToCart = false;
                        })
                        .catch(error => {
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                            if (error.response.data.redirect_uri) {
                                window.location.href = error.response.data.redirect_uri;
                            }

                            this.isAddingToCart = false;
                        });
                },
            },
        });
    </script>
@endpushOnce
