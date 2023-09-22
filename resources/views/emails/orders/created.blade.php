<div>
    <p>Order: {{ $order->order_number }}</p>
    <p>Customer: {{ $order->first_name }} {{ $order->last_name }}</p>
    <p>Phone: {{ $order->phone }}</p>
    <p>Email: {{ $order->email }}</p>
    <p>Summary Price: {{ $order->summary_price }}</p>
</div>
