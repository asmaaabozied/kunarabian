@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

@php
    $channel = core()->getCurrentChannel();
    $locales = $channel->locales()->orderBy('name')->get();
    $currentLocale = app()->getLocale();
    $showCompare = (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showWishlist = (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');
    $showCart = (bool) core()->getConfigData('sales.checkout.shopping_cart.cart_page');

    $headerOffer = core()->getConfigData('general.content.header_offer.title');
    $headerOfferLink = core()->getConfigData('general.content.header_offer.redirection_link');
    $headerOfferTitle = core()->getConfigData('general.content.header_offer.redirection_title');

    try {
        $cartCount = \Webkul\Checkout\Facades\Cart::getCart()?->items_count ?? 0;
    } catch (\Exception $e) {
        $cartCount = 0;
    }
@endphp

{{-- Top Bar --}}
@if ($locales->count() > 1 || $headerOffer)
    <div class="w-full bg-slate-950 h-12 text-xs flex items-center justify-between px-[106px] max-lg:px-8 max-sm:px-4 border-b">
        {{-- Locale Switcher --}}
        @if ($locales->count() > 1)
            <v-kun-locale-switcher></v-kun-locale-switcher>
        @else
            <div></div>
        @endif

        {{-- Header Offer --}}
        @if ($headerOffer)
            <div class="flex items-center gap-2 flex-1 justify-center px-4">
                @if ($headerOfferLink)
                    <a href="{{ $headerOfferLink }}" class="whitespace-nowrap text-gray-50 hover:underline">
                        {{ $headerOffer }}
                        @if ($headerOfferTitle)
                            <span class="font-medium">{{ $headerOfferTitle }}</span>
                        @endif
                    </a>
                @else
                    <span class="whitespace-nowrap text-gray-50">{{ $headerOffer }}</span>
                @endif
            </div>
        @endif

        <div></div>
    </div>
@endif

{{-- Main Header --}}
<header class="w-full h-20 bg-gray-50 border-b border-brand-color-01-200 sticky top-0 z-50 transition-shadow duration-300" id="kun-main-header">
    <div class="flex items-center justify-between h-full px-[106px] max-lg:px-8 max-sm:px-4 gap-4">
        {{-- Logo --}}
        <a href="{{ route('shop.home.index') }}"
           class="h-10 w-16 flex items-center justify-center flex-shrink-0"
           aria-label="{{ $channel->name }}">
            <img
                src="{{ $channel->logo_url ?? asset('images/kun_logo.svg') }}"
                alt="{{ $channel->name }}"
                class="max-w-full h-auto"
            >
        </a>

        {{-- Dynamic Category Navigation --}}
        <v-kun-header-nav></v-kun-header-nav>

        {{-- Search --}}
        <form action="{{ route('shop.search.index') }}" method="GET"
              class="hidden md:flex items-center bg-white border border-brand-color-01-200 rounded-full h-10 px-4 gap-2 w-56 flex-shrink-0"
              role="search">
            <input type="text"
                   name="query"
                   placeholder="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
                   value="{{ request('query') }}"
                   aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
                   class="bg-transparent border-none outline-none text-sm font-poppins text-slate-950 w-full placeholder:text-neutral-400">
            <button type="submit" aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search')"
                    class="bg-transparent border-none cursor-pointer p-0 text-slate-900 hover:opacity-75 transition-opacity flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
            </button>
        </form>

        {{-- Action Icons --}}
        <div class="flex items-center gap-2">
            @auth('customer')
                <a href="{{ route('shop.customers.account.profile.index') }}"
                   class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-color-01-1000 text-white hover:opacity-90 transition-opacity flex-shrink-0"
                   aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.profile')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('shop.customer.session.index') }}"
                   class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-color-01-1000 text-white hover:opacity-90 transition-opacity flex-shrink-0"
                   aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.sign-in')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            @endauth

            @if ($showCompare)
                <a href="{{ route('shop.compare.index') }}"
                   class="w-10 h-10 flex items-center justify-center rounded-full text-slate-950 hover:text-brand-color-01-1000 transition-colors relative flex-shrink-0"
                   aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.compare')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 3l4 4-4 4"/>
                        <path d="M20 7H4"/>
                        <path d="M8 21l-4-4 4-4"/>
                        <path d="M4 17h16"/>
                    </svg>
                </a>
            @endif

            @if ($showWishlist)
                <a href="{{ route('shop.customers.account.wishlist.index') }}"
                   class="w-10 h-10 flex items-center justify-center rounded-full text-slate-950 hover:text-brand-color-01-1000 transition-colors relative flex-shrink-0"
                   aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.wishlist')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                </a>
            @endif

            @if ($showCart)
                <a href="{{ route('shop.checkout.cart.index') }}"
                   class="w-10 h-10 flex items-center justify-center rounded-full text-slate-950 hover:text-brand-color-01-1000 transition-colors relative flex-shrink-0"
                   aria-label="@lang('kun-theme::app.layout.header.cart')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <path d="M3 6h18"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    @if ($cartCount > 0)
                        <span class="absolute top-0 right-0 min-w-[18px] h-[18px] bg-brand-color-01-1000 rounded-full text-[9px] text-white font-bold flex items-center justify-center px-0.5 leading-none">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            @endif
        </div>
    </div>
</header>

{{-- Dynamic Category Scroll --}}
<v-kun-category-scroll></v-kun-category-scroll>

{{-- Mobile Bottom Navigation --}}
<nav class="kun-mobile-nav" aria-label="Mobile navigation">
    <a href="{{ route('shop.home.index') }}"
       class="kun-mobile-nav__item {{ request()->routeIs('shop.home.index') ? 'kun-mobile-nav__item--active' : '' }}"
       aria-label="@lang('shop::app.components.layouts.header.desktop.top.home')">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>Home</span>
    </a>

    <a href="{{ route('shop.search.index') }}"
       class="kun-mobile-nav__item {{ request()->routeIs('shop.search.index') ? 'kun-mobile-nav__item--active' : '' }}"
       aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search')">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
        </svg>
        <span>Search</span>
    </a>

    @if ($showCart)
        <a href="{{ route('shop.checkout.cart.index') }}"
           class="kun-mobile-nav__item {{ request()->routeIs('shop.checkout.cart.index') ? 'kun-mobile-nav__item--active' : '' }}"
           aria-label="@lang('kun-theme::app.layout.header.cart')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <path d="M3 6h18"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            @if ($cartCount > 0)
                <span class="kun-mobile-nav__badge">{{ $cartCount }}</span>
            @endif
            <span>Cart</span>
        </a>
    @endif

    @auth('customer')
        <a href="{{ route('shop.customers.account.profile.index') }}"
           class="kun-mobile-nav__item"
           aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.profile')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Account</span>
        </a>
    @else
        <a href="{{ route('shop.customer.session.index') }}"
           class="kun-mobile-nav__item"
           aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.sign-in')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Sign In</span>
        </a>
    @endauth
</nav>

@pushOnce('scripts')
    <script type="text/x-template" id="v-kun-locale-switcher-template">
        <div class="flex items-center gap-3">
            @foreach ($locales as $locale)
                <a href="javascript:void(0)"
                   @click="change('{{ $locale->code }}')"
                   class="hover:text-gray-300 transition-colors {{ $currentLocale === $locale->code ? 'font-medium text-brand-color-03-1000' : 'font-regular opacity-75 text-gray-50' }}">
                    {{ $locale->name }}
                </a>
                @if (! $loop->last)
                    <span class="text-gray-300">|</span>
                @endif
            @endforeach
        </div>
    </script>

    <script type="text/x-template" id="v-kun-header-nav-template">
        <nav aria-label="Primary navigation" class="hidden lg:block flex-1" v-if="categories.length">
            <ul class="flex items-center list-none m-0 p-0">
                <li
                    v-for="category in categories"
                    :key="category.id"
                    class="kun-nav-item"
                    @mouseenter="openCategory = category.id"
                    @mouseleave="openCategory = null"
                >
                    <a :href="category.url || ('{{ url('/') }}/' + category.slug)"
                       class="px-4 py-2 text-sm font-medium text-slate-950 hover:text-brand-color-01-1000 transition-colors inline-flex items-center gap-1">
                        @{{ category.name }}
                        <svg v-if="category.children && category.children.length" width="12" height="12" viewBox="0 0 12 12" fill="none" class="opacity-50">
                            <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>

                    {{-- Dropdown --}}
                    <div
                        v-if="category.children && category.children.length && openCategory === category.id"
                        class="kun-dropdown"
                    >
                        <div class="kun-dropdown-grid">
                            <div v-for="child in category.children" :key="child.id" class="kun-dropdown-col">
                                <a :href="child.url || ('{{ url('/') }}/' + child.slug)"
                                   class="kun-dropdown-heading">
                                    @{{ child.name }}
                                </a>

                                <ul v-if="child.children && child.children.length" class="kun-dropdown-list">
                                    <li v-for="grandchild in child.children" :key="grandchild.id">
                                        <a :href="grandchild.url || ('{{ url('/') }}/' + grandchild.slug)"
                                           class="kun-dropdown-link">
                                            @{{ grandchild.name }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
        <nav v-else class="hidden lg:block flex-1">
            <ul class="flex items-center list-none m-0 p-0 gap-2">
                <li v-for="n in 4" :key="n">
                    <div class="shimmer w-20 h-6 rounded"></div>
                </li>
            </ul>
        </nav>
    </script>

    <script type="text/x-template" id="v-kun-category-scroll-template">
        <div class="w-full bg-gray-50 border-b border-brand-color-01-200 py-4 px-[106px] max-lg:px-8 max-sm:px-4 overflow-hidden" v-if="categories.length">
            <div
                ref="scrollContainer"
                class="flex items-center gap-3 overflow-x-auto no-scrollbar cursor-grab active:cursor-grabbing select-none"
                @mousedown="startDrag"
                @mousemove="onDrag"
                @mouseup="stopDrag"
                @mouseleave="stopDrag"
            >
                <a href="{{ route('shop.home.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-full text-sm font-poppins transition-all whitespace-nowrap border flex-shrink-0"
                   :class="!activeCategory ? 'bg-brand-color-02-1000 text-brand-color-03-300 border-transparent' : 'bg-brand-color-03-300 text-slate-950 border-transparent hover:border-brand-color-01-1000'"
                   @click.prevent="navigate($event, '{{ route('shop.home.index') }}')">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="w-4 h-4">
                        <path d="M2 4H14M2 8H14M2 12H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span>@lang('kun-theme::app.layout.header.all-categories')</span>
                </a>

                <a v-for="category in categories"
                   :key="category.id"
                   :href="'{{ url('/') }}/' + category.slug"
                   class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-full text-sm font-poppins transition-all whitespace-nowrap border flex-shrink-0"
                   :class="activeCategory == category.id ? 'bg-brand-color-02-1000 text-brand-color-03-300 border-transparent' : 'bg-brand-color-03-300 text-slate-950 border-transparent hover:border-brand-color-01-1000'"
                   @click.prevent="navigate($event, '{{ url('/') }}/' + category.slug)">
                    <span>@{{ category.name }}</span>
                </a>
            </div>
        </div>
        <div class="w-full bg-gray-50 border-b border-brand-color-01-200 py-4 px-[106px] max-lg:px-8 max-sm:px-4 overflow-hidden" v-else>
            <div class="flex items-center gap-3">
                <div class="shimmer w-24 h-9 rounded-full" v-for="n in 8" :key="n"></div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-kun-locale-switcher', {
            template: '#v-kun-locale-switcher-template',

            methods: {
                change(localeCode) {
                    let url = new URL(window.location.href);
                    url.searchParams.set('locale', localeCode);
                    window.location.href = url.href;
                },
            },
        });

        app.component('v-kun-header-nav', {
            template: '#v-kun-header-nav-template',

            data() {
                return {
                    categories: [],
                    openCategory: null,
                };
            },

            mounted() {
                this.fetchCategories();
            },

            methods: {
                fetchCategories() {
                    this.$axios.get('{{ route("shop.api.categories.tree") }}')
                        .then(response => {
                            this.categories = response.data.data || [];
                            this.$emitter.emit('categories-loaded', this.categories);
                        })
                        .catch(error => {
                            console.error(error);
                        });
                },
            },
        });

        app.component('v-kun-category-scroll', {
            template: '#v-kun-category-scroll-template',

            data() {
                return {
                    categories: [],
                    activeCategory: null,
                    isDragging: false,
                    startX: 0,
                    scrollLeft: 0,
                    isDown: false,
                };
            },

            mounted() {
                this.$emitter.on('categories-loaded', (categories) => {
                    this.categories = categories;
                });

                // Fallback: fetch independently if categories not received within 3 seconds
                setTimeout(() => {
                    if (!this.categories.length) {
                        this.fetchCategories();
                    }
                }, 3000);
            },

            methods: {
                fetchCategories() {
                    this.$axios.get('{{ route("shop.api.categories.tree") }}')
                        .then(response => {
                            this.categories = response.data.data || [];
                        })
                        .catch(error => {
                            console.error(error);
                        });
                },

                startDrag(e) {
                    this.isDown = true;
                    this.isDragging = false;
                    this.startX = e.pageX - this.$refs.scrollContainer.offsetLeft;
                    this.scrollLeft = this.$refs.scrollContainer.scrollLeft;
                },

                onDrag(e) {
                    if (!this.isDown) return;
                    const x = e.pageX - this.$refs.scrollContainer.offsetLeft;
                    const walk = (x - this.startX) * 2;
                    if (Math.abs(walk) > 5) this.isDragging = true;
                    e.preventDefault();
                    this.$refs.scrollContainer.scrollLeft = this.scrollLeft - walk;
                },

                stopDrag() {
                    this.isDown = false;
                },

                navigate(e, url) {
                    if (this.isDragging) return;
                    window.location.href = url;
                },
            },
        });
    </script>

    <script type="module">
        // Sticky header shadow on scroll
        const header = document.getElementById('kun-main-header');
        if (header) {
            window.addEventListener('scroll', () => {
                header.classList.toggle('shadow-md', window.scrollY > 10);
            }, { passive: true });
        }
    </script>
@endPushOnce
