<?php

namespace App\Exports;

use App\Models\Delivery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeliveryDataExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;
    public function collection()
    {
        return Delivery::with([
            'customer:id,customer_name,customer_address,email,customer_phone_number',
            'sender:id,sender_name,sender_address,email,sender_phone'
        ])->get()->map(function ($delivery) {
            return [
                //'id' => $delivery->id,
                'order_number' => $delivery->order_number,
                'created_at' => Carbon::parse($delivery->created_at)->format('Y-m-d H:i:s'),
                'status_label' => $delivery->status_label,
                'customer_name' => $delivery->customer->customer_name,
                'customer_address' => $delivery->customer->customer_address,
                'sender_name' => $delivery->sender->sender_name,
                'sender_address' => $delivery->sender->sender_address,
            ];
        });
   
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Order Date',
            'Delivery Status',
            'Customer Name',
            'Customer Address',
            'Sender name',
            'Sender Address',
        ];
    }
}
