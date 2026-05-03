@inject ('reviewHelper', 'Webkul\Product\Helpers\Review')
@inject ('productViewHelper', 'Webkul\Product\Helpers\View')

@php
    $avgRatings = $reviewHelper->getAverageRating($product);

    $percentageRatings = $reviewHelper->getPercentageRating($product);

    $customAttributeValues = $productViewHelper->getAdditionalData($product);

    $attributeData = collect($customAttributeValues)->filter(fn ($item) => ! empty($item['value']));

    $totalRatings = $reviewHelper->getTotalFeedback($product);

    $totalReviews = $reviewHelper->getTotalReviews($product);
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="{{ trim($product->meta_description) != "" ? $product->meta_description : \Illuminate\Support\Str::limit(strip_tags($product->description), 120, '') }}"/>

    <meta name="keywords" content="{{ $product->meta_keywords }}"/>

    @if (core()->getConfigData('catalog.rich_snippets.products.enable'))
        <script type="application/ld+json">
            {!! app('Webkul\Product\Helpers\SEO')->getProductJsonLd($product) !!}
        </script>
    @endif

    <?php $productBaseImage = product_image()->getProductBaseImage($product); ?>

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $product->name }}" />
    <meta name="twitter:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
    <meta name="twitter:image:alt" content="" />
    <meta name="twitter:image" content="{{ $productBaseImage['medium_image_url'] }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:title" content="{{ $product->name }}" />
    <meta property="og:image" content="{{ $productBaseImage['medium_image_url'] }}" />
    <meta property="og:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />
    <meta property="og:url" content="{{ route('shop.product_or_category.index', $product->url_key) }}" />
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ trim($product->meta_title) != "" ? $product->meta_title : $product->name }}
    </x-slot>

    {!! view_render_event('bagisto.shop.products.view.before', ['product' => $product]) !!}

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        <div class="kun-section kun-section--tight max-lg:hidden">
            <x-shop::breadcrumbs
                name="product"
                :entity="$product"
            />
        </div>
    @endif

    <!-- Product Information Vue Component -->
    <v-product>
        <x-shop::shimmer.products.view />
    </v-product>

    <!-- Tabs Section (Desktop) -->
    <div class="kun-section mt-12 max-1180:hidden">
        <v-kun-product-tabs>
            <div class="flex gap-8 border-b border-slate-200">
                <button class="kun-tab-btn kun-tab-active pb-3 text-base font-semibold">
                    @lang('shop::app.products.view.description')
                </button>
                <button class="kun-tab-btn pb-3 text-base font-medium text-slate-400">
                    @lang('shop::app.products.view.review')
                </button>
            </div>

            <div class="mt-8">
                <div class="prose max-w-none text-base leading-relaxed text-slate-600">
                    {!! $product->description !!}
                </div>
            </div>
        </v-kun-product-tabs>
    </div>

    <!-- Tabs Section (Mobile - Accordion) -->
    <div class="kun-section mt-6 grid gap-3 1180:hidden">
        <x-shop::accordion
            class="max-md:border-none"
            :is-active="true"
        >
            <x-slot:header class="bg-slate-50 rounded-xl max-md:!py-3 max-sm:!py-2">
                <p class="text-base font-medium">
                    @lang('shop::app.products.view.description')
                </p>
            </x-slot>

            <x-slot:content class="max-sm:px-0">
                <div class="mb-5 text-base text-slate-600 max-1180:text-sm max-md:mb-1 max-md:px-4">
                    {!! $product->description !!}
                </div>
            </x-slot>
        </x-shop::accordion>

        @if (count($attributeData))
            <x-shop::accordion
                class="max-md:border-none"
                :is-active="false"
            >
                <x-slot:header class="bg-slate-50 rounded-xl max-md:!py-3 max-sm:!py-2">
                    <p class="text-base font-medium">
                        @lang('shop::app.products.view.additional-information')
                    </p>
                </x-slot>

                <x-slot:content class="max-sm:px-0">
                    <div class="max-1180:px-5">
                        <div class="grid max-w-max grid-cols-[auto_1fr] gap-4 text-base text-slate-600 max-1180:text-sm">
                            @foreach ($customAttributeValues as $customAttributeValue)
                                @if (! empty($customAttributeValue['value']))
                                    <div class="grid">
                                        <p class="text-base text-slate-900 font-medium" v-pre>
                                            {{ $customAttributeValue['label'] }}
                                        </p>
                                    </div>

                                    @if ($customAttributeValue['type'] == 'file')
                                        <a
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}"
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <span class="text-2xl icon-download"></span>
                                        </a>
                                    @elseif ($customAttributeValue['type'] == 'image')
                                        <a
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}"
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <img
                                                class="w-5 h-5 min-h-5 min-w-5"
                                                src="{{ Storage::url($customAttributeValue['value']) }}"
                                                alt="Product Image"
                                            />
                                        </a>
                                    @else
                                        <div class="grid">
                                            <p class="text-base text-slate-500" v-pre>
                                                {{ $customAttributeValue['value'] ?? '-' }}
                                            </p>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-slot>
            </x-shop::accordion>
        @endif

        <x-shop::accordion
            class="max-md:border-none"
            :is-active="false"
        >
            <x-slot:header
                class="bg-slate-50 rounded-xl max-md:!py-3 max-sm:!py-2"
                id="review-accordian-button"
            >
                <p class="text-base font-medium">
                    @lang('shop::app.products.view.review')
                </p>
            </x-slot>

            <x-slot:content>
                @include('shop::products.view.reviews')
            </x-slot>
        </x-shop::accordion>
    </div>

    <v-product-associations />

    {!! view_render_event('bagisto.shop.products.view.after', ['product' => $product]) !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-product-template"
        >
            <x-shop::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form
                    ref="formData"
                    @submit="handleSubmit($event, addToCart)"
                >
                    <input
                        type="hidden"
                        name="product_id"
                        value="{{ $product->id }}"
                    >

                    <input
                        type="hidden"
                        name="is_buy_now"
                        v-model="is_buy_now"
                    >

                    <div class="kun-section">
                        <div class="kun-pdp-grid">
                            <!-- Gallery -->
                            @include('shop::products.view.gallery')

                            <!-- Product Details -->
                            <div class="relative flex flex-col gap-[50px] max-1180:w-full max-sm:gap-[32px]">
                              <!-- Main Info Section (name, price, desc, options) -->
                              <div class="flex flex-col gap-[48px] max-sm:gap-[28px]">
                                <!-- Inner items with 20px gap -->
                                <div class="flex flex-col gap-[20px] max-sm:gap-[16px]">
                                {!! view_render_event('bagisto.shop.products.name.before', ['product' => $product]) !!}

                                <!-- Row 1: Name + Wishlist/Share icons -->
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex flex-col gap-[11px]">
                                        <h1 class="font-[family-name:var(--kun-font-body)] text-[28px] font-medium leading-[36px] text-slate-900 max-sm:text-xl" v-pre>
                                            {{ $product->name }}
                                        </h1>

                                        {{-- Vendor / Brand name --}}
                                        @if ($product->brand)
                                            <p class="text-[20px] font-medium leading-[28px] text-[var(--kun-color-terracotta)]" v-pre>
                                                {{ $product->brand->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex items-start gap-[13px] shrink-0">
                                        @if (core()->getConfigData('customer.settings.wishlist.wishlist_option'))
                                            <div
                                                class="kun-pdp-pill kun-pdp-pill--wishlist transition-all hover:brightness-95"
                                                role="button"
                                                aria-label="@lang('shop::app.products.view.add-to-wishlist')"
                                                :aria-pressed="isWishlist ? 'true' : 'false'"
                                                tabindex="0"
                                                @click="addToWishlist"
                                            >
                                                <span
                                                    class="text-[20px]"
                                                    :class="isWishlist ? 'icon-heart-fill text-rose-400' : 'icon-heart text-rose-400'"
                                                ></span>
                                            </div>
                                        @endif

                                        <!-- Share Icon -->
                                        <div
                                            class="kun-pdp-pill kun-pdp-pill--share"
                                            role="button"
                                            aria-label="Share"
                                            tabindex="0"
                                            @click="shareProduct"
                                        >
                                            <svg class="w-5 h-5 text-fuchsia-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {!! view_render_event('bagisto.shop.products.name.after', ['product' => $product]) !!}

                                <!-- Divider -->
                                <div class="h-px w-full bg-slate-200"></div>

                                <!-- Row 2: Price (left) + Rating badges (right) -->
                                {!! view_render_event('bagisto.shop.products.price.before', ['product' => $product]) !!}
                                {!! view_render_event('bagisto.shop.products.rating.before', ['product' => $product]) !!}

                                <div class="flex items-center gap-[41px] max-sm:flex-col max-sm:items-start max-sm:gap-3">
                                    <!-- Price Column -->
                                    <div class="flex flex-col gap-[6px] shrink-0">
                                        <p class="text-[36px] font-medium leading-[44px] text-[var(--kun-color-terracotta)]">
                                            {!! $product->getTypeInstance()->getPriceHtml() !!}
                                        </p>

                                        @if (\Webkul\Tax\Facades\Tax::isInclusiveTaxProductPrices())
                                            <span class="text-sm text-slate-400">
                                                (@lang('shop::app.products.view.tax-inclusive'))
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Rating + Reviews Column (always visible) -->
                                    <div class="flex flex-col gap-[14px]">
                                        <div class="flex items-start gap-3">
                                            <!-- Star Rating Pill -->
                                            <div
                                                class="kun-pdp-pill kun-pdp-pill--rating"
                                                role="button"
                                                aria-label="@lang('shop::app.products.view.reviews.rating')"
                                                tabindex="0"
                                                @click="scrollToReview"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                                <span>{{ $totalRatings ? number_format($avgRatings, 1) : '0.0' }}</span>
                                            </div>

                                            <!-- Reviews Count Pill -->
                                            <div
                                                class="kun-pdp-pill kun-pdp-pill--reviews"
                                                role="button"
                                                aria-label="@lang('shop::app.products.view.review')"
                                                tabindex="0"
                                                @click="scrollToReview"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                                <span>{{ $totalRatings ?: 0 }} @lang('shop::app.products.view.review')</span>
                                            </div>
                                        </div>

                                        <!-- Recommendation Text -->
                                        @if ($totalRatings && $avgRatings >= 4)
                                            <p class="text-[16px] leading-[24px] text-slate-400">
                                                <span class="text-emerald-600">{{ round(($avgRatings / 5) * 100) }}%</span>
                                                of buyers have recommended this.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if (count($product->getTypeInstance()->getCustomerGroupPricingOffers()))
                                    <div class="grid gap-1">
                                        @foreach ($product->getTypeInstance()->getCustomerGroupPricingOffers() as $offer)
                                            <p class="text-slate-500 [&>*]:text-slate-900">
                                                {!! $offer !!}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                {!! view_render_event('bagisto.shop.products.price.after', ['product' => $product]) !!}
                                {!! view_render_event('bagisto.shop.products.rating.after', ['product' => $product]) !!}

                                <!-- Divider -->
                                <div class="h-px w-full bg-slate-200"></div>

                                <!-- Description Section -->
                                {!! view_render_event('bagisto.shop.products.short_description.before', ['product' => $product]) !!}

                                <div class="flex flex-col gap-[10px]">
                                    <h2 class="text-[20px] font-medium leading-[28px] text-slate-900">
                                        @lang('shop::app.products.view.description')
                                    </h2>
                                    <div class="text-[16px] leading-[24px] text-slate-500 kun-pdp-description">
                                        {!! $product->short_description !!}
                                    </div>
                                </div>

                                <!-- Short Story Box -->
                                @if ($product->description)
                                    <div class="rounded-[16px] bg-slate-100 p-[20px]">
                                        <h3 class="text-[20px] font-medium leading-[28px] text-slate-900">
                                            Short Story
                                        </h3>
                                        <p class="mt-[10px] text-[16px] leading-[24px] text-slate-500">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 120) }}
                                        </p>
                                    </div>
                                @endif

                                {!! view_render_event('bagisto.shop.products.short_description.after', ['product' => $product]) !!}

                                <!-- Divider -->
                                <div class="h-px w-full bg-slate-200"></div>

                                <!-- Product Type Options (Color swatches, Size pills, etc.) -->
                                @include('shop::products.view.types.simple')

                                @include('shop::products.view.types.configurable')

                                @include('shop::products.view.types.grouped')

                                @include('shop::products.view.types.bundle')

                                @include('shop::products.view.types.downloadable')

                                @include('shop::products.view.types.booking')

                                </div><!-- /inner 20px gap -->

                                <!-- Actions Row: Quantity | Compare | Add to Cart | Buy Now — all in one horizontal row -->
                                <div class="flex flex-wrap items-center gap-4 max-sm:gap-3">
                                    {!! view_render_event('bagisto.shop.products.view.quantity.before', ['product' => $product]) !!}

                                    @if ($product->getTypeInstance()->showQuantityBox())
                                        <x-shop::quantity-changer
                                            name="quantity"
                                            value="1"
                                            class="gap-x-[10px] rounded-[48px] bg-slate-100 border-0 px-[24px] py-[14px] max-sm:px-4 max-sm:py-3"
                                        />
                                    @endif

                                    {!! view_render_event('bagisto.shop.products.view.quantity.after', ['product' => $product]) !!}

                                    <!-- Compare Icon Button -->
                                    {!! view_render_event('bagisto.shop.products.view.compare.before', ['product' => $product]) !!}

                                    @if (core()->getConfigData('catalog.products.settings.compare_option'))
                                        <div
                                            class="flex h-[56px] cursor-pointer items-center justify-center rounded-[48px] border border-[var(--kun-color-olive)] bg-neutral-100 px-[24px] py-[14px] transition-colors hover:bg-neutral-200"
                                            role="button"
                                            aria-label="@lang('shop::app.products.view.add-to-compare')"
                                            tabindex="0"
                                            @click="is_buy_now=0; addToCompare({{ $product->id }})"
                                        >
                                            <span class="icon-compare text-[20px] text-[var(--kun-color-olive)]"></span>
                                        </div>
                                    @endif

                                    {!! view_render_event('bagisto.shop.products.view.compare.after', ['product' => $product]) !!}

                                    @if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
                                        <!-- Add To Cart Button (OUTLINED per Figma) -->
                                        {!! view_render_event('bagisto.shop.products.view.add_to_cart.before', ['product' => $product]) !!}

                                        <button
                                            type="submit"
                                            class="kun-pdp-btn-outline flex h-[56px] items-center gap-2 !w-auto !px-[24px] !py-[14px] text-[16px]"
                                            :disabled="!{{ $product->isSaleable(1) ? 'true' : 'false' }} || isStoring.addToCart"
                                            @click="is_buy_now=0;"
                                        >
                                            <span
                                                class="icon-cart text-[20px]"
                                                v-if="! isStoring.addToCart"
                                            ></span>
                                            <img
                                                v-if="isStoring.addToCart"
                                                class="h-5 w-5 animate-spin"
                                                src="{{ bagisto_asset('images/spinner.svg') }}"
                                            />
                                            @lang('shop::app.products.view.add-to-cart')
                                        </button>

                                        {!! view_render_event('bagisto.shop.products.view.add_to_cart.after', ['product' => $product]) !!}

                                        <!-- Buy Now Button (FILLED per Figma) -->
                                        {!! view_render_event('bagisto.shop.products.view.buy_now.before', ['product' => $product]) !!}

                                        <button
                                            type="submit"
                                            class="kun-pdp-btn-primary flex h-[56px] items-center justify-center !w-auto !px-[56px] !py-[18px] text-[16px] !rounded-[64px]"
                                            :disabled="!{{ $product->isSaleable(1) ? 'true' : 'false' }} || isStoring.buyNow"
                                            @click="is_buy_now=1;"
                                        >
                                            <img
                                                v-if="isStoring.buyNow"
                                                class="h-5 w-5 animate-spin"
                                                src="{{ bagisto_asset('images/spinner.svg') }}"
                                            />
                                            @lang('shop::app.products.view.buy-now')
                                        </button>

                                        {!! view_render_event('bagisto.shop.products.view.buy_now.after', ['product' => $product]) !!}
                                    @endif
                                </div>

                                {!! view_render_event('bagisto.shop.products.view.additional_actions.before', ['product' => $product]) !!}

                                {!! view_render_event('bagisto.shop.products.view.additional_actions.after', ['product' => $product]) !!}

                              </div><!-- /48px gap (main content + buttons) -->

                                <!-- Delivery Info Box -->
                                <div class="rounded-[14px] border border-slate-100 p-[17px]">
                                    <div class="flex items-start gap-[14px]">
                                        <svg class="mt-1 h-6 w-6 flex-shrink-0 text-[var(--kun-color-olive)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-[20px] font-medium leading-[28px] text-[var(--kun-color-olive)]">Free Delivery</p>
                                            <p class="mt-[7px] text-[14px] leading-[22px] text-slate-500">Enter your Postal code for Delivery Availability</p>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="my-[21px] h-px w-full bg-slate-100"></div>

                                    <div class="flex items-start gap-[14px]">
                                        <svg class="mt-1 h-5 w-5 flex-shrink-0 text-[var(--kun-color-olive)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-[20px] font-medium leading-[28px] text-[var(--kun-color-olive)]">Return Delivery</p>
                                            <p class="mt-[7px] text-[14px] leading-[22px] text-slate-500">Free 30 days Delivery Return. Details</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </x-shop::form>
        </script>

        <!-- Kun Product Tabs Template -->
        <script
            type="text/x-template"
            id="v-kun-product-tabs-template"
        >
            <div>
                <div class="flex gap-8 border-b border-slate-200">
                    <button
                        type="button"
                        class="kun-tab-btn pb-3 text-base font-medium text-slate-400 transition-colors"
                        :class="{ 'kun-tab-active': activeTab === 'description' }"
                        @click="activeTab = 'description'"
                    >
                        @lang('shop::app.products.view.description')
                    </button>

                    @if(count($attributeData))
                        <button
                            type="button"
                            class="kun-tab-btn pb-3 text-base font-medium text-slate-400 transition-colors"
                            :class="{ 'kun-tab-active': activeTab === 'additional' }"
                            @click="activeTab = 'additional'"
                        >
                            @lang('shop::app.products.view.additional-information')
                        </button>
                    @endif

                    <button
                        type="button"
                        id="review-tab-button"
                        class="kun-tab-btn pb-3 text-base font-medium text-slate-400 transition-colors"
                        :class="{ 'kun-tab-active': activeTab === 'reviews' }"
                        @click="activeTab = 'reviews'"
                    >
                        @lang('shop::app.products.view.review')
                    </button>
                </div>

                <!-- Description Tab Content -->
                <div class="mt-8" v-show="activeTab === 'description'">
                    <div class="prose max-w-none text-base leading-relaxed text-slate-600">
                        {!! $product->description !!}
                    </div>
                </div>

                <!-- Additional Info Tab Content -->
                @if(count($attributeData))
                    <div class="mt-8" v-show="activeTab === 'additional'">
                        <div class="grid max-w-max grid-cols-[auto_1fr] gap-4">
                            @foreach ($customAttributeValues as $customAttributeValue)
                                @if (! empty($customAttributeValue['value']))
                                    <div class="grid">
                                        <p class="text-base font-medium text-slate-900">
                                            {!! $customAttributeValue['label'] !!}
                                        </p>
                                    </div>

                                    @if ($customAttributeValue['type'] == 'file')
                                        <a
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}"
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <span class="text-2xl icon-download"></span>
                                        </a>
                                    @elseif ($customAttributeValue['type'] == 'image')
                                        <a
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}"
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <img
                                                class="w-5 h-5 min-h-5 min-w-5"
                                                src="{{ Storage::url($customAttributeValue['value']) }}"
                                            />
                                        </a>
                                    @else
                                        <div class="grid">
                                            <p class="text-base text-slate-500">
                                                {!! $customAttributeValue['value'] !!}
                                            </p>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Reviews Tab Content -->
                <div class="mt-8" v-show="activeTab === 'reviews'">
                    @include('shop::products.view.reviews')
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-product', {
                template: '#v-product-template',

                data() {
                    return {
                        isWishlist: false,

                        isCustomer: '{{ auth()->guard('customer')->check() }}',

                        is_buy_now: 0,

                        isStoring: {
                            addToCart: false,

                            buyNow: false,
                        },
                    }
                },

                mounted() {
                    this.checkWishlistStatus();
                },

                methods: {
                    addToCart(params) {
                        const operation = this.is_buy_now ? 'buyNow' : 'addToCart';

                        this.isStoring[operation] = true;

                        let formData = new FormData(this.$refs.formData);

                        this.ensureQuantity(formData);

                        this.$axios.post('{{ route("shop.api.checkout.cart.store") }}', formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                            .then(response => {
                                if (response.data.message) {
                                    this.$emitter.emit('update-mini-cart', response.data.data);

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    if (response.data.redirect) {
                                        window.location.href= response.data.redirect;
                                    }
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                }

                                this.isStoring[operation] = false;
                            })
                            .catch(error => {
                                this.isStoring[operation] = false;

                                this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.message });
                            });
                    },

                    checkWishlistStatus() {
                        if (this.isCustomer) {
                            this.$axios.get('{{ route('shop.api.customers.account.wishlist.index') }}')
                                .then(response => {
                                    const wishlistItems = response.data.data || [];

                                    this.isWishlist = Boolean(wishlistItems.find(item => item.product.id == "{{ $product->id }}")?.product?.is_wishlist);
                                })
                                .catch(error => {});
                        }
                    },

                    addToWishlist() {
                        if (this.isCustomer) {
                            this.$axios.post('{{ route('shop.api.customers.account.wishlist.store') }}', {
                                    product_id: "{{ $product->id }}"
                                })
                                .then(response => {
                                    this.isWishlist = ! this.isWishlist;

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

                        let existingItems = this.getStorageValue(this.getCompareItemsStorageKey()) ?? [];

                        if (existingItems.length) {
                            if (! existingItems.includes(productId)) {
                                existingItems.push(productId);

                                this.setStorageValue(this.getCompareItemsStorageKey(), existingItems);

                                this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.products.view.add-to-compare')" });
                            } else {
                                this.$emitter.emit('add-flash', { type: 'warning', message: "@lang('shop::app.products.view.already-in-compare')" });
                            }
                        } else {
                            this.setStorageValue(this.getCompareItemsStorageKey(), [productId]);

                            this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.products.view.add-to-compare')" });
                        }
                    },

                    updateQty(quantity, id) {
                        this.isLoading = true;

                        let qty = {};

                        qty[id] = quantity;

                        this.$axios.put('{{ route('shop.api.checkout.cart.update') }}', { qty })
                            .then(response => {
                                if (response.data.message) {
                                    this.cart = response.data.data;
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                }

                                this.isLoading = false;
                            }).catch(error => this.isLoading = false);
                    },

                    getCompareItemsStorageKey() {
                        return 'compare_items';
                    },

                    setStorageValue(key, value) {
                        localStorage.setItem(key, JSON.stringify(value));
                    },

                    getStorageValue(key) {
                        let value = localStorage.getItem(key);

                        if (value) {
                            value = JSON.parse(value);
                        }

                        return value;
                    },

                    scrollToReview() {
                        let accordianElement = document.querySelector('#review-accordian-button');

                        if (accordianElement) {
                            accordianElement.click();

                            accordianElement.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }

                        let tabElement = document.querySelector('#review-tab-button');

                        if (tabElement) {
                            tabElement.click();

                            tabElement.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    },

                    shareProduct() {
                        if (navigator.share) {
                            navigator.share({
                                title: document.title,
                                url: window.location.href,
                            });
                        } else {
                            navigator.clipboard.writeText(window.location.href).then(() => {
                                this.$emitter.emit('add-flash', { type: 'success', message: 'Link copied to clipboard!' });
                            });
                        }
                    },

                    ensureQuantity(formData) {
                        if (! formData.has('quantity')) {
                            formData.append('quantity', 1);
                        }
                    },
                },
            });

            app.component('v-kun-product-tabs', {
                template: '#v-kun-product-tabs-template',

                data() {
                    return {
                        activeTab: 'description',
                    };
                },
            });
        </script>

        <script
            type="text/x-template"
            id="v-product-associations-template"
        >
            <div ref="carouselWrapper">
                <template v-if="isVisible">
                    <x-shop::products.carousel
                        :title="trans('shop::app.products.view.related-product-title')"
                        :src="route('shop.api.products.related.index', ['id' => $product->id])"
                    />

                    <x-shop::products.carousel
                        :title="trans('shop::app.products.view.up-sell-title')"
                        :src="route('shop.api.products.up-sell.index', ['id' => $product->id])"
                    />
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-product-associations', {
                template: '#v-product-associations-template',

                data() {
                    return {
                        isVisible: false,
                    };
                },

                mounted() {
                    const observer = new IntersectionObserver(
                        (entries) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting) {
                                    this.isVisible = true;
                                    observer.unobserve(entry.target);
                                }
                            });
                        },
                        { threshold: 0.1 }
                    );

                    observer.observe(this.$refs.carouselWrapper);
                }
            });
        </script>
    @endPushOnce
</x-shop::layouts>
