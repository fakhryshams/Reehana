<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
                [
                    UserSeeder::class,
                    RoleSeeder::class,
                    MenuLevelSeeder::class,
                    SettingSeeder::class,
                    LanguageSeeder::class,
                    ColorSeeder::class,
                    UnitSeeder::class,
                    CategorySeeder::class,
                    AlertSettingsSeeder::class,
                    CurrencySeeder::class,
                    OrderStatusSeeder::class,
                    PaymentMethodSeeder::class,
                    HomeCategorySeeder::class
                ]
        );
    }

}
