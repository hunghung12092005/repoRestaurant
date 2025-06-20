<?php 
   namespace App\Models;

   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Foundation\Auth\User as Authenticatable;
   use Illuminate\Notifications\Notifiable;
   use Tymon\JWTAuth\Contracts\JWTSubject;
   
   class User extends Authenticatable implements JWTSubject
   {
       use HasFactory, Notifiable;
   
       protected $fillable = [
           'name',
           'email',
           'password',
           'role',
           'qr_code',
           'turnstileResponse',
       ];
   
       protected $hidden = [
           'password',
           'remember_token',
       ];
   
       protected $casts = [
           'email_verified_at' => 'datetime',
           'password' => 'hashed',
       ];
   
       // Implement methods from JWTSubject
       public function getJWTIdentifier()
       {
           return $this->getKey();
       }
   
       public function getJWTCustomClaims()
       {
           return [];
       }
   } 
?>