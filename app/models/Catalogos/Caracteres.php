<?php 
namespace App\Models\Catalogos;
use Illuminate\Database\Eloquent\Model;


class Caracteres extends Model {
     protected $primaryKey = 'idCaracter';
     protected $table = 'sia_catCaracteres';
     protected $fillable = ['siglas', 'nombre','usrAlta','fAlta','estatus'];
     public $timestamps = false;

 }
