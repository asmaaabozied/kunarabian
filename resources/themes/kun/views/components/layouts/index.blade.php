@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])


<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >
        <meta
            name="generator"
            content="Bagisto"
        >

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? asset('themes/shop/kun/images/favicon.ico') }}"
        />

        {{-- Default Shop assets (JS + CSS from default theme) --}}
        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'], 'shop')

        {{-- KUN Theme assets (CSS only) --}}
        @bagistoVite(['src/Resources/assets/css/app.css'], 'shop-kun')

        {{-- KUN Fonts --}}
        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            rel="preload"
            as="style"
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Poppins:wght@300;400;500;600;700&display=swap"
        />

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Poppins:wght@300;400;500;600;700&display=swap"
        />

        @stack('styles')

        <style>
            body {
                font-family: var(--kun-font-body);
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: var(--kun-font-display);
            }

            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        @if(core()->getConfigData('general.content.speculation_rules.enabled'))
            <script type="speculationrules">
                @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            </script>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

    </head>

    <body>
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        {{-- Skip to Content (Accessibility) --}}
        <a href="#main" class="kun-skip-to-content">
            Skip to content
        </a>

        <div id="app">
            <x-shop::flash-group />

            <x-shop::modal.confirm />

            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            @if(
                core()->getConfigData('general.gdpr.settings.enabled')
                && core()->getConfigData('general.gdpr.cookie.enabled')
            )
                <x-shop::layouts.cookie />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}

            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {{-- ARIA Live Region for dynamic announcements --}}
        <div aria-live="polite" aria-atomic="true" class="kun-sr-only" id="kun-announcements"></div>

        {{-- Back to Top Button --}}
        <button
            type="button"
            class="kun-back-to-top"
            id="kun-back-to-top"
            aria-label="Back to top"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 15l-6-6-6 6"/>
            </svg>
        </button>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            window.addEventListener("load", function (event) {
                app.mount("#app");
            });
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>

        {{-- Scroll Reveal Observer + Back to Top --}}
        <script>
            (function() {
                // Scroll-triggered reveals (supports dynamically-added Vue elements)
                if ('IntersectionObserver' in window) {
                    var revealObs = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                var el = entry.target;
                                if (el.classList.contains('kun-reveal')) {
                                    el.classList.add('kun-reveal--visible');
                                }
                                if (el.classList.contains('kun-reveal-stagger')) {
                                    el.classList.add('kun-reveal-stagger--visible');
                                }
                                revealObs.unobserve(el);
                            }
                        });
                    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

                    function observeReveals(root) {
                        root.querySelectorAll('.kun-reveal:not(.kun-reveal--visible), .kun-reveal-stagger:not(.kun-reveal-stagger--visible)').forEach(function(el) {
                            revealObs.observe(el);
                        });
                    }

                    // Observe elements already in DOM
                    observeReveals(document);

                    // Watch for Vue-rendered elements added later
                    var mutObs = new MutationObserver(function(mutations) {
                        mutations.forEach(function(m) {
                            m.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1) {
                                    if (node.classList && (node.classList.contains('kun-reveal') || node.classList.contains('kun-reveal-stagger'))) {
                                        revealObs.observe(node);
                                    }
                                    observeReveals(node);
                                }
                            });
                        });
                    });
                    mutObs.observe(document.getElementById('app') || document.body, { childList: true, subtree: true });
                }

                // Back to top button
                var backBtn = document.getElementById('kun-back-to-top');
                if (backBtn) {
                    var scrollThreshold = 600;
                    var ticking = false;

                    window.addEventListener('scroll', function() {
                        if (!ticking) {
                            window.requestAnimationFrame(function() {
                                if (window.scrollY > scrollThreshold) {
                                    backBtn.classList.add('kun-back-to-top--visible');
                                } else {
                                    backBtn.classList.remove('kun-back-to-top--visible');
                                }
                                ticking = false;
                            });
                            ticking = true;
                        }
                    });

                    backBtn.addEventListener('click', function() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
            })();
        </script>
    </body>
</html>