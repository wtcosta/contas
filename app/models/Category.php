<?php

class Category extends \HXPHP\System\Model
{
	public static function cadastrar($post)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->cad = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		//Salva os dados no banco de dados
		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->cad = $cadastrar;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($cad_id, array $post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->cad = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cad = self::find($cad_id);
		$cad->nome = $post['nome'];

		$atualizar = $cad->save(false);

		if ($atualizar) {
			$callbackObj->cad = $cad;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}