@php
    $defaultCta = 'Add to cart';
    if ($variant === 'shop') $defaultCta = 'Visit shop';

    $ctaText = $cta_label ?? $defaultCta;

    $full    = $rating !== null ? (int) floor((float) $rating) : 0;
    $hasHalf = $rating !== null && ((float) $rating - $full) >= 0.5;

    $hasWishlist = ($variant === 'product' || $variant === 'collection');
@endphp

<div
    class="bg-white rounded-2xl overflow-hidden flex flex-col shadow-[0_1px_4px_rgba(15,23,42,0.07)]"
>
    <div class="relative flex-shrink-0 h-[220px] bg-[#1a1a1a]">
        <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover">

        @if (!empty($tags))
            <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap">
                @foreach ($tags as $tag)
                    @php
                        $tagClass = match($tag['type'] ?? 'outlined') {
                            'pink'  => 'bg-[#FCEBD8] text-[#B95B27]',
                            'green' => 'bg-[#EAF3DE] text-[#3B6D11]',
                            default => 'bg-[#F1DED4] text-[#B95B27]',
                        };
                    @endphp
                    <span class="inline-flex items-center font-poppins text-xs font-medium px-2.5 py-0.5 rounded-full {{ $tagClass }}">
                        {{ $tag['label'] }}
                    </span>
                @endforeach
            </div>
        @endif

        @if (($variant === 'product' || $variant === 'collection') && $views)
            <div class="absolute bottom-3 left-3 flex items-center gap-1.5">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" color="#94977B">
                    <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="white" stroke-width="1.3"/>
                    <circle cx="8" cy="8" r="2" stroke="white" stroke-width="1.3"/>
                </svg>
                <span class="text-sm text-[#94977B]">{{ $views }} views</span>
            </div>
        @endif

        @if ($variant === 'shop' && $logo)
            <div class="absolute top-[180px] left-6 w-[88px] h-[88px] rounded-[56px] border-4 border-[#FDFDFB] bg-[#FAF9F4] flex items-center justify-center overflow-hidden">
                <img src="{{ $logo }}" alt="{{ $name }} logo" class="w-full h-full object-contain">
            </div>
        @elseif ($variant === 'shop' && !$logo)
            <div class="absolute bottom-3 left-3 w-11 h-11 rounded-full bg-white border-2 border-white shadow-[0_1px_4px_rgba(0,0,0,0.15)] flex items-center justify-center font-poppins text-sm font-bold text-[#C06B3D]">
                {{ strtoupper(substr($name, 0, 2)) }}
            </div>
        @endif
    </div>

    <div class="{{ $variant === 'shop' ? 'flex flex-col flex-1 p-4 gap-1.5 mt-10' : 'flex flex-col flex-1 p-4 gap-1.5' }}">

        @if (($variant === 'product' || $variant === 'collection') && $vendor)
            <p class="text-base text-[#4D5223] leading-6">{{ $vendor }}</p>
        @endif

        <h3 class="font-poppins m-0">
            <a href="{{ $url }}" class="text-xl font-medium leading-7 text-[#0F172B] no-underline">{{ $name }}</a>
        </h3>

        @if ($variant === 'shop' && $tagline)
            <p class="text-xs text-[#8A8077] m-0">{{ $tagline }}</p>
        @endif

        @if ($variant === 'shop' && ($items || $sales))
            <div class="flex items-center gap-3">
                @if ($items) <span class="text-xs font-semibold text-[#C06B3D]">{{ $items }}</span> @endif
                @if ($sales) <span class="text-xs font-semibold text-[#C06B3D]">{{ $sales }}</span> @endif
            </div>
        @endif

        @if ($rating !== null)
            <div class="flex items-center gap-1.5">
                <div class="flex items-center gap-0.5">
                    @for ($s = 1; $s <= 5; $s++)
                        @if ($s <= $full)
                            <svg width="13" height="13" viewBox="0 0 16 16" fill="#E86A27"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/></svg>
                        @elseif ($hasHalf && $s === $full + 1)
                            <svg width="13" height="13" viewBox="0 0 16 16">
                                <defs><linearGradient id="kc-half-{{ $loop->index }}-{{ $s }}"><stop offset="50%" stop-color="#E86A27"/><stop offset="50%" stop-color="#D9CCA9"/></linearGradient></defs>
                                <path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z" fill="url(#kc-half-{{ $loop->index }}-{{ $s }})"/>
                            </svg>
                        @else
                            <svg width="13" height="13" viewBox="0 0 16 16" fill="#D9CCA9"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.4l-3.7 1.9.7-4.1-3-2.9 4.2-.7z"/></svg>
                        @endif
                    @endfor
                </div>
                @if ($rating) <span class="text-base text-[#0F172B]">{{ $rating }}</span> @endif
            </div>
        @endif

        @if (($variant === 'product' || $variant === 'collection') && $description)
            <p class="text-sm text-[#62748E] leading-[22px] line-clamp-2">
                {{ $description }}
            </p>
        @endif

        @if ($variant === 'product' && $price)
            <div class="flex items-baseline gap-2 mt-auto pt-1">
                <span class="text-lg text-[#0F172B]">{{ $price }}</span>
                @if ($old_price) <span class="text-xs text-[#90A1B9] line-through">{{ $old_price }}</span> @endif
            </div>
        @endif

        <div class="flex items-center justify-between gap-2 mt-4">
            @php
                $btnClass = match($variant) {
                    'product'    => 'border border-[#4D5223] bg-[#4D5223]/10 text-[#4D5223]',
                    'shop'       => 'border border-[#B95B27] bg-[#F8EFE9] text-[#B95B27]',
                    default      => 'border border-[#B95B27] bg-[#B95B27]/10 text-[#B95B27]',
                };
            @endphp

            <a
                href="{{ $url }}"
                class="inline-flex items-center justify-center gap-2 font-poppins flex-1 rounded-[64px] py-3.5 px-4 text-sm font-medium leading-4 no-underline {{ $btnClass }}"
            >
               @if ($variant === 'product')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.08337 11.875C7.08337 13.475 8.40004 14.7917 10 14.7917C11.6 14.7917 12.9167 13.475 12.9167 11.875" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.34162 1.66675L4.32495 4.69175" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.6583 1.66675L15.675 4.69175" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.66663 6.54167C1.66663 5 2.49163 4.875 3.51663 4.875H16.4833C17.5083 4.875 18.3333 5 18.3333 6.54167C18.3333 8.33333 17.5083 8.20833 16.4833 8.20833H3.51663C2.49163 8.20833 1.66663 8.33333 1.66663 6.54167Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M2.91663 8.33325L4.09163 15.5333C4.35829 17.1499 4.99996 18.3333 7.38329 18.3333H12.4083C15 18.3333 15.3833 17.1999 15.6833 15.6333L17.0833 8.33325" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                @elseif ($variant === 'collection')
                   <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
  <path d="M7.08337 11.875C7.08337 13.475 8.40004 14.7917 10 14.7917C11.6 14.7917 12.9167 13.475 12.9167 11.875" stroke="#B95B27" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M7.34162 1.66675L4.32495 4.69175" stroke="#B95B27" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M12.6583 1.66675L15.675 4.69175" stroke="#B95B27" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M1.66663 6.54167C1.66663 5 2.49163 4.875 3.51663 4.875H16.4833C17.5083 4.875 18.3333 5 18.3333 6.54167C18.3333 8.33333 17.5083 8.20833 16.4833 8.20833H3.51663C2.49163 8.20833 1.66663 8.33333 1.66663 6.54167Z" stroke="#B95B27" stroke-width="1.5"/>
  <path d="M2.91663 8.33325L4.09163 15.5333C4.35829 17.1499 4.99996 18.3333 7.38329 18.3333H12.4083C15 18.3333 15.3833 17.1999 15.6833 15.6333L17.0833 8.33325" stroke="#B95B27" stroke-width="1.5" stroke-linecap="round"/>
</svg>
                @endif
                {{ $ctaText }}
            </a>

            @if ($hasWishlist)
                <button
                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-full bg-[#FFF1F2]"
                    aria-label="@lang('shop::app.products.view.add-to-wishlist')"
                >
                   <img src="/images/favorite.svg" alt="" class="w-5 h-5">
                </button>
            @endif
        </div>
    </div>
</div>
