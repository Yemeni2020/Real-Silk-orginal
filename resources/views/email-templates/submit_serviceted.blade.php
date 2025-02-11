<!DOCTYPE html>
<html>
<head>
    <title>New Order Submitted</title>
</head>
<body>
    <h2>New Order Received</h2>
    <p>A new order has been submitted. Here are the details:</p>

    <h4>Order ID: {{ $order->id }}</h4>
    <h4>Product: {{ $name_product }}</h4>
    <h4>Customer Information: </h4>
    <ul>
        <li>Name: {{$user["name"]}} ({{ $user["f_name"] }} {{ $user["l_name"] }})</li>
        <li>Email: {{ $user["email"] }}</li>
        <li>Phone: {{ $user["phone"] }}</li>
    </ul>

    <table border="1" cellpadding="10">
        <tr>
            <th>Field Name</th>
            <th>Value</th>
        </tr>
        @foreach($orderDetails as $detail)
        <tr>
            <td>{{ $detail['field'] }}</td>
            <td>{{ $detail['value'] }}</td>
        </tr>
        @endforeach
    </table>

    <p>Thank you.</p>
</body>
</html>
