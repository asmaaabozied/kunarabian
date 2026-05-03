{!! view_render_event('bagisto.shop.layout.footer.before') !!}

@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

@php
    $channel = core()->getCurrentChannel();

    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);
@endphp

{{-- Newsletter Bar --}}
{!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}

@if (core()->getConfigData('customer.settings.newsletter.subscription'))
<div class="bg-[#3B3D2B] py-10">
    <div class="flex items-center justify-between gap-8 max-md:flex-col max-md:gap-5 max-w-[1280px] mx-auto px-10">
        <h3 class="font-poppins text-[#FAF9F4] text-[22px] font-medium leading-[30px] m-0 whitespace-nowrap">
            @lang('shop::app.components.layouts.footer.newsletter-text')
        </h3>

        <x-shop::form
            :action="route('shop.subscription.store')"
            class="flex-1 max-w-[480px]"
        >
            <div class="flex items-center gap-3">
                <x-shop::form.control-group.control
                    type="email"
                    name="email"
                    rules="required|email"
                    label="Email"
                    :aria-label="trans('shop::app.components.layouts.footer.email')"
                    placeholder="email@example.com"
                    class="kun-newsletter-input flex-1 rounded-full border-none outline-none px-4 py-3 text-[14px] bg-white/10 text-white placeholder:text-white/50 transition-all duration-150"
                />

                <button type="submit"
                        class="px-7 py-3 bg-white text-[#3B3D2B] rounded-full border-none cursor-pointer text-[14px] font-poppins font-medium transition-all duration-150 hover:bg-white/90 hover:shadow-lg whitespace-nowrap inline-flex items-center gap-2">
                    @lang('shop::app.components.layouts.footer.subscribe')
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 2L11 13"/>
                        <path d="M22 2L15 22l-4-9-9-4z"/>
                    </svg>
                </button>
            </div>

            <x-shop::form.control-group.error control-name="email" />
        </x-shop::form>
    </div>
</div>
@endif

{!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}

{{-- Footer Links --}}
<footer role="contentinfo" class="bg-footer-bg border-t border-footer-border pt-[52px] px-10 max-lg:px-5 font-poppins">
    <div class="grid grid-cols-[2fr_1fr_1fr_1fr] gap-12 pb-11 border-b border-brand-color-01-500 max-md:grid-cols-1 max-md:gap-9">
        {{-- Brand --}}
        <div>
            <a href="{{ route('shop.home.index') }}" class="inline-flex items-center no-underline mb-3.5" aria-label="{{ $channel->name }}">
                <img
                    src="{{ $channel->logo_url ?? asset('images/kun_logo.svg') }}"
                    alt="{{ $channel->name }}"
                    class="w-[117px] h-[74px]"
                >
            </a>

            @if ($channel->description)
                <p class="text-[16px] leading-[24px] text-slate-950 max-w-[380px]">
                    {{ $channel->description }}
                </p>
            @else
                <p class="text-[16px] leading-[24px] text-slate-950 max-w-[380px]">
                    @lang('shop::app.components.layouts.footer.footer-text', ['current_year' => date('Y')])
                </p>
            @endif
        </div>

        {{-- Dynamic Footer Links --}}
        @if ($customization?->options)
            @foreach ($customization->options as $columnKey => $footerLinkSection)
                <div>
                    @php
                        usort($footerLinkSection, function ($a, $b) {
                            return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                        });
                    @endphp

                    @if (! empty($footerLinkSection[0]['title']))
                        <div class="text-[16px] font-medium text-brand-color-01-1000 mb-5">
                            @lang('kun-theme::app.layout.footer.quick-links')
                        </div>
                    @endif

                    <ul class="list-none m-0 p-0 flex flex-col gap-3">
                        @foreach ($footerLinkSection as $link)
                            <li>
                                <a href="{{ $link['url'] ?? '#' }}"
                                   class="kun-footer-link text-[14px] text-slate-600 leading-[20px] no-underline transition-colors duration-150 hover:text-footer-accent">
                                    {{ $link['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif

        {{-- Social Links --}}
        <div>
            <div class="text-[16px] font-medium text-brand-color-01-1000 mb-5">
                @lang('kun-theme::app.layout.footer.follow-us')
            </div>

            <div class="flex items-center gap-3">
                <a href="#" class="kun-social-facebook flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 transition-all duration-200" aria-label="Facebook">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#334155"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                </a>
                <a href="#" class="kun-social-instagram flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 transition-all duration-200" aria-label="Instagram">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="#" class="kun-social-twitter flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 transition-all duration-200" aria-label="Twitter">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#334155"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="#" class="kun-social-youtube flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 transition-all duration-200" aria-label="YouTube">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#334155"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="flex items-center justify-between py-[18px] gap-4 flex-wrap max-sm:flex-col max-sm:items-start">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <span class="text-[13px] text-slate-500">
            @if (core()->getConfigData('general.content.footer.copyright_content'))
                {!! core()->getConfigData('general.content.footer.copyright_content') !!}
            @else
                @lang('shop::app.components.layouts.footer.footer-text', ['current_year' => date('Y')])
            @endif
        </span>

        <div class="flex items-center gap-4">
            <a href="#" class="kun-footer-link text-[13px] text-slate-500 no-underline hover:text-slate-700">@lang('kun-theme::app.layout.footer.privacy-policy')</a>
            <a href="#" class="kun-footer-link text-[13px] text-slate-500 no-underline hover:text-slate-700">@lang('kun-theme::app.layout.footer.terms-of-service')</a>
            <a href="#" class="kun-footer-link text-[13px] text-slate-500 no-underline hover:text-slate-700">@lang('kun-theme::app.layout.footer.cookie-policy')</a>
        </div>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
