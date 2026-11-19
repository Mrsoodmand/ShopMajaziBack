<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Local test data for the buyer review request (App\Services\OrderReviews): one test buyer with a delivered
 * subscription order and a delivered Apple ID order, so the review card on /account/orders/{id} can be tried.
 * Local development only; it refuses to run anywhere else.
 *
 *   php artisan db:seed --class=ReviewTestSeeder
 *   Sign in on the storefront with mobile 09000000101, password review-test-1234
 */
class ReviewTestSeeder extends Seeder
{
    public const MOBILE = '09000000101';
    public const PASSWORD = 'review-test-1234';

    public function run(): void
    {
        if (!app()->environment('local')) {
            $this->command?->error('ReviewTestSeeder only runs with APP_ENV=local.');
            return;
        }
        $User = User::where('mobile', self::MOBILE)->first() ?? new User();
        $User->name = 'خریدار آزمایشی';
        $User->mobile = self::MOBILE;
        $User->password = bcrypt(self::PASSWORD);
        $User->token ??= GenerateApiKey();
        $User->save();

        // A subscription on sale (Claude when there is one), as bought from /accounts/{slug}.
        $Product = Product::where('type', 'accounts')->where('status', 1)->orderByRaw("slug = 'buy-claude' desc")->orderByDesc('id')->first();
        if ($Product) {
            $this->order($User, 7, $Product->id, json_encode([
                ['filed_name' => 'email', 'filed_type' => 'ایمیل', 'filed_value' => 'buyer@example.test'],
                'plan' => ['plan_id' => null, 'plan_name' => 'یک‌ماهه'],
            ], JSON_UNESCAPED_UNICODE));
        }
        $this->order($User, 3, null, '1');
        $this->command?->info('Test buyer ' . self::MOBILE . ' with delivered orders ready.');
    }

    private function order(User $User, int $Type, ?int $ProductId, string $Detail): void
    {
        if (Order::where('user_id', $User->id)->where('type', $Type)->exists()) {
            return;
        }
        $Order = new Order();
        $Order->user_id = $User->id;
        $Order->type = $Type;
        $Order->price = 100000;
        $Order->status = 'complete';
        $Order->pay_status = 'Complete';
        $Order->product_id = $ProductId;
        $Order->save();
        $Item = new OrderItem();
        $Item->order_id = $Order->id;
        $Item->product_id = $ProductId;
        $Item->type = (string)$Type;
        $Item->price = '100000';
        $Item->detail = $Detail;
        $Item->save();
    }
}
