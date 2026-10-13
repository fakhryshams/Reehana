<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_categories')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_categories AUTO_INCREMENT = 1'));

        $data = array(
            array(
                "id"                 => "1",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "dara",
                "show_on_homepage"   => "0",
                "sorting"            => "1",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "2",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "abaya",
                "show_on_homepage"   => "0",
                "sorting"            => "2",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "3",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "bisht",
                "show_on_homepage"   => "0",
                "sorting"            => "3",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "4",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "kaftan",
                "show_on_homepage"   => "0",
                "sorting"            => "4",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "5",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "dress",
                "show_on_homepage"   => "0",
                "sorting"            => "5",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "6",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "tops",
                "show_on_homepage"   => "0",
                "sorting"            => "6",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
            array(
                "id"                 => "7",
                "categories_image"   => null,
                "categories_icon"    => null,
                "parent_id"          => "0",
                "categories_slug"    => "skirts",
                "show_on_homepage"   => "0",
                "sorting"            => "7",
                "show_in_menu"       => "0",
                "is_active"          => "1",
                "min_order_quantity" => "1",
                "max_order_quantity" => "10",
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s"),
                "deleted_at"         => null,
                "created_by"         => "2",
                "updated_by"         => "2",
                "deleted_by"         => null
            ),
        );

        DB::table('admin_categories')->insert($data);

        DB::table('admin_categories_description')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_categories_description AUTO_INCREMENT = 1'));

        $data = array(
            array('id' => '1', 'categories_id' => '1', 'language_id' => '1', 'categories_name' => 'Dara', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:12:56', 'updated_at' => '2020-11-05 13:38:35', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => '2', 'deleted_by' => NULL),
            array('id' => '2', 'categories_id' => '2', 'language_id' => '1', 'categories_name' => 'Abaya', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:12:56', 'updated_at' => '2020-11-05 13:38:35', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => '2', 'deleted_by' => NULL),
            array('id' => '3', 'categories_id' => '3', 'language_id' => '1', 'categories_name' => 'Bisht', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:13:21', 'updated_at' => '2020-11-05 13:13:21', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => NULL, 'deleted_by' => NULL),
            array('id' => '4', 'categories_id' => '4', 'language_id' => '1', 'categories_name' => 'Kaftan', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:13:21', 'updated_at' => '2020-11-05 13:13:21', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => NULL, 'deleted_by' => NULL),
            array('id' => '5', 'categories_id' => '5', 'language_id' => '1', 'categories_name' => 'Dress', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:14:07', 'updated_at' => '2020-11-05 13:14:07', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => NULL, 'deleted_by' => NULL),
            array('id' => '6', 'categories_id' => '6', 'language_id' => '1', 'categories_name' => 'Tops', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:14:07', 'updated_at' => '2020-11-05 13:14:07', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => NULL, 'deleted_by' => NULL),
            array('id' => '7', 'categories_id' => '7', 'language_id' => '1', 'categories_name' => 'Skirts', 'categories_description' => NULL, 'is_active' => '1', 'created_at' => '2020-11-05 13:14:48', 'updated_at' => '2020-11-05 13:14:48', 'deleted_at' => NULL, 'created_by' => '2', 'updated_by' => NULL, 'deleted_by' => NULL),
        );

        DB::table('admin_categories_description')->insert($data);
    }

}
