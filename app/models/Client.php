<?php

/**
*
*/
class Client extends \HXPHP\System\Model
{
	public static function cadastrar($post='',$user_id='')
	{
		$callbackObj = new \stdClass;
		$callbackObj->cliente = '';
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$post_aux = array(
			'user_id' => $user_id
		);

		$post = array_merge($post, $post_aux);

		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->cliente = $cadastrar;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($post='', $cliente_id='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->cpf = $post['cpf'];
		$cliente->nome = $post['nome'];
		$cliente->email = $post['email'];
		$cliente->endereco = $post['endereco'];
		$cliente->celular = $post['celular'];
		$cliente->telefone = $post['telefone'];
		$cliente->recado = $post['recado'];
		$cliente->obs = $post['obs'];
		$cliente->companies_id = $post['companies_id'];
		$cliente->data_up = $post['data_up'];
		$cliente->user_up = $post['user_up'];
		$cliente->telefones = $post['telefones'];
		$cliente->enderecos = $post['enderecos'];
		$cliente->parentescos = $post['parentescos'];
		$cliente->status = $post['status'];

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function addArquivo($arquivo='', $cliente_id='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->arquivos = $arquivo;

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function transf($cliente_id='', $status='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->status = $status;

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function defcobrador($cobrador='', $cliente_id='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->cobrador = $cobrador;

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function protocolo($protocolo='', $cliente_id='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->protocolo = $protocolo;

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function user_boleto($user_boleto='', $cliente_id='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$cliente = self::find($cliente_id);

		$cliente->user_boleto = $user_boleto;

		$atualizar = $cliente->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function busca_cliente($cliente='')
	{
		return self::find('all',array('conditions' => array('companies_id = ?', $cliente), 'order' => 'data_cad desc'));
	}
}