<table id="datatable"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
    <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{ translate('SL') }}</th>
            <th>{{ translate('Shop_Name') }}</th>
            <th class="text-center">{{ translate('Vendor_Name') }}</th>
            <th class="text-center">{{ translate('Contact_info') }}</th>
            <th class="text-center">{{ translate('Status') }}</th>
            <th class="text-center">{{ translate('Total_products') }}</th>
            <th class="text-center">{{ translate('action') }}</th>
        </tr>
    </thead>
    <tbody >
    @foreach($sellers as $seller)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $seller->shop_name }}</td>
            <td class="text-center">{{ $seller->name }}</td>
            <td class="text-center">{{ $seller->email }}</td>
            <td class="text-center">{{ $seller->status }}</td>
            <td class="text-center">{{ $seller->products_count }}</td>
            <td class="text-center"><a href="{{route('admin.vendors.view',$seller->id)}}">View</a></td>
        </tr>
    @endforeach                    
</tbody>
</table>
