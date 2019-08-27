<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<table>
    <thead>
    <tr>
        <th>{{ trans('reports.integrated_at') }}</th>
        <th>{{ trans('reports.created_at') }}</th>
        <th>{{ trans('reports.client') }}</th>
        <th>{{ trans('reports.phone') }}</th>
        <th>{{ trans('reports.email') }}</th>
        <th>{{ trans('reports.adress') }}</th>
        <th>{{ trans('reports.client_id') }}</th>
        <th>{{ trans('reports.external_key') }}</th>
        <th>{{ trans('reports.tracking_number') }}</th>
        <th>{{ trans('reports.payment_method') }}</th>
        <th>{{ trans('reports.payout')}}</th>
        <th>{{ trans('reports.order_cost')}}</th>
        <th>{{ trans('reports.delivery_cost')}}</th>
        <th>{{ trans('reports.total')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td>
                {{ date('d.m.Y', strtotime($order['integrated_at'])) }} <br>
                {{ date('H:i', strtotime($order['integrated_at'])) }}
            </td>
            <td>
                {{ date('d.m.Y', strtotime($order['created_at'])) }} <br>
                {{ date('H:i', strtotime($order['created_at'])) }}
            </td>
            <td>
                {{ $order['name'] }} <br>
            </td>
            <td>
                {{ $order['phone'] }} <br>
            </td>
            <td>
                {{ $order['email'] }} <br>
            </td>
            <td>
                {{ $order['lead']['country']['title'] }}, {{ $order['info_array']['zipcode'] }}<br>
                {{ $order['info_array']['city'] }}, str. {{ $order['info_array']['street'] }},
                h. {{ $order['info_array']['house'] }}
                @if($order['info_array']['apartment']),
                flat {{ $order['info_array']['apartment'] }} @endif
            </td>
            <td>{{ $order['lead']['external_key'] }}</td>
            <td>{{ $order['id'] }}</td>
            <td>
                @if($order['tracking_number'])
                    {{ $order['tracking_number'] }}
                @else
                    {{ trans('reports.tracking_number_not_defined') }}
                @endif
            </td>
            <td>
                @if($order['lead']['payment_method'])
                    {{ $order['lead']['payment_method']['template']['title'] }}
                @endif
            </td>
            <td>{{ $order['lead']['payout'] + $order['lead']['profit'] }}{{ $order['lead']['currency']['sign'] }}</td>
            <td>{{ $order['info_array']['product_cost'] }} {{ $order['lead']['currency']['sign'] }}</td>
            <td>{{ $order['info_array']['delivery_cost'] }} {{ $order['lead']['currency']['sign'] }}</td>
            <td>{{ $order['info_array']['total_cost'] }} {{ $order['lead']['currency']['sign'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>