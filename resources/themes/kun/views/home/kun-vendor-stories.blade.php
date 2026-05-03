@php
    $title = $options['title'] ?? __('kun-theme::app.home.our-premium-shops');
    $subtitle = $options['subtitle'] ?? __('kun-theme::app.home.featured-vendors');
@endphp

<v-kun-vendor-stories
    title="{{ $title }}"
    subtitle="{{ $subtitle }}"
>
    <section class="kun-section" aria-label="{{ $title }}">
        <div class="shimmer rounded-2xl h-[500px]"></div>
    </section>
</v-kun-vendor-stories>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-kun-vendor-stories-template"
    >
        <div>
            <!-- Shimmer -->
            <section class="kun-section" v-if="isLoading">
                <div class="shimmer rounded-2xl h-[500px]"></div>
            </section>

            <!-- Stories -->
            <section class="kun-section kun-reveal kun-content-enter" :aria-label="title" v-else-if="sellers.length">
                {{-- Section Header --}}
                <div class="kun-section-header">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2.5">
                            <div class="kun-accent-line"></div>
                            <span class="kun-subtitle-text">@{{ subtitle }}</span>
                        </div>
                        <h2 class="kun-section-title">
                            @{{ title }}
                        </h2>
                    </div>

                    <a
                        href="{{ route('shop.marketplace.index') }}"
                        class="kun-link-accent"
                    >
                        @lang('kun-theme::app.home.view-all-stories')
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Full-Width Card with Arrows --}}
                <div class="relative">
                    {{-- Left Arrow --}}
                    <button
                        class="kun-nav-arrow kun-nav-arrow--left"
                        aria-label="@lang('shop::app.components.products.carousel.previous')"
                        @click="prevStory"
                        v-if="sellers.length > 1"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="rotate-180">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    {{-- Card --}}
                    <div class="kun-stories-card">
                        {{-- Background Image --}}
                        <img
                            v-if="currentSeller.banner_url"
                            :src="currentSeller.banner_url"
                            :alt="currentSeller.business_name"
                            class="absolute inset-0 w-full h-full object-cover"
                        >
                        <div v-else class="absolute inset-0 bg-gradient-to-br from-[#2d6b5a] to-[#1a4a3a]"></div>

                        {{-- Dark gradient overlay --}}
                        <div class="absolute inset-0 kun-gradient-bottom"></div>

                        {{-- Tag Pills --}}
                        <div class="kun-stories-tags">
                            <span class="kun-tag kun-tag--new opacity-80">
                                Verified&#8209;vendor
                            </span>
                            <span class="kun-tag kun-tag--origin opacity-80">
                                Origin&#8209;verified
                            </span>
                            <span
                                v-if="currentSeller.country"
                                class="kun-tag kun-tag--brand"
                            >
                                @{{ currentSeller.country }}
                            </span>
                            <span
                                v-else
                                class="kun-tag kun-tag--brand"
                            >
                                UAE
                            </span>
                        </div>

                        {{-- Text Content --}}
                        <div class="kun-stories-text">
                            <h3 class="font-poppins kun-stories-name">
                                @{{ currentSeller.business_name }}
                            </h3>
                            <p class="kun-stories-desc">
                                @{{ currentSeller.description || 'Discover 175 rare, hand-picked artifacts from Dubai\'s hidden tombs. Own a piece of history. Limited stock! Lorem ipsum is simply dummy text of the printing and typesetting industry.' }}
                            </p>
                        </div>

                        {{-- Vendor Logo --}}
                        <div class="kun-stories-logo">
                            <img
                                v-if="currentSeller.logo_url"
                                :src="currentSeller.logo_url"
                                :alt="(currentSeller.business_name || '') + ' logo'"
                                class="max-w-[95px] max-h-[38px] object-contain"
                            >
                            <span
                                v-else
                                class="font-poppins text-2xl font-bold text-[#B95B27]"
                            >
                                @{{ currentSeller.business_name ? currentSeller.business_name.substring(0, 2).toUpperCase() : '' }}
                            </span>
                        </div>
                    </div>

                    {{-- Right Arrow --}}
                    <button
                        class="kun-nav-arrow kun-nav-arrow--right"
                        aria-label="@lang('shop::app.components.products.carousel.next')"
                        @click="nextStory"
                        v-if="sellers.length > 1"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 12h16.83" stroke="#0F172B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Dot Pagination --}}
                <div class="flex items-center justify-center gap-1 mt-6" v-if="sellers.length > 1">
                    <button
                        v-for="(seller, idx) in sellers"
                        :key="idx"
                        @click="currentIndex = idx"
                        class="kun-dot h-1.5 rounded-lg border-none p-0 cursor-pointer transition-all duration-300"
                        :class="idx === currentIndex ? 'w-8 bg-[#B95B27]' : 'w-5 bg-[#f8efe9]'"
                    ></button>
                </div>
            </section>
        </div>
    </script>

    <script type="module">
        app.component('v-kun-vendor-stories', {
            template: '#v-kun-vendor-stories-template',

            props: ['title', 'subtitle'],

            data() {
                return {
                    isLoading: true,
                    sellers: [],
                    currentIndex: 0,
                };
            },

            computed: {
                currentSeller() {
                    return this.sellers[this.currentIndex] || {};
                },
            },

            mounted() {
                this.fetchSellers();
            },

            methods: {
                fetchSellers() {
                    this.$axios.get('{{ route("kun.api.featured_sellers") }}')
                        .then(response => {
                            this.sellers = response.data || [];
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error(error);
                            this.isLoading = false;
                        });
                },

                prevStory() {
                    this.currentIndex = (this.currentIndex - 1 + this.sellers.length) % this.sellers.length;
                },

                nextStory() {
                    this.currentIndex = (this.currentIndex + 1) % this.sellers.length;
                },
            },
        });
    </script>
@endPushOnce
