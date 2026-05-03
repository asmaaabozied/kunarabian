@php
    $channel = core()->getCurrentChannel();
@endphp

@push('meta')
    <meta name="title"       content="{{ $channel->home_seo['meta_title']       ?? '' }}">
    <meta name="description" content="{{ $channel->home_seo['meta_description'] ?? '' }}">
    <meta name="keywords"    content="{{ $channel->home_seo['meta_keywords']    ?? '' }}">
@endpush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <x-slot:title>
        {{ $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    @foreach ($customizations as $customization)
        @php $data = $customization->options; @endphp

        @switch ($customization->type)
            @case ($customization::IMAGE_CAROUSEL)
                @include('shop::home.kun-image-carousel', ['options' => $data, 'customization' => $customization])
                @break

            @case ($customization::STATIC_CONTENT)
                @if (! empty($data['kun_component']))
                    @include('shop::home.kun-' . $data['kun_component'], ['options' => $data, 'customization' => $customization])
                @else
                    @if (! empty($data['css']))
                        @push('styles')
                            <style>{{ $data['css'] }}</style>
                        @endpush
                    @endif
                    @if (! empty($data['html']))
                        <section class="kun-section" aria-label="{{ $customization->name }}">
                            {!! $data['html'] !!}
                        </section>
                    @endif
                @endif
                @break

            @case ($customization::CATEGORY_CAROUSEL)
                @include('shop::home.kun-category-carousel', ['options' => $data, 'customization' => $customization])
                @break

            @case ($customization::PRODUCT_CAROUSEL)
                @include('shop::home.kun-product-carousel', ['options' => $data, 'customization' => $customization])
                @break
        @endswitch
    @endforeach

</x-shop::layouts>
