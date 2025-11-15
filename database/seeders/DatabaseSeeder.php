<?php

// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Specialty,Doctor,DoctorSchedule,User};
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // Create test user
        User::firstOrCreate(
            ['email' => 'cristian.piedrahita22118@ucaldas.edu.co'],
            [
                'name' => 'Cristian Piedrahita',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        $psi = Specialty::firstOrCreate(['slug'=>'psiquiatria'],['name'=>'Psiquiatría']);
        Doctor::factory()->count(3)->create(['specialty_id'=>$psi->id])->each(function($d){
            foreach(range(1,5) as $w){
                DoctorSchedule::create([
                    'doctor_id'=>$d->id,'weekday'=>$w,'start_time'=>'08:00','end_time'=>'12:00'
                ]);
                DoctorSchedule::create([
                    'doctor_id'=>$d->id,'weekday'=>$w,'start_time'=>'14:00','end_time'=>'18:00'
                ]);
            }
        });
    }
}
