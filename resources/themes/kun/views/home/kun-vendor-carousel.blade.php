@php
    $title = $options['title'] ?? __('kun-theme::app.home.our-premium-shops');
@endphp

<v-kun-vendor-carousel
    title="{{ $title }}"
>
    <section class="kun-section" aria-label="{{ $title }}">
        <div class="kun-grid-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="shimmer rounded-[36px] h-[500px]"></div>
            @endfor
        </div>
    </section>
</v-kun-vendor-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-kun-vendor-carousel-template"
    >
        <div>
            <!-- Shimmer -->
            <section class="kun-section" v-if="isLoading">
                <div class="grid grid-cols-3 gap-6">
                    <div class="shimmer rounded-[36px] h-[500px]" v-for="n in 3" :key="n"></div>
                </div>
            </section>

            <!-- Vendors -->
            <section class="kun-section kun-reveal kun-content-enter" :aria-label="title" v-else-if="sellers.length">
                {{-- Section Header --}}
                <div class="kun-section-header">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2.5">
                            <div class="kun-accent-line"></div>
                            <span class="kun-subtitle-text">@lang('kun-theme::app.home.featured-vendors')</span>
                        </div>
                        <h2 class="kun-section-title">
                            @{{ title }}
                        </h2>
                    </div>

                    <a
                        href="{{ route('shop.marketplace.index') }}"
                        class="kun-link-accent"
                    >
                        @lang('kun-theme::app.home.view-all-shops')
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Cards with Arrows --}}
                <div class="relative">
                    {{-- Left Arrow --}}
                    <button
                        class="kun-nav-arrow kun-nav-arrow--left"
                        aria-label="@lang('shop::app.components.products.carousel.previous')"
                        @click="scrollTrack(-1)"
                        v-if="canScrollLeft"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="rotate-180">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        ref="track"
                        class="kun-vendor-track"
                        @scroll="updateScrollState"
                    >
                        <div
                            v-for="seller in sellers"
                            :key="seller.slug"
                            class="kun-vendor-card"
                        >
                            {{-- Tag pills on outer card --}}
                            <div class="absolute top-6 left-6 flex items-center gap-2 z-[2]">
                                <span class="kun-tag kun-tag--origin opacity-80">
                                    Origin&#8209;verified
                                </span>
                                <span
                                    v-if="seller.country"
                                    class="kun-tag kun-tag--brand"
                                >
                                    @{{ seller.country }}
                                </span>
                            </div>

                            {{-- Inner white card --}}
                            <div class="absolute inset-2 bg-[var(--kun-color-cream)] rounded-[32px] overflow-hidden">
                                {{-- Banner Image --}}
                                <div class="h-[195px] relative overflow-hidden">
                                    <img
                                        v-if="seller.banner_url"
                                        :src="seller.banner_url"
                                        :alt="seller.business_name"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    >
                                    <div v-else class="w-full h-full bg-gradient-to-br from-orange-50 to-[var(--kun-color-cream)]"></div>
                                </div>

                                {{-- Circular Logo overlapping banner --}}
                                <div class="absolute top-[143px] left-6 w-[88px] h-[88px] rounded-full bg-[var(--kun-color-cream)] border-4 border-[var(--kun-color-cream)] overflow-hidden flex items-center justify-center z-[1]">
                                    <img
                                        v-if="seller.logo_url"
                                        :src="seller.logo_url"
                                        :alt="seller.business_name + ' logo'"
                                        class="max-w-[55px] max-h-[22px] object-contain"
                                    >
                                    <span
                                        v-else
                                        class="font-poppins text-lg font-bold text-[var(--kun-color-terracotta)]"
                                    >
                                        @{{ seller.business_name ? seller.business_name.substring(0, 2).toUpperCase() : '' }}
                                    </span>
                                </div>

                                {{-- Vendor Info --}}
                                <div class="pt-14 px-6">
                                    <h3 class="font-poppins text-2xl font-medium leading-8 text-slate-950 m-0">
                                        @{{ seller.business_name }}
                                    </h3>
                                    <p class="text-base font-normal leading-6 text-slate-500 mt-1 mb-0">
                                        @{{ seller.category || seller.full_address || 'Artisan Crafts' }}
                                    </p>
                                </div>

                                {{-- Stats: Items & Sales --}}
                                <div class="flex items-center gap-4 pt-4 px-6">
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-poppins text-base font-medium leading-6 text-[var(--kun-color-terracotta)]">
                                            @{{ seller.total_products ? formatCount(seller.total_products) : '0' }}
                                        </span>
                                        <span class="font-poppins text-xs font-normal leading-[18px] text-orange-300">
                                            Items
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-poppins text-base font-medium leading-6 text-[var(--kun-color-terracotta)]">
                                            @{{ seller.total_sales ? formatCount(seller.total_sales) : '0' }}
                                        </span>
                                        <span class="font-poppins text-xs font-normal leading-[18px] text-orange-300">
                                            Sales
                                        </span>
                                    </div>
                                </div>

                                {{-- Star Rating --}}
                                <div class="flex items-center gap-1 pt-2 px-6">
                                    <template v-for="star in 5">
                                        <svg width="24" height="24" viewBox="0 0 16 16"
                                             :fill="star <= Math.round(seller.avg_rating || 0) ? '#F59E0B' : '#D9CCA9'">
                                            <path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/>
                                        </svg>
                                    </template>
                                    <span class="text-slate-900 text-base leading-6 ml-0.5">@{{ seller.avg_rating || '0' }}</span>
                                    <span class="text-slate-900 text-xs leading-5">(@{{ formatCount(seller.total_reviews || 0) }})</span>
                                </div>

                                {{-- Visit Shop Button --}}
                                <div class="pt-4 px-6">
                                    <a
                                        :href="'{{ url('/marketplace') }}/' + seller.slug"
                                        class="kun-vendor-btn"
                                    >
                                        @lang('kun-theme::app.home.visit-shop')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Arrow --}}
                    <button
                        class="kun-nav-arrow kun-nav-arrow--right"
                        aria-label="@lang('shop::app.components.products.carousel.next')"
                        @click="scrollTrack(1)"
                        v-if="canScrollRight"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Dot Pagination --}}
                <div class="flex items-center justify-center gap-1 mt-6" v-if="sellers.length > 3">
                    <div class="w-8 h-1.5 rounded-lg bg-[var(--kun-color-terracotta)]"></div>
                    <div class="w-5 h-1.5 rounded-lg bg-orange-50" v-for="n in Math.ceil(sellers.length / 3) - 1" :key="n"></div>
                </div>
            </section>
        </div>
    </script>

    <script type="module">
        app.component('v-kun-vendor-carousel', {
            template: '#v-kun-vendor-carousel-template',

            props: ['title'],

            data() {
                return {
                    isLoading: true,
                    sellers: [],
                    canScrollLeft: false,
                    canScrollRight: false,
                };
            },

            mounted() {
                this.fetchSellers();
            },

            methods: {
                formatCount(num) {
                    if (! num) return '0';
                    num = parseInt(num);
                    if (num >= 1000) return (num / 1000).toFixed(num >= 10000 ? 0 : 1).replace(/\.0$/, '') + 'K';
                    return num.toLocaleString();
                },

                fetchSellers() {
                    this.$axios.get('{{ route("kun.api.featured_sellers") }}')
                        .then(response => {
                            this.sellers = response.data || [];
                            this.isLoading = false;

                            this.$nextTick(() => this.updateScrollState());
                        })
                        .catch(error => {
                            console.error(error);
                            this.isLoading = false;
                        });
                },

                scrollTrack(direction) {
                    const track = this.$refs.track;
                    if (! track) return;
                    track.scrollBy({ left: direction * 441, behavior: 'smooth' });
                },

                updateScrollState() {
                    const track = this.$refs.track;
                    if (! track) return;
                    this.canScrollLeft = track.scrollLeft > 0;
                    this.canScrollRight = track.scrollLeft + track.clientWidth < track.scrollWidth - 1;
                },
            },
        });
    </script>
@endPushOnce
