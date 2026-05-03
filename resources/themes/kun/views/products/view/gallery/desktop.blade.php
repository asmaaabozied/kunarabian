<!-- Desktop Gallery — horizontal thumbnails below main image -->
<div class="sticky top-20 max-w-[588px] max-1180:hidden">
    <!-- Main Image / Video with Shimmer -->
    <div
        class="relative overflow-hidden rounded-[17px] border border-[#edf0f8]"
        v-show="isMediaLoading"
    >
        <div class="shimmer w-full rounded-[17px] aspect-[588/691] bg-slate-200"></div>
    </div>

    <div
        class="relative overflow-hidden rounded-[17px] border border-[#edf0f8]"
        v-show="! isMediaLoading"
    >
        <img
            class="w-full cursor-pointer rounded-[17px] object-cover aspect-[588/691]"
            :src="baseFile.path"
            v-if="baseFile.type == 'image'"
            alt="{{ $product->name }}"
            width="588"
            height="691"
            tabindex="0"
            @click="isImageZooming = !isImageZooming"
            @load="onMediaLoad()"
            fetchpriority="high"
        />

        <div
            class="w-full cursor-pointer rounded-[17px]"
            tabindex="0"
            v-if="baseFile.type == 'video'"
        >
            <video
                controls
                class="w-full rounded-[17px]"
                alt="{{ $product->name }}"
                @click="isImageZooming = !isImageZooming"
                @loadeddata="onMediaLoad()"
                :key="baseFile.path"
            >
                <source
                    :src="baseFile.path"
                    type="video/mp4"
                />
            </video>
        </div>
    </div>

    <!-- Horizontal Thumbnails Row -->
    <div class="flex items-center gap-3 mt-9">
        <!-- Left Arrow -->
        <button
            type="button"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50"
            aria-label="@lang('shop::app.components.products.carousel.previous')"
            @click="swipeLeft"
            v-if="lengthOfMedia"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Thumbnails Container -->
        <div
            ref="swiperContainer"
            class="flex overflow-hidden scroll-smooth gap-5"
        >
            <template v-for="(media, index) in [...media.images, ...media.videos]">
                <video
                    v-if="media.type == 'videos'"
                    class="h-[115px] w-[115px] flex-shrink-0 cursor-pointer rounded-[14px] object-cover transition-all"
                    :style="isActiveMedia(index) ? 'border: 3px solid #4d5223; opacity: 1;' : 'border: 3px solid transparent; opacity: 0.7;'"
                    @click="change(media, index)"
                    alt="{{ $product->name }}"
                    tabindex="0"
                >
                    <source
                        :src="media.video_url"
                        type="video/mp4"
                    />
                </video>

                <img
                    v-else
                    class="h-[115px] w-[115px] flex-shrink-0 cursor-pointer rounded-[14px] object-cover transition-all"
                    :style="isActiveMedia(index) ? 'border: 3px solid #4d5223; opacity: 1;' : 'border: 3px solid transparent; opacity: 0.7;'"
                    :src="media.small_image_url"
                    alt="{{ $product->name }}"
                    width="115"
                    height="115"
                    tabindex="0"
                    @click="change(media, index)"
                />
            </template>
        </div>

        <!-- Right Arrow -->
        <button
            type="button"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50"
            aria-label="@lang('shop::app.components.products.carousel.next')"
            @click="swipeRight"
            v-if="lengthOfMedia"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
