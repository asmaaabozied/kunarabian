@foreach ($sellerOrder->shipments as $sellerShipment)
    @php
        $shipment = $sellerShipment->shipment;
        $sellerGenerateLabelEnabled = (bool) config('kun-shipping.tracking.seller_generate_label_enabled', true);
        $labelUrl = $shipment->label_url;
    @endphp

    <div class="flex items-center justify-between">
        <div class="flex max-sm:flex-wrap">
            <p class="text-base font-medium">
                @lang('marketplace::app.seller.orders.view.shipments.individual-shipment', ['track_number' => $shipment->track_number]),
            </p>&nbsp;
            <p class="text-base font-medium">
                @lang('marketplace::app.seller.orders.view.shipments.created-on', ['date_time' => core()->formatDate($shipment->created_at, 'd/m/y h:i:s')])
            </p>
        </div>
    </div>

    <div class="relative mt-6 overflow-x-auto rounded-xl border">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-[#F5F5F5] text-sm text-black">
                <tr>
                    <th scope="col" class="px-6 py-4 font-medium">
                        @lang('marketplace::app.seller.orders.view.shipments.name')
                    </th>
                    <th scope="col" class="px-6 py-4 font-medium">
                        @lang('marketplace::app.seller.orders.view.shipments.qty')
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach ($shipment->items as $item)
                    <tr class="border-b bg-white">
                        <td class="px-6 py-4 font-medium text-black">
                            <div class="flex flex-col">
                                <p class="text-sm font-medium">{{ $item->name }}</p>
                                <p class="text-sm font-normal">
                                    @lang('marketplace::app.seller.orders.view.shipments.sku', ['sku' => $item->sku])
                                </p>
                            </div>
                        </td>

                        <td class="px-6 py-4 font-medium text-black">
                            {{ $item->qty }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-8">
        <table class="text-left text-sm">
            <tr>
                <td class="text-sm">@lang('marketplace::app.seller.orders.view.shipments.inventory-source')</td>
                <td class="px-2.5 text-sm">-</td>
                <td class="text-sm">{{ $shipment->inventory_source_name }}</td>
            </tr>

            <tr>
                <td class="text-sm">@lang('marketplace::app.seller.orders.view.shipments.carrier-title')</td>
                <td class="px-2.5 text-sm">-</td>
                <td class="text-sm">{{ $shipment->carrier_title }}</td>
            </tr>

            <tr>
                <td class="text-sm">@lang('marketplace::app.seller.orders.view.shipments.tracking-number')</td>
                <td class="px-2.5 text-sm">-</td>
                <td class="text-sm">{{ $shipment->track_number }}</td>
            </tr>

            @if ($sellerGenerateLabelEnabled)
                {!! view_render_event('kun.shipping.seller.orders.view.shipments.generate_label.before', ['shipment' => $shipment]) !!}

                <tr>
                    <td class="text-sm">Generate Label</td>
                    <td class="px-2.5 text-sm">-</td>
                    <td class="text-sm">
                        @if (! empty($labelUrl))
                            <a
                                href="{{ $labelUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="secondary-button px-3 py-1.5 text-xs"
                            >
                                Open Label (PDF)
                            </a>
                        @else
                            <span class="text-gray-500">Label not available yet</span>
                        @endif
                    </td>
                </tr>

                {!! view_render_event('kun.shipping.seller.orders.view.shipments.generate_label.after', ['shipment' => $shipment]) !!}
            @endif
        </table>
    </div>
@endforeach

