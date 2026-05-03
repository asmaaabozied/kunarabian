@php
    use Webkul\Marketplace\Repositories\ReviewRepository;

    $reviewRepository = app(ReviewRepository::class);

    $reviews = $reviewRepository->getRecentReviews($seller->id);

    $avgRatings = $reviewRepository->getAverageRating($seller);

    $totalReviews = $reviewRepository->getTotalReviews($seller);

    $percentageRatings = $reviewRepository->getPercentageRating($seller);
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="title"
        content="{{ $seller->meta_title ?? '' }}"
    />

    <meta
        name="description"
        content="{{ trim($seller->meta_description) != ''
        ? $seller->meta_description
        : Illuminate\Support\Str::limit(strip_tags($seller->business_description), 120, '') }}"
    />

    <meta
        name="keywords"
        content="{{ $seller->meta_keywords }}"
    />

    @if ($seller->google_analytics_id)
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seller->google_analytics_id }}"></script>

        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());

            gtag('config', '{{ $seller->google_analytics_id }}');
        </script>
    @endif

    <!-- Organization JSON-LD -->
    <script type="application/ld+json">
        {!! app('Webkul\Product\Helpers\SEO')->getSellerJsonLd($seller) !!}
    </script>
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $seller->business_name }}
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
        <div class="mt-8 grid gap-8">
            <!-- Banner -->
            <div class="max-sm:h-[250px] h-[300px]">
                <x-shop::media.images.lazy
                    :src="$seller->banner_url ?: bagisto_asset('images/default-banner.webp', 'marketplace')"
                    class="max-sm:h-[250px] w-full object-cover h-[300px] rounded-lg"
                    alt="marketplace banner"
                    width="1320"
                    height="300"
                >
                </x-shop::media.images.lazy>
            </div>
        
            <div class="grid gap-5">
                <div class="max-sm:grid max-sm:gap-y-1.5 flex md:items-center md:justify-between">
                    <div class="max-sm:grid gap-2.5 flex">
                        <div class="h-20 w-20 rounded-xl border">
                            <x-shop::media.images.lazy
                                :src="$seller->logo_url ?: bagisto_asset('images/default-logo.webp', 'marketplace')"
                                class="h-20 w-20 rounded-xl object-cover"
                                alt="seller logo"
                                width="80"
                                height="80"
                            >
                            </x-shop::media.images.lazy>
                        </div>
    
                        <div class="grid max-sm:gap-y-1.5 md:max-w-[500px]">
                            <div class="flex gap-1.5">
                                <h1 class="text-3xl font-medium">
                                    {{ $seller->business_name }}
                                </h1>

                                @if ($seller->showRedFlag())
                                    <span
                                        class="mp-flag-icon text-2xl text-red-600 cursor-pointer"
                                        title="{{ $seller->flags_count }}"
                                    ></span>
                                @endif
                            </div>

                            <h6 class="text-base font-medium text-[#757575]">
                                {{ $seller->full_address }}
                            </h6>
                        </div>
                    </div>
    
                    <div class="grid gap-1.5">
                        <div class="flex items-center gap-2.5 md:justify-end">
                            <x-marketplace::shop.products.star-rating 
                                :value="$avgRatings"
                                :is-editable=false
                            />
            
                            <div class="flex gap-4">
                                <p class="text-sm leading-8 text-[#757575]">
                                    ({{ $totalReviews }} @lang('reviews'))
                                </p>
                            </div>
                        </div>
    
                        @include('marketplace::shop.sellers.contacts')
                    </div>
                </div>
    
                <!-- seller Profile component -->
                <v-seller-profile>
                    <x-marketplace::shop.shimmer.profile-tab />
    
                    <x-marketplace::shop.shimmer.product />
                </v-seller-profile>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-seller-profile-template"
        >
            <div class="max-sm:grid justify-between gap-y-4 rounded-2xl flex md:border md:p-5">
                <div class="flex gap-5">
                    {!! view_render_event('bagisto.shop.marketplace.seller.profile.tabs.before', ['seller' => $seller]) !!}
                    
                    <button
                        class="rounded-xl max-sm:px-2 py-2 text-lg font-normal leading-7 px-4"
                        :class="{ 'border border-navyBlue': activeTab == 'products' }"
                        @click="activeTab = 'products'"
                    >
                        @{{ "@lang('marketplace::app.shop.sellers.profile.products-count')".replace(':count', totalProductCount) }}
                    </button>
    
                    <button
                        class="rounded-xl max-sm:px-2 py-2 text-lg font-normal leading-7 px-4"
                        :class="{ 'border border-navyBlue': activeTab == 'reviews' }"
                        @click="activeTab = 'reviews'"
                    >
                        @lang('marketplace::app.shop.sellers.profile.reviews-count', ['count' => $totalReviews])
                    </button>
    
                    <button
                        class="rounded-xl max-sm:px-2 py-2 text-lg font-normal leading-7 px-4"
                        :class="{ 'border border-navyBlue': activeTab == 'about' }"
                        @click="activeTab = 'about'"
                    >
                        @lang('marketplace::app.shop.sellers.profile.about.title')
                    </button>

                    {!! view_render_event('bagisto.shop.marketplace.seller.profile.tabs.after', ['seller' => $seller]) !!}
                </div>
    
                <div class="flex">
                    <!-- Serach Product Form -->
                    <form
                        v-if="activeTab == 'products'"
                        action="{{ route('shop.marketplace.sellers.show', $seller->slug) }}"
                        class="flex w-full items-center"
                    >
                        <label
                            for="organic-search"
                            class="sr-only"
                        >
                            @lang('marketplace::app.shop.sellers.profile.reviews.search')
                        </label>
    
                        <div class="relative w-full">
                            <div
                                class="icon-search pointer-events-none absolute top-2.5 flex items-center text-2xl ltr:left-3 rtl:right-3">
                            </div>
    
                            <input
                                type="text"
                                class="block w-full rounded-xl border border-navyBlue px-11 py-3.5 text-xs font-medium"
                                name="term"
                                v-model="searchTerm"
                                placeholder="@lang('marketplace::app.shop.sellers.profile.search-text')"
                            >
                        </div>
                    </form>
                </div>
            </div>

            <template v-if="activeTab == 'products'">
                <v-seller-products
                    :search-term="searchTerm"
                    v-model="totalProductCount"
                >
                    <x-marketplace::shop.shimmer.product />
                </v-seller-products>
            </template>

            <template v-if="activeTab == 'reviews'">
                <div class="mt-3 max-sm:grid justify-between gap-3 flex">
                    <h2 class="text-2xl font-medium">
                        @lang('marketplace::app.shop.sellers.profile.reviews.customer-reviews')
                    </h2>
                    
                    <span
                        class="icon-pen flex cursor-pointer items-center gap-2 rounded-xl border border-navyBlue px-4 py-2 text-2xl"
                        @click="openReviewModal"
                    >
                        <span class="text-lg font-normal leading-8">
                            @lang('marketplace::app.shop.sellers.profile.reviews.write-review')
                        </span>
                    </span>
                </div>

                <div class="mt-8 max-sm:grid gap-y-8 flex gap-x-8">
                    <div class="grid content-baseline">
                        <div class="grid gap-2">
                            <div class="mt-2.5 flex items-center gap-4">
                                <div class="flex gap-4">
                                    <p class="text-3xl font-medium text-[#232323]">
                                        {{$avgRatings}}
                                    </p>
                                </div>

                                <x-marketplace::shop.products.star-rating 
                                    :value="$avgRatings"
                                    :is-editable=false
                                />
                
                                <div class="flex gap-4">
                                    <p class="text-xs text-[#858585] underline">
                                        (@lang('marketplace::app.shop.sellers.profile.reviews.customer-review', ['count' => $totalReviews]))
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-y-4 max-w-[365px]">
                            @for ($i = 5; $i >= 1; $i--)
                                <div class="flex items-center justify-between gap-x-6 max-sm:flex-wrap">
                                    <div class="whitespace-nowrap text-base font-medium">{{ $i }} Stars</div>
                                    <div class="h-4 w-[275px] max-w-full rounded-sm bg-[#E5E5E5]">
                                        <div class="h-4 rounded-sm bg-[#FEA82B]" style="width: {{ $percentageRatings[$i] }}%"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="flex flex-col gap-5 w-full">
                        @foreach ($reviews as $review)
                            <div class="flex gap-5 rounded-xl border border-[#e5e5e5] p-6 max-sm:flex-col">
                                <div class="max-sm:hidden">
                                    <div class="flex h-[100px] w-[100px] items-center justify-center rounded-xl bg-[#F5F5F5] max-sm:mx-auto">
                                        @if($review->customer->image_url)
                                            <img 
                                                src="{{ $review->customer->image_url }}" 
                                                alt="{{ $review->customer->name }}" 
                                                class="h-full w-full object-cover rounded-xl"
                                            />
                                        @else
                                            @php
                                                $splitName = explode(' ', $review->customer->name);
                                                $uppercaseNames = array_map(fn($name) => strtoupper($name[0]), $splitName);
                                                $joinedName = implode('', $uppercaseNames);
                                            @endphp
                                        
                                            <span class="text-2xl font-semibold text-[#6E6E6E]">
                                                {{ $joinedName }}
                                            </span>
                                        @endif
                                    </div>
                                </div>                                

                                <div class="flex flex-col w-full">
                                    <div class="flex justify-between items-center flex-wrap">
                                        <p class="text-xl font-medium max-sm:text-base">
                                            {{$review->title}}
                                        </p>

                                        <x-marketplace::shop.products.star-rating 
                                            :value="$review->rating"
                                        />
                                    </div>                    

                                    <p class="mt-2 text-base text-[#757575] max-sm:text-sm">
                                        {{$review->comment}}
                                    </p>

                                    <p class="mt-2 text-sm text-[#666666] max-sm:text-xs">
                                        @lang('marketplace::app.shop.sellers.profile.reviews.review-by') <span class="font-medium">
                                            {{$review->customer->name}}
                                        </span>
                                        {{core()->formatDate($review->created_at, 'd M, Y')}}
                                    </p>
                                </div>
                            </div>

                            @if ($loop->count >= 5)
                                <a
                                    href="{{ route('shop.marketplace.seller.reviews.index', $seller->slug) }}"
                                    class="secondary-button mx-auto block w-max rounded-xl px-11 py-2.5 text-center text-base"
                                >
                                    @lang('marketplace::app.shop.sellers.profile.reviews.view-all')
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, createReview)"
                        ref="reviewForm"
                    >
                        <!-- Seller Review Modal -->
                        <x-shop::modal ref="reviewModal">
                            <x-slot:header>
                                <!-- Modal Header -->
                                <h2 class="text-2xl">
                                    @lang('marketplace::app.shop.sellers.profile.reviews.write-review')
                                </h2>
                            </x-slot:header>

                            <x-slot:content>
                                <x-shop::form.control-group.control
                                    type="hidden"
                                    name="slug"
                                    value="{{ $seller->slug }}"
                                />

                                {!! view_render_event('bagisto.shop.marketplace.seller.profile.review.create_form_controls.before', ['seller' => $seller]) !!}
                                
                                <x-shop::form.control-group class="w-full">
                                    <x-shop::form.control-group.label class="required flex">
                                        @lang('shop::app.products.view.reviews.rating')
                                    </x-shop::form.control-group.label>

                                    <x-marketplace::shop.products.star-rating
                                        name="rating"
                                        :value="old('rating') ?? 5"
                                        :disabled="false"
                                        rules="required"
                                        :label="trans('shop::app.products.view.reviews.rating')"
                                    />

                                    <x-shop::form.control-group.error
                                        control-name="rating"
                                        class="flex"
                                    >
                                    </x-shop::form.control-group.error>
                                </x-shop::form.control-group>

                                <x-shop::form.control-group class="w-full">
                                    <x-shop::form.control-group.label class="required flex">
                                        @lang('marketplace::app.shop.sellers.profile.reviews.title')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        name="title"
                                        class="! shadow-none"
                                        rules="required"
                                        :label="trans('marketplace::app.shop.sellers.profile.reviews.title')"
                                        :placeholder="trans('marketplace::app.shop.sellers.profile.reviews.title')"
                                    />

                                    <x-shop::form.control-group.error
                                        control-name="title"
                                        class="flex"
                                    />
                                </x-shop::form.control-group>

                                <x-shop::form.control-group class="w-full">
                                    <x-shop::form.control-group.label class="required flex">
                                        @lang('marketplace::app.shop.sellers.profile.reviews.comment')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="textarea"
                                        name="comment"
                                        class="mb-0 !shadow-none"
                                        rows="4"
                                        rules="required"
                                        :label="trans('marketplace::app.shop.sellers.profile.reviews.comment')"
                                        :placeholder="trans('marketplace::app.shop.sellers.profile.reviews.comment')"
                                    />

                                    <x-shop::form.control-group.error
                                        control-name="comment"
                                        class="flex"
                                    />
                                </x-shop::form.control-group>

                                {!! view_render_event('bagisto.shop.marketplace.seller.profile.review.create_form_controls.after', ['seller' => $seller]) !!}
                            </x-slot:content>

                            <x-slot:footer>
                                <x-shop::button
                                    type="submit"
                                    class="primary-button place-self-end"
                                    ::disabled="isSubmitting"
                                    ::loading="isSubmitting"
                                    :title="trans('marketplace::app.shop.sellers.profile.reviews.submit-btn')"
                                />
                            </x-slot:footer>
                        </x-shop::modal>
                    </form>
                </x-shop::form>
            </template>

            <template v-if="activeTab == 'about'">
                @include('marketplace::shop.sellers.about')
            </template>

            {!! view_render_event('bagisto.shop.marketplace.seller.profile.tab.content', ['seller' => $seller]) !!}
        </script>

        <script type="module">
            app.component('v-seller-profile', {
                template: '#v-seller-profile-template',

                data() {
                    return {
                        activeTab: 'products',

                        searchTerm: '',

                        totalProductCount: 0,

                        customer: @json(auth()->guard('customer')->user()),

                        isSubmitting: false,
                    }
                },

                methods: {
                    createReview(params, { resetForm, setErrors }) {
                        this.isSubmitting = true;

                        this.$axios.post("{{route('shop.marketplace.seller.reviews.store')}}", params)
                            .then(response => {
                                this.$refs.reviewForm.reset();
                                
                                this.$refs.reviewModal.close();

                                this.$emitter.emit('add-flash', {
                                    type: response.data.type,
                                    message: response.data.message
                                });
                            })
                            .catch(error => {
                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            })
                            .finally(() => {
                                this.isSubmitting = false;
                            });
                    },

                    openReviewModal() {
                        if (! this.customer) {
                            this.$emitter.emit('open-confirm-modal', {
                                title: "@lang('marketplace::app.shop.sellers.profile.reviews.alert')",
                                message: "@lang('marketplace::app.shop.sellers.profile.reviews.guest-alert')",
                                agree: () => {
                                    window.location.href = "{{ route('shop.customer.session.index') }}";
                                }
                            });

                            return;
                        }

                        this.$refs.reviewModal.open();
                    },
                }
            });
        </script>

        <script 
            type="text/x-template" 
            id="v-seller-products-template"
        >
            <div>
                <div class="flex items-start gap-10 max-lg:gap-5 mt-4">
                    <!-- Product Listing Filters -->
                    @include('marketplace::shop.sellers.products.filters')

                    <!-- Product Listing Container -->
                    <div class="flex-1">
                        <!-- Desktop Product Listing Toolbar -->
                        <div class="max-md:hidden">
                            @include('marketplace::shop.sellers.products.toolbar')
                        </div>

                        <!-- Product List Card Container -->
                        <div
                            class="mt-8 grid grid-cols-1 gap-6"
                            v-if="filters.toolbar.mode === 'list'"
                        >
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <x-shop::shimmer.products.cards.list count="9" />
                            </template>

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <x-marketplace::shop.products.card
                                        ::mode="'list'"
                                        v-for="product in products"
                                    >
                                    </x-marketplace::shop.products.card>
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid h-[476px] w-[100%] place-content-center items-center justify-items-center text-center">
                                        <img src="{{ bagisto_asset('images/thank-you.png') }}"/>
                                
                                        <p class="text-xl">
                                            @lang('marketplace::app.shop.sellers.profile.products.no-result')
                                        </p>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- Product Grid Card Container -->
                        <div v-else>
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <div class="mt-8 grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-sm:mt-5 max-sm:justify-items-center max-sm:gap-4">
                                    <x-shop::shimmer.products.cards.grid count="9" />
                                </div>
                            </template>

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <div class="mt-8 grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-sm:mt-5 max-sm:justify-items-center max-sm:gap-4">
                                        <x-marketplace::shop.products.card
                                            ::mode="'grid'"
                                            v-for="product in products"
                                        >
                                        </x-marketplace::shop.products.card>
                                    </div>
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid h-[476px] w-[100%] place-content-center items-center justify-items-center text-center">
                                        <img src="{{ bagisto_asset('images/thank-you.png') }}"/>
                                        
                                        <p class="text-xl">
                                            @lang('marketplace::app.shop.sellers.profile.products.no-result')
                                        </p>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- Load More Button -->
                        <button
                            class="secondary-button mx-auto mt-15 block w-max rounded-2xl px-11 py-2.5 text-center text-base"
                            @click="loadMoreProducts"
                            v-if="links.next"
                        >
                            @lang('shop::app.categories.view.load-more')
                        </button>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-seller-products', {
                template: '#v-seller-products-template',

                props: {
                    searchTerm: {
                        type: String,
                        default: ''
                    }
                },

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,

                        isLoading: true,

                        isDrawerActive: {
                            toolbar: false,
                            filter: false,
                        },

                        filters: {
                            toolbar: {},
                            filter: {},
                        },

                        products: [],

                        links: {},
                    };
                },

                computed: {
                    queryParams() {
                        let queryParams = Object.assign(
                            {}, 
                            this.filters.filter, 
                            this.filters.toolbar, 
                            { query: this.searchTerm }
                        );

                        return this.removeJsonEmptyValues(queryParams);
                    },
                },

                watch: {
                    queryParams() {
                        this.getProducts();
                    },
                },

                methods: {
                    setFilters(type, filters) {
                        this.filters[type] = filters;

                        document.body.style.overflow = 'auto';
                    },

                    clearFilters(type) {
                        this.filters[type] = {};

                        document.body.style.overflow = 'auto';
                    },

                    getProducts() {
                        this.isDrawerActive = {
                            toolbar: false,
                            filter: false,
                        };

                        this.$axios.get("{{ route('shop.marketplace.sellers.products', $seller->slug) }}", {
                            params: this.queryParams,
                        })
                        .then(response => {
                            this.isLoading = false;

                            this.products = response.data.data;

                            this.links = response.data.links;                            

                            this.$emit('update:modelValue', response.data.meta.total);
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    },

                    loadMoreProducts() {
                        if (this.links.next) {
                            this.$axios.get(this.links.next).then(response => {
                                this.products = [...this.products, ...response.data.data];

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                        }
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(key => {
                            if (
                                ! params[key]
                                && params[key] !== undefined
                            ) {
                                delete params[key];
                            }

                            if (Array.isArray(params[key])) {
                                params[key] = params[key].join(',');
                            }
                        });

                        return params;
                    },
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>