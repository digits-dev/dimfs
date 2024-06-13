<?php

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Please wait updating the data...');
        $submaster = DB::table('cms_menus')->where('name','Submaster')->first();
        $data = [
            [
                'name' => 'Model',
                'type' => 'Route',
                'path' => 'AdminItemModelsControllerGetIndex',
                'parent_id' => $submaster->id,
                'sorting' => 29
            ],
            [
                'name' => 'Brand Group',
                'type' => 'Route',
                'path' => 'AdminBrandGroupsControllerGetIndex',
                'parent_id' => $submaster->id,
                'sorting' => 30
            ],
            [
                'name' => 'Brand Direction',
                'type' => 'Route',
                'path' => 'AdminBrandDirectionsControllerGetIndex',
                'parent_id' => $submaster->id,
                'sorting' => 31
            ],
            [
                'name' => 'Brand Marketing',
                'type' => 'Route',
                'path' => 'AdminBrandMarketingsControllerGetIndex',
                'parent_id' => $submaster->id,
                'sorting' => 32
            ],
        ];

        foreach ($data as $k => $d) {
            $data[$k] += [
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'Route',
                'icon' => 'fa fa-circle-o',
                'is_dashboard' => 0,
                'is_active' => 1,
                'id_cms_privileges' => 1,
            ];

            if (DB::table('cms_menus')->where('name', $d['name'])->count()) {
                DB::table('cms_menus')->where('name', $d['name'])->update($data[$k]);
                unset($data[$k]);
            }
        }

        DB::table('cms_menus')->insert($data);

        $this->command->info("Create menu completed");
    }
}
