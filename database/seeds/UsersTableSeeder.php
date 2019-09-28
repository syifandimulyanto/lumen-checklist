<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $check = User::where('email', 'fanfandi17@gmail.com')->first();
        if ($check) return;

        $user = new User();
        $user->name = 'Syifandi Mulyanto';
        $user->email = 'fanfandi17@gmail.com';
        $user->password = Hash::make('123456');
        $user->save();
    }
}
