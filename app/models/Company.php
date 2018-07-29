<?php

/**
*
*/
class Company extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('role')
	);

	static $validates_presence_of = array(
		array(
			'empresa',
			'message' => 'O nome da empresa é obrigatório.'
		),
		array(
			'email',
			'message' => 'O e-mail é um campo obrigatório.'
		),
		array(
			'cnpj',
			'message' => 'O CNPJ é um campo obrigatório.'
		)
	);

	public static function cadastrar($post, $user_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->emp = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
			'user_id' => $user_id,
			'juros' => str_replace(",", ".", $post['juros']),
			'multa' => str_replace(",", ".", $post['multa'])
		);

		unset($post['juros']);
		unset($post['multa']);

		$post = array_merge($post, $userCad);

		//Salva os dados no banco de dados
		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->emp = $cadastrar;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($post, $emp_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->emp = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$emp = self::find($emp_id);

		if ($emp->email != $post['email']) {
			$exists_mail = self::find_by_email($post['email']);
			if (!is_null($exists_mail) && intval($emp_id) !== intval($exists_mail->id)) {
				array_push($callbackObj->errors, 'Já existe uma empresa com este e-mail cadastrado. Por favor, escolha outro e tente novamente');
				return $callbackObj;
			}
		}

		if ($emp->cnpj !== $post['cnpj']) {
			$exists_cnpj = self::find_by_cnpj($post['cnpj']);
			if (!is_null($exists_cnpj) && intval($emp_id) !== intval($exists_cnpj->id)) {
				array_push($callbackObj->errors, 'Já existe uma empresa com este CNPJ cadastrado. Por favor, escolha outro e tente novamente');
				return $callbackObj;
			}
		}

		$emp->cnpj = $post['cnpj'];
		$emp->gerente = $post['gerente'];
		$emp->empresa = $post['empresa'];
		$emp->contato = $post['contato'];
		$emp->email = $post['email'];
		$emp->endereco = $post['endereco'];
		$emp->telefone = $post['telefone'];
		$emp->celular = $post['celular'];
		$emp->multa = str_replace(",", ".", $post['multa']);
		$emp->juros = str_replace(",", ".", $post['juros']);
		$emp->iduserempresa = @$post['iduserempresa'];
		$emp->data_up = $post['data_up'];
		$emp->tipo_conta = $post['tipo_conta'];
		$emp->banco = $post['banco'];
		$emp->agencia = $post['agencia'];
		$emp->conta = $post['conta'];
		$emp->autorizacao_terceiro = @$post['autorizacao_terceiro'];

		$atualizar = $emp->save(false);

		if ($atualizar) {
			$callbackObj->emp = $emp;
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