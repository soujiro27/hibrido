<?php  
namespace App\Controllers;

use Respect\Validation\Validator as v;


class ValidateController {

	public function string($string,$campo,$max){

		$errors  = array();
		$res = v::alpha()->validate($string);
		if(!$res){
			
			$errors['campo'] = $campo;
			$errors['message'] = 'El Campo no puede estar vacio y/o solo acepta letras';
			
		} elseif (!v::length(1,$max)->validate($string)) {
			
			$errors['campo'] = $campo;
			$errors['message'] = 'El Campo no puede contener mas de: '.$max.' caracteres';
			
		}

		return $errors;
	}
}

?>