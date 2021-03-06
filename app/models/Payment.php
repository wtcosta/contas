<?php

class Payment extends \HXPHP\System\Model
{
public static function cadastrar($post)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->pag = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		//Salva os dados no banco de dados
		$pag = self::create($post);

		if ($pag->is_valid()) {
			$callbackObj->pag = $pag;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $pag->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}