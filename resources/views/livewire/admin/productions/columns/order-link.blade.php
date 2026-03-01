<a href="{{ route('orders.edit', ['order' => $row->order_id]) }}" class="text-brand-600 hover:underline">
    {{ $row->order_no ?: '-' }}
</a>
