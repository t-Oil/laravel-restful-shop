<?php

namespace Tests\Feature\Controller\Api\Public\OrderController;

use App\Models\Product;
use Database\Factories\ProductFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderStoreTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @var string
     */
    private string $route = 'api.public.order';

    private $products = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = Product::factory(20)->create([
            'created_by' => 1
        ]);
    }

    public function testWithEmptyRequestShouldBeFails(): void
    {
        $response = $this->post(route($this->route . '.store'));

        $this->assertEquals(400, $response->json('status'));

        $response->assertJson(
            [
                'error' => [
                    'errors' => [
                        'first_name' => [
                            'กรุณากรอกชื่อ',
                        ],
                        'last_name' => [
                            'กรุณากรอกนามสกุล',
                        ],
                        'email' => [
                            'กรุณากรอกอีเมล',
                        ],
                        'phone' => [
                            'กรุณากรอกเบอร์โทรศัพท์',
                        ],
                        'shipping_address' => [
                            'กรุณากรอกที่อยู่สำหรับจัดส่ง',
                        ],
                        'receipt_address' => [
                            'กรุณากรอกที่อยู่สำหรับการออกใบเสร็จ',
                        ],
                        'products' => [
                            'กรุณาเพิ่มสินค้า',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testWithProductsIsNotArrayShouldBeFails()
    {
        $params = [
            'first_name' => 'name',
            'last_name' => 'lastname',
            'phone' => '0999999999',
            'email' => 'email@example.com',
            'shipping_address' => '111/2 Ram Intra Rd',
            'receipt_address' => '111/2 Ram Intra Rd',
            'products' => "Wrong type of product",
        ];

        $response = $this->post(route($this->route . '.store'), $params);

        $this->assertEquals(400, $response->json('status'));

        $response->assertJson(
            [
                'error' => [
                    'errors' => [
                        'products' => [
                            'รูปแบบสินค้าผิดพลาด',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testWithPhoneIsWrongRegexShouldBeFails()
    {
        $params = [
            'first_name' => 'name',
            'last_name' => 'lastname',
            'phone' => '0999999xx',
            'email' => 'email@example.com',
            'shipping_address' => '111/2 Ram Intra Rd',
            'receipt_address' => '111/2 Ram Intra Rd',
            'products' => [1],
        ];

        $response = $this->post(route($this->route . '.store'), $params);

        $this->assertEquals(400, $response->json('status'));

        $response->assertJson(
            [
                'error' => [
                    'errors' => [
                        'phone' => [
                            'เบอร์โทรศัทพ์ไม่ถูกต้อง 0999999999 หรือ 029999999#11',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testWithValidDataShouldBeOk()
    {
        $params = [
            'first_name' => 'name',
            'last_name' => 'lastname',
            'phone' => '0999999999',
            'email' => 'email@example.com',
            'shipping_address' => '111/2 Ram Intra Rd',
            'receipt_address' => '111/2 Ram Intra Rd',
            'products' => [1, 2, 3],
        ];

        $response = $this->post(route($this->route . '.store'), $params);

        $this->assertEquals(200, $response->json('status'));
        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'order_number' =>  $response->json('data.order_number')
        ]);
    }


    public function testWithSomeProductIdsNotFoundShouldBeOk()
    {
        $params = [
            'first_name' => 'name',
            'last_name' => 'lastname',
            'phone' => '0999999999',
            'email' => 'email@example.com',
            'shipping_address' => '111/2 Ram Intra Rd',
            'receipt_address' => '111/2 Ram Intra Rd',
            'products' => [1, 2, 4, 999999],
        ];

        $response = $this->post(route($this->route . '.store'), $params);

        $getOrder = $this->get(route($this->route . '.show', ['order_number' => $response->json('data.order_number')]));

        $this->assertEquals(200, $response->json('status'));
        $this->assertCount(3, $getOrder->json('data.products'));
    }

    public function testRunningOrderNumberWithValidDataShouldBeOk()
    {
        $params = [];
        for ($i = 1; $i <= 2; $i++) {
            array_push($params, [
                'first_name' => 'name' . $i,
                'last_name' => 'lastname' . $i,
                'phone' => '0999999999',
                'email' => 'email' . $i . '@example.com',
                'shipping_address' => '111/2 Ram Intra Rd',
                'receipt_address' => '111/2 Ram Intra Rd',
                'products' => [1, 2, 3],
            ]);
        }

        $firstOrder = $this->post(route($this->route . '.store'), $params[0]);
        $secondOrder = $this->post(route($this->route . '.store'), $params[1]);

        $this->assertEquals(200, $firstOrder->json('status'));
        $this->assertEquals(200, $secondOrder->json('status'));
        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'order_number' =>  $firstOrder->json('data.order_number')
        ]);
        $this->assertEquals('ORD-00002', $secondOrder->json('data.order_number'));
    }
}
