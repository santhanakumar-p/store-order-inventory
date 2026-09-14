<x-mail::message>
# Order Confirmation

Hi {{ $order->customer->name }},

Thank you for your order. Here are the details:

**Order ID:** {{ $order->id }}
**Customer:** {{ $order->customer->name }} ({{ $order->customer->email }})

<x-mail::table>
| Item | Qty | Unit Price | Tax Rate | Line Subtotal | Line Tax | Line Total |
|:-----|----:|-----------:|---------:|--------------:|---------:|-----------:|
@foreach ($order->items as $item)
| {{ $item->product?->name ?? 'Product #'.$item->product_id }} ({{ $item->product?->sku }}) | {{ $item->qty }} | {{ number_format((float) $item->unit_price, 2) }} | {{ number_format((float) $item->tax_rate, 2) }}% | {{ number_format((float) $item->line_subtotal, 2) }} | {{ number_format((float) $item->line_tax_amount, 2) }} | {{ number_format((float) $item->line_grand_total, 2) }} |
@endforeach
</x-mail::table>

**Subtotal:** {{ number_format((float) $order->subtotal, 2) }}
**Tax Amount:** {{ number_format((float) $order->tax_amount, 2) }}
**Grand Total:** {{ number_format((float) $order->grand_total, 2) }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
