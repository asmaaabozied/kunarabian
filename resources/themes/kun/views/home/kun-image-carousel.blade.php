@php
    $images = $options['images'] ?? [];
    $carouselId = 'kun-carousel-' . $customization->id;
    $slideCount = count($images);
@endphp

@if ($slideCount)
<v-kun-image-carousel
    carousel-id="{{ $carouselId }}"
    :slides='@json($images)'
    :auto-play="{{ $slideCount > 1 ? 'true' : 'false' }}"
    label="{{ $customization->name }}"
>
    {{-- SSR fallback: first slide --}}
    <section class="kun-section" aria-label="{{ $customization->name }}">
        <div class="kun-hero">
            <div class="absolute inset-0 w-full h-full">
                <img
                    src="{{ $images[0]['image'] ?? '' }}"
                    alt="{{ $images[0]['title'] ?? 'Slide 1' }}"
                    class="absolute inset-0 w-full h-full object-cover"
                    fetchpriority="high"
                >
                <div class="absolute inset-0 kun-gradient-hero-bottom"></div>
                <div class="absolute inset-0 kun-gradient-hero-left"></div>
            </div>
        </div>
    </section>
</v-kun-image-carousel>

@pushOnce('scripts')
    <script type="text/x-template" id="v-kun-image-carousel-template">
        <section class="kun-section" :aria-label="label">
            <div
                class="kun-hero"
                ref="heroEl"
                @mousedown="startDrag"
                @mousemove="onDrag"
                @mouseup="endDrag"
                @mouseleave="endDrag"
                @touchstart.passive="startTouch"
                @touchmove.passive="onTouch"
                @touchend="endTouch"
            >
                <div
                    v-for="(slide, i) in slides"
                    :key="i"
                    class="kun-hero-slide"
                    :class="{ 'kun-hero-slide--active': current === i }"
                >
                    <img
                        :src="slide.image"
                        :alt="slide.title || ('Slide ' + (i + 1))"
                        class="absolute inset-0 w-full h-full object-cover"
                        :fetchpriority="i === 0 ? 'high' : undefined"
                        :loading="i === 0 ? undefined : 'lazy'"
                    >
                    <div class="absolute inset-0 kun-gradient-hero-bottom"></div>
                    <div class="absolute inset-0 kun-gradient-hero-left"></div>

                    {{-- Bottom-left text overlay --}}
                    <div class="kun-hero-text">
                        <div v-if="slide.subtitle" class="flex items-center gap-2 mb-2.5">
                            <div class="kun-accent-line bg-[#D0BF94]"></div>
                            <span class="text-[#D0BF94] text-sm font-normal tracking-wider">@{{ slide.subtitle }}</span>
                        </div>

                        <h2 v-if="slide.title" class="font-poppins text-[#FAF9F4] text-[40px] font-medium leading-[48px] m-0 mb-2">
                            @{{ slide.title }}
                        </h2>

                        <p v-if="slide.item_count" class="text-[#FAF9F4]/70 text-sm m-0">
                            @{{ slide.item_count }}
                        </p>
                    </div>

                    {{-- Bottom-right CTA --}}
                    <div v-if="slide.link" class="kun-hero-cta">
                        <a :href="slide.link" class="kun-btn-cta" @click="onCtaClick($event)">
                            @lang('kun-theme::app.home.discover-more')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M14.43 5.93L20.5 12l-6.07 6.07" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

                {{-- Autoplay progress bar --}}
                <div v-if="autoPlay && slides.length > 1" class="kun-hero-progress">
                    <div
                        class="kun-hero-progress__bar"
                        :key="'progress-' + current + '-' + progressKey"
                    ></div>
                </div>

            {{-- Dot pagination --}}
            <div v-if="slides.length > 1" class="flex items-center justify-center gap-2 mt-4" role="tablist">
                <button
                    v-for="(slide, i) in slides"
                    :key="i"
                    @click="goTo(i)"
                    role="tab"
                    :aria-label="'Go to slide ' + (i + 1)"
                    :aria-selected="current === i ? 'true' : 'false'"
                    class="kun-dot border-none p-0"
                    :class="current === i ? 'kun-dot--active !w-8 !h-1.5 !rounded-full !bg-[#0F172B]' : '!w-5 !h-1.5 !rounded-full'"
                ></button>
            </div>
        </section>
    </script>

    <script type="module">
        app.component('v-kun-image-carousel', {
            template: '#v-kun-image-carousel-template',

            props: {
                carouselId: { type: String, required: true },
                slides: { type: Array, default: () => [] },
                autoPlay: { type: Boolean, default: true },
                label: { type: String, default: '' },
            },

            data() {
                return {
                    current: 0,
                    timer: null,
                    dragStartX: 0,
                    isDragging: false,
                    progressKey: 0,
                };
            },

            mounted() {
                if (this.autoPlay && this.slides.length > 1) {
                    this.startTimer();
                }
            },

            beforeUnmount() {
                this.stopTimer();
            },

            methods: {
                goTo(index) {
                    this.current = index;
                    this.progressKey++;
                    this.resetTimer();
                },

                next() {
                    this.current = (this.current + 1) % this.slides.length;
                    this.progressKey++;
                },

                prev() {
                    this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                    this.progressKey++;
                },

                startTimer() {
                    this.stopTimer();
                    this.timer = setInterval(() => this.next(), 5000);
                },

                stopTimer() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },

                resetTimer() {
                    if (this.autoPlay && this.slides.length > 1) {
                        this.startTimer();
                    }
                },

                startDrag(e) {
                    this.dragStartX = e.clientX;
                    this.isDragging = false;
                },

                onDrag(e) {
                    if (this.dragStartX && Math.abs(e.clientX - this.dragStartX) > 8) {
                        this.isDragging = true;
                    }
                },

                endDrag(e) {
                    if (!this.dragStartX) return;
                    if (this.isDragging) {
                        const diff = this.dragStartX - e.clientX;
                        if (Math.abs(diff) >= 50) {
                            diff > 0 ? this.next() : this.prev();
                            this.resetTimer();
                        }
                    }
                    this.dragStartX = 0;
                    this.isDragging = false;
                },

                startTouch(e) {
                    this.dragStartX = e.touches[0].clientX;
                    this.isDragging = false;
                },

                onTouch(e) {
                    if (Math.abs(e.touches[0].clientX - this.dragStartX) > 8) {
                        this.isDragging = true;
                    }
                },

                endTouch(e) {
                    if (!this.isDragging) { this.dragStartX = 0; return; }
                    const diff = this.dragStartX - e.changedTouches[0].clientX;
                    if (Math.abs(diff) >= 50) {
                        diff > 0 ? this.next() : this.prev();
                        this.resetTimer();
                    }
                    this.dragStartX = 0;
                    this.isDragging = false;
                },

                onCtaClick(e) {
                    if (this.isDragging) e.preventDefault();
                },
            },
        });
    </script>
@endPushOnce
@endif
