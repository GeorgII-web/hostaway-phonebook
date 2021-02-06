<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory(20)->create();

        // Const auth token for testing
        $user = User::find(1);
        $user->api_token = '24f56647eddc650bd0904883dd7168e609017696cf69714fe7d1224012491710';

        $user->save();
    }
}
