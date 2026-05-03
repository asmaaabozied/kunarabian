@php
    $title = $options['title'] ?? '';
    $filters = $options['filters'] ?? [];
    $carouselId = 'kun-categories-' . $customization->id;
@endphp

<v-kun-category-carousel src="{{ route('shop.api.categories.tree') }}"
    flat-src="{{ route('shop.api.categories.index', array_merge($filters, ['limit' => 100])) }}"
    title="{{ $title }}" carousel-id="{{ $carouselId }}">
    <section class="kun-section" aria-label="{{ $title ?: $customization->name }}">
        <div class="flex gap-4 overflow-hidden">
            @for ($i = 0; $i < 6; $i++)
                <div class="flex-shrink-0 rounded-[14px] shimmer w-[170px] h-[130px]"></div>
            @endfor
        </div>
    </section>
</v-kun-category-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-kun-category-carousel-template"
    >
        <div>
            <!-- Shimmer while loading -->
            <section class="kun-section" v-if="isLoading">
                <div class="flex gap-4 overflow-hidden">
                    <div class="flex-shrink-0 rounded-[14px] shimmer w-[170px] h-[130px]" v-for="n in 6" :key="n"></div>
                </div>
            </section>

            <!-- Categories -->
            <section class="kun-section relative kun-reveal kun-content-enter" :aria-label="title || 'Categories'" v-else-if="categories.length">
                <button
                    @click="scrollTrack(-1)"
                    v-if="canScrollLeft"
                    class="absolute left-[52px] top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow flex items-center justify-center hover:bg-gray-50 transition-all"
                    aria-label="Previous categories"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M10 12L6 8l4-4" stroke="#1a1a1a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div
                    :id="carouselId + '-track'"
                    class="flex gap-4 overflow-x-auto scroll-smooth no-scrollbar"
                    ref="track"
                    @scroll="updateScrollState"
                >
                    <a
                        v-for="category in categories"
                        :key="category.id"
                        :href="category.slug || category.url"
                        class="kun-category-card group"
                        :aria-label="category.name"
                    >
                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="category.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 group-hover:bg-gray-300 transition-colors duration-300">
                            <span class="text-2xl font-semibold text-gray-500">@{{ category.name?.charAt(0) }}</span>
                        </div>
                        <div class="absolute inset-0 kun-gradient-bottom"></div>
                        <span class="absolute bottom-2.5 left-3 right-3 text-center text-gray-50 font-poppins text-sm font-medium leading-5">
                            @{{ category.name }}
                        </span>
                    </a>
                </div>

                <button
                    @click="scrollTrack(1)"
                    v-if="canScrollRight"
                    class="absolute right-[52px] top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow flex items-center justify-center hover:bg-gray-50 transition-all"
                    aria-label="Next categories"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M6 4l4 4-4 4" stroke="#1a1a1a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </section>
        </div>
    </script>

    <script type="module">
        app.component('v-kun-category-carousel', {
            template: '#v-kun-category-carousel-template',

            props: ['src', 'flatSrc', 'title', 'carouselId'],

            data() {
                return {
                    isLoading: true,
                    categories: [],
                    canScrollLeft: false,
                    canScrollRight: false,
                };
            },

            mounted() {
                this.fetchCategories();
            },

            methods: {
                fetchCategories() {
                    // Fetch both tree (for structure) and flat (for images) in parallel
                    Promise.all([
                            this.$axios.get(this.src),
                            this.$axios.get(this.flatSrc),
                        ])
                        .then(([treeResponse, flatResponse]) => {
                            const tree = treeResponse.data.data || treeResponse.data || [];
                            const flatCats = flatResponse.data.data || flatResponse.data || [];

                            // Build image lookup from flat API (has logo/banner)
                            const imageMap = {};
                            flatCats.forEach(cat => {
                                const logo = cat.logo;
                                const banner = cat.banner;
                                imageMap[cat.id] = (logo && (logo.large_image_url || logo
                                        .medium_image_url || logo.original_image_url)) ||
                                    (banner && (banner.large_image_url || banner.medium_image_url ||
                                        banner.original_image_url)) ||
                                    null;
                            });

                            // Flatten tree: all categories + their children
                            const flattened = [];
                            tree.forEach(cat => {
                                flattened.push({
                                    id: cat.id,
                                    name: cat.name,
                                    slug: cat.slug || cat.url,
                                    image: imageMap[cat.id] || null,
                                });

                                if (cat.children && cat.children.length) {
                                    cat.children.forEach(child => {
                                        flattened.push({
                                            id: child.id,
                                            name: child.name,
                                            slug: child.slug || child.url,
                                            image: imageMap[child.id] || null,
                                        });
                                    });
                                }
                            });

                            this.categories = flattened;
                            this.isLoading = false;

                            this.$nextTick(() => this.updateScrollState());
                        })
                        .catch(error => {
                            console.log(error);
                            this.isLoading = false;
                        });
                },

                scrollTrack(direction) {
                    const track = this.$refs.track;
                    if (track) {
                        track.scrollBy({
                            left: direction * 380,
                            behavior: 'smooth'
                        });
                    }
                },

                updateScrollState() {
                    const track = this.$refs.track;
                    if (!track) return;

                    this.canScrollLeft = track.scrollLeft > 0;
                    this.canScrollRight = track.scrollLeft + track.clientWidth < track.scrollWidth - 1;
                },
            },
        });
    </script>

@endPushOnce
