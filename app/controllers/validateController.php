<?php  
namespace App\Controllers;

use Respect\Validation\Validator as v;


class ValidateController {

	public function string($string){

		$errors = [];
		$res = v::alpha()->validate($string);
		if(!$res){
			$errors[$campo] = 'El Campo no puede estar vacio y/o solo acepta caracteres';
		}
	}
}

?>