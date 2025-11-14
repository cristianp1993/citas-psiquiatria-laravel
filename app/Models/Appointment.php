<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model {
    protected $fillable = ['uuid','doctor_id','patient_name','patient_email','patient_phone','start_at','end_at','status','slug'];
    protected $casts = ['start_at'=>'datetime','end_at'=>'datetime'];
    protected static function booted(){
        static::creating(function($m){
            $m->uuid = (string) Str::uuid();
            $m->slug = Str::slug($m->uuid);
        });
    }
    public function doctor(){ return $this->belongsTo(Doctor::class); }
    public function scopePending($q){ return $q->where('status','pending'); }
    public function scopeConfirmed($q){ return $q->where('status','confirmed'); }
    public function getRouteKeyName(){ return 'slug'; }
}
