<!DOCTYPE html>
<html>

<head>
    <title>Deliveries List</title>
    <style>
        table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 1px;
            text-align: left;
            font-size: 10px;
        }

        th {
            background-color: #f2f2f2;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
            word-break: break-all;
            white-space: normal;
        }
    </style>
</head>

<body>
    <h4>Deliveries List</h4>
    <table>
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Receiver Name</th>
                <th>Receiver Address</th>
                <th>Sender Name</th>
                <th>Sender Address</th>
                <!--th>Sender Phone</th-->
            </tr>
        </thead>
        <tbody>
            @foreach ($deliveries as $delivery)
            <tr>

                <td>{{ $delivery->order_number }}</td>
                <td>{{ $delivery->created_at }}</td>
                <td>{{ $delivery->status_label }}</td>
                <td>{{ $delivery->customer->customer_name }}</td>
                <td>{{ $delivery->customer->customer_address }}</td>
                <!--td>{{ $delivery->customer->customer_phone_number }}</td-->
                <td>{{ $delivery->sender->sender_name }}</td>
                <td>{{ $delivery->sender->sender_address }}</td>
                <!--td>{{ $delivery->sender->sender_phone }}</td-->
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>