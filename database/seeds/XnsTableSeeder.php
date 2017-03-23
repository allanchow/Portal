<?php


use App\Model\Xns\Xns;
use Illuminate\Database\Seeder;

class XnsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Xns::create([
            'domain_id' => 459221,
            'domain_name' => 'allcdn888.com',
            'api_key' => '5de55b595ce57e232ca0516a36fd3fee',
			'secret_key' => '0d9992bc00dc0374'
        ]);
        Xns::create([
            'domain_id' => 303561,
            'domain_name' => 'allbrightnetwork.com',
            'api_key' => '5de55b595ce57e232ca0516a36fd3fee',
			'secret_key' => '0d9992bc00dc0374'
        ]);
    }
}
