@php
    $title = $options['title'] ?? '';
    $filters = $options['filters'] ?? [];
    $gridRows = $options['grid_rows'] ?? 1;
    $subtitle = $options['subtitle'] ?? '';
    $carouselId = 'kun-products-' . $customization->id;
@endphp

<v-kun-product-carousel
    src="{{ route('shop.api.products.index', $filters) }}"
    title="{{ $title }}"
    subtitle="{{ $subtitle }}"
    navigation-link="{{ route('shop.search.index', $filters) }}"
    carousel-id="{{ $carouselId }}"
    :grid-rows="{{ $gridRows }}"
>
    <section class="kun-section" aria-label="{{ $title ?: $customization->name }}">
        <div class="kun-grid-3">
            @for ($i = 0; $i < ($gridRows > 1 ? 6 : 3); $i++)
                <div class="shimmer rounded-[32px] h-[434px]"></div>
            @endfor
        </div>
    </section>
</v-kun-product-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-kun-product-carousel-template"
    >
        <div>
            <!-- Shimmer while loading -->
            <section class="kun-section" v-if="isLoading">
                <div class="kun-grid-3">
                    <div class="shimmer rounded-[32px] h-[434px]" v-for="n in (gridRows > 1 ? 6 : 3)" :key="n"></div>
                </div>
            </section>

            <!-- Products -->
            <section class="kun-section kun-reveal kun-content-enter" :aria-label="title || 'Products'" v-else-if="products.length">
                <div class="kun-section-header">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2.5">
                            <div class="kun-accent-line"></div>
                            <span class="kun-subtitle-text">@{{ subtitle || title }}</span>
                        </div>
                        <h2 class="kun-section-title">@{{ title }}</h2>
                    </div>

                    <a v-if="navigationLink" :href="navigationLink" class="kun-link-accent">
                        @lang('shop::app.components.products.carousel.view-all')
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <div class="kun-grid-3">
                    <div
                        v-for="product in displayProducts"
                        :key="product.id"
                        class="kun-product-card"
                    >
                        {{-- Image Area --}}
                        <div class="kun-product-image">
                            <a :href="getProductUrl(product)" class="block w-full h-full">
                                <img
                                    :src="product.base_image?.medium_image_url || product.base_image?.original_image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                            </a>

                            {{-- Tags --}}
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

                            {{-- Views Counter --}}
                            <div class="kun-views absolute bottom-4 left-4" v-if="product.reviews?.total">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="#94977B" stroke-width="1.2"/>
                                    <circle cx="8" cy="8" r="2" stroke="#94977B" stroke-width="1.2"/>
                                </svg>
                                <span>@{{ formatViews(product.reviews.total) }} views</span>
                            </div>
                        </div>

                        {{-- Info Area --}}
                        <div class="kun-product-info">
                            <p class="kun-product-vendor" v-if="product.brand?.name">
                                @{{ product.brand.name }}
                            </p>

                            <h3 class="font-poppins m-0">
                                <a :href="getProductUrl(product)" class="kun-product-name-link">
                                    @{{ product.name }}
                                </a>
                            </h3>

                            <div class="kun-rating" v-if="product.ratings && product.ratings.average">
                                <template v-for="star in 5">
                                    <svg width="20" height="20" viewBox="0 0 16 16"
                                        :fill="star <= Math.round(product.ratings.average) ? '#F59E0B' : '#D9CCA9'">
                                        <path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/>
                                    </svg>
                                </template>
                                <span class="text-[#0F172B] text-base leading-6">@{{ product.ratings.average }}</span>
                                <span class="text-[#0F172B] text-xs leading-5">(@{{ formatViews(product.ratings.total) }})</span>
                            </div>

                            <p class="kun-product-desc" v-if="product.short_description">
                                @{{ product.short_description }}
                            </p>

                            <div class="flex items-center gap-1 mt-auto pt-1">
                                <span class="kun-product-price" v-html="product.price_html"></span>
                            </div>

                            {{-- Actions --}}
                            <div class="kun-product-actions mt-2 !px-0">
                                <button
                                    class="kun-btn-cart font-poppins"
                                    :disabled="! product.is_saleable"
                                    @click="addToCart(product)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M7.083 11.875c0 1.6 1.317 2.917 2.917 2.917s2.917-1.317 2.917-2.917" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7.342 1.667L4.325 4.692" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12.658 1.667l3.017 3.025" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M1.667 6.542c0-1.542.825-1.667 1.85-1.667h12.966c1.025 0 1.85.125 1.85 1.667 0 1.791-.825 1.666-1.85 1.666H3.517c-1.025 0-1.85.125-1.85-1.666z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M2.917 8.333l1.175 7.2c.266 1.617.908 2.8 3.291 2.8h5.025c2.592 0 2.975-1.133 3.275-2.7l1.4-7.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    @lang('kun-theme::app.home.add-to-cart')
                                </button>

                                <button
                                    class="kun-btn-wishlist"
                                    :aria-pressed="product.is_wishlist ? 'true' : 'false'"
                                    aria-label="@lang('shop::app.components.products.card.wishlist')"
                                    @click="addToWishlist(product)"
                                >
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M10.517 17.342a.862.862 0 01-1.034 0C7.067 15.592 1.667 11.717 1.667 6.667A4.167 4.167 0 015.833 2.5c1.5 0 2.817.792 3.55 1.975.117.2.117.2.617.025.733-1.183 2.05-2 3.55-2a4.167 4.167 0 014.117 4.167c0 5.05-5.4 8.925-7.15 10.675z"
                                            stroke="#EF4444"
                                            :fill="product.is_wishlist ? '#EF4444' : 'none'"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </script>

    <script type="module">
        app.component('v-kun-product-carousel', {
            template: '#v-kun-product-carousel-template',

            props: ['src', 'title', 'subtitle', 'navigationLink', 'carouselId', 'gridRows'],

            data() {
                return {
                    isLoading: true,
                    products: [],
                };
            },

            computed: {
                displayProducts() {
                    return this.gridRows > 1 ? this.products.slice(0, 6) : this.products.slice(0, 3);
                },
            },

            mounted() {
                this.fetchProducts();
            },

            methods: {
                getProductUrl(product) {
                    return `{{ route('shop.product_or_category.index', '') }}/${product.url_key}`;
                },

                formatViews(num) {
                    if (! num) return '0';
                    if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                    return num.toString();
                },

                fetchProducts() {
                    this.$axios.get(this.src)
                        .then(response => {
                            this.products = response.data.data || [];
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.log(error);
                            this.isLoading = false;
                        });
                },

                addToWishlist(product) {
                    this.$axios.post('{{ route("shop.api.customers.account.wishlist.store") }}', {
                        product_id: product.id,
                    })
                    .then(response => {
                        product.is_wishlist = ! product.is_wishlist;
                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.data.message });
                    })
                    .catch(error => {
                        if (error.response?.status === 401) {
                            window.location.href = '{{ route("shop.customer.session.index") }}';
                        }
                    });
                },

                addToCart(product) {
                    this.$axios.post('{{ route("shop.api.checkout.cart.store") }}', {
                        quantity: 1,
                        product_id: product.id,
                    })
                    .then(response => {
                        if (response.data.message) {
                            this.$emitter.emit('update-mini-cart', response.data.data);
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                        } else {
                            this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                        }
                    })
                    .catch(error => {
                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                        if (error.response.data.redirect_uri) {
                            window.location.href = error.response.data.redirect_uri;
                        }
                    });
                },
            },
        });
    </script>
@endPushOnce
