<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\OrderAddressType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with(['addresses', 'products'])->get();
    }

    public function store(Request $request)
    {
        try {
            $parameter = $request->all();
            $validator = $this->createValidator($parameter);

            if ($validator->fails()) {
                $errors = ['errors' => $validator->errors()->toArray()];
                $errorMessage = 'Validation Error';

                return $this->errorResponse(400, $errors, $errorMessage);
            }

            DB::beginTransaction();

            $createOrder['first_name'] = Arr::get($parameter, 'first_name');
            $createOrder['last_name'] = Arr::get($parameter, 'last_name');
            $createOrder['email'] = Arr::get($parameter, 'email');
            $createOrder['phone'] = Arr::get($parameter, 'phone');
            $createOrder['summary_price'] = 0;
            $createdOrder = Order::create($createOrder);

            $addresses[OrderAddressType::SHIPPING] = Arr::get($parameter, 'shipping_address');
            $addresses[OrderAddressType::RECEIPT] = Arr::get($parameter, 'receipt_address');

            foreach ($addresses as $key => $address) {
                $createdOrder->addresses()->create([
                    'type' => $key,
                    'address' => $address
                ]);
            }

            $products = Product::whereIn('id', Arr::get($parameter, 'products'))->where('is_active', 1)->get();


            $summary_price = 0;
            foreach ($products as $key => $product) {
                $createdOrder->products()->attach($product->id, ['price' => $product->price]);
                $summary_price += $product->price;
            }

            $createdOrder->update([
                'summary_price' => $summary_price
            ]);

            DB::commit();

            return $createdOrder;
        } catch (\Throwable $th) {
            DB::rollback();

            throw $th;
        }
    }

    protected function createValidator(array $parameter)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^\d{9,10}(#[\w\d]{1,11})?$/',
            'shipping_address' => 'required|string|max:255',
            'receipt_address' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*' => 'required|integer|min:1',
        ];

        $customMessages = [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'ต้องเป็นอีเมลเท่านั้น',
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.regex' => 'เบอร์โทรศัทพ์ไม่ถูกต้อง 0999999999 หรือ 029999999#11',
            'shipping_address.required' => 'กรุณากรอกที่อยู่สำหรับจัดส่ง',
            'receipt_address.required' => 'กรุณากรอกที่อยู่สำหรับการออกใบเสร็จ',
            'products.required' => 'กรุณาเพิ่มสินค้า',
            'products.*.integer' => 'รหัสสินค้าต้องเป็นตัวเลขเท่านั้น',
        ];

        return Validator::make($parameter, $rules, $customMessages);
    }
}
