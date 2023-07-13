<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Person;
use App\Models\Catalog;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $identification_type = Catalog::where('type', 'tipos-identificacion')->where('value', 'Cédula')->first();
        $nationality = Catalog::where('type', 'tipos-nacionalidad')->where('value', 'Ecuatoriana')->first();

        $person = Person::create([
            'identification_type' => $identification_type->id,
            'identification' => '1111111111',
            'nationality' => $nationality->id,
            'names' => 'Asistente Web',
            'last_names' => 'FDJE Administrador',
        ]);

        $user = User::create([
            'email' => 'asistenteweb.fdje@gmail.com',
            'password' => Hash::make('Web2023@02.10'),
            'person' => $person->id,
        ]);
        $user->assignRole('Super Administrador');
    }
}
