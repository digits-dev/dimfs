<?php

use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Please wait updating the data...');
        $data = [
            [
                'name' => 'Model',
                'path' => 'item_models',
                'table_name' => 'item_models',
                'controller' => 'AdminItemModelsController',
            ],
        ];

        foreach ($data as $k => $d) {

            $data[$k] += [
                'created_at' => date('Y-m-d H:i:s'),
                'icon' => 'fa fa-circle-o',
                'is_protected' => 0,
                'is_active' => 1
            ];

            if (DB::table('cms_moduls')->where('name', $d['name'])->count()) {
                DB::table('cms_moduls')->where('name', $d['name'])->update($data[$k]);
                unset($data[$k]);
            }
        }

        DB::table('cms_moduls')->insert($data);

        $this->command->info("Create submaster modules completed");
    }
}