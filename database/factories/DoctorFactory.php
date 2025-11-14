<?php

namespace Database\Factories;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DoctorFactory extends Factory {
    protected $model = Doctor::class;
    public function definition(){
        $name = $this->faker->name();
        return [
            'name'=>$name,
            'slug'=>Str::slug($name.'-'.Str::random(5)),
            'email'=>$this->faker->safeEmail(),
            'is_active'=>true
        ];
    }
}
