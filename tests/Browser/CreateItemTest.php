<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateItemTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            //login
            $browser->maximize();
            $browser->visit('/admin/login')
                ->type('email', 'mikerodelas@digits.ph')
                ->type('password', 'admin8mike')
                ->click('button[type="submit"]')
                ->assertPathIs('/digits-imfsv3/public/admin');
            //create item
            $browser->visit('/admin/item_masters/add')
                ->assertSee('Add Item Master')
                ->type('upc_code', 'TST114')
                ->type('supplier_item_code', 'TST115')
                ->select('brands_id')
                ->pause(1000)
                ->select('vendors_id')
                ->pause(1000)
                ->select('vendor_groups_id')
                ->select('categories_id')
                ->pause(1000)
                ->select('classes_id')
                ->pause(1000)
                ->select('subclasses_id')
                ->pause(1000)
                ->select('margin_categories_id')
                ->pause(1000)
                ->select('store_categories_id')
                ->pause(1000)
                ->select('warehouse_categories_id')
                ->type('model', 'apple mini x')
                ->select('model_specifics_id')
                ->pause(1000)
                ->select('colors_id')
                ->pause(1000)
                ->type('actual_color', 'space gray')
                ->type('size_value', '128')
                ->select('sizes_id')
                ->select('uoms_id','1')
                ->select('inventory_types_id','2')
                ->select('sku_legends_id','2')
                ->type('original_srp', '39990.00')
                ->type('moq', '10')
                ->select('currencies_id')
                ->pause(1000)
                ->type('purchase_price', '850')
                ->check('serialized[]','1')
                ->check('serialized[]','2')
                ->select('btb_segmentation','2')
                ->select('dw_segmentation','2')
                ->click('input[value="Save"]')
                ->pause(2000);
        });
    }
}
