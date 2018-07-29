<?php

class ClienteController extends \HXPHP\System\Controller
{
	public function __construct($configs)
	{
		parent::__construct($configs);

		$this->load(
			'Services\Auth',
			$configs->auth->after_login,
			$configs->auth->after_logout,
			true
		);

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if ($role->role == 'cliente') {
			$cliente = Client::find(array('conditions' => array('idUserCliente = ?', $user_id)));
			self::filtrarAction($cliente->id);
		}elseif ($role->role == 'empresa') {
			$empresa = Company::find(array('conditions' => array('idUserEmpresa = ?', $user_id)));
			self::empresaAction($empresa->id);
		}elseif ($role->role == 'administrator') {
			$this->view
			->setFile('admin')
			->setVars([
				'clientes' => Client::find('all', array('order' => 'data_cad desc'))
			]);
		}elseif ($role->role == 'cobrança') {
			$this->view
			->setVars([
				'clientes' => Client::find(
					'all',
					array(
						'conditions' => array('cobrador = ?', $user_id),
						'order' => 'data_cad asc'
					)
				)
			]);
		}elseif ($role->role == 'extrajudicial') {
			$this->view
			->setFile('extra')
			->setVars([
				'clientes' => Client::find(
					'all',
					array(
						'conditions' => array('status = 22'),
						'order' => 'data_cad asc'
					)
				)
			]);
		}elseif ($role->role == 'juridico') {
			$this->view
			->setFile('juridico')
			->setVars([
				'clientes' => Client::find(
					'all',
					array(
						'conditions' => array('status = 29'),
						'order' => 'data_cad asc'
					)
				)
			]);
		}else{
			$this->view
			->setVars([
				'clientes' => Client::all()
			]);
		}
	}

	public function resultAction($msn='')
	{
		$this->view->setFile('index');

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		if ($msn != '') {
			if ($msn == 'erro0') {
				$this->load('Helpers\Alert', array(
					'danger',
					'Selecione a cobradora!',
					''
				));
			}
			if ($msn == 'erro1') {
				$this->load('Helpers\Alert', array(
					'danger',
					'Selecione ao menos uma dívida!',
					''
				));
			}
			if ($msn == 'erro2') {
				$this->load('Helpers\Alert', array(
					'danger',
					'Erro ao enviar alguma dívida para o cobrador, verifique com o administrador!',
					''
				));
			}
		}else{
			if ($role->role == 'administrator') {
				$this->view->setFile('admin');
				$this->load('Helpers\Alert', array(
					'success',
					'Ateração realizada!',
					''
				));
			}elseif ($role->role == 'cobrança') {
				$this->load('Helpers\Alert', array(
					'success',
					'Ateração realizada!',
					''
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Alterações realizadas!',
					''
				));
			}
		}
	}

	public function cadastrarAction($empresa='')
	{
		//Redireciona para uma view
		if ($empresa != '') {
			$this->view
			->setFile('cadastrar')
			->setVars([
				'empresa' => $empresa
			]);
		}else{
			$this->view->setFile('cadastrar');
		}

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post) && $post['cpf'] !== null) {

			$user_id = $this->auth->getUserId();

			$cadCliente = Client::cadastrar($post, $user_id);

			$arquivos = $_FILES['arquivos'];
			$qtdArq = count($arquivos['name']);

			if (count($qtdArq)>0) {
				$arquivosBd = array();
				for ($i=0; $i < $qtdArq; $i++) {
					$extencao = explode(".", $arquivos['name'][$i]);
					$arquivoFull = array(
						'name' => $arquivos['name'][$i],
						'type' => $arquivos['type'][$i],
						'tmp_name' => $arquivos['tmp_name'][$i],
						'error' => $arquivos['error'][$i],
						'size' => $arquivos['size'][$i]
					);
					$uploadUserImage = new upload($arquivoFull);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = end($extencao);
						$uploadUserImage->resize = false;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'clients' . DS . $cadCliente->cliente->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed === true) {
							$uploadUserImage->clean();
							$arquivosBd[]['arquivo'] = $image_name.".".end($extencao);
							$arquivosBd[]['nome'] = $arquivoFull['name'];
						}else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar os arquivos',
								$uploadUserImage->error
							));
						}
					}
				}

				$atualizaBd = Client::addArquivo(json_encode($arquivosBd), $cadCliente->cliente->id);

				if ($atualizaBd->status == false) {
					$this->load('Helpers\Alert', array(
						'danger',
						'Ops! Não foi possível armazenar os arquivos. <br> Informe o erro abaixo ao administrador:',
						$atualizaBd->errors
					));
				}
			}

			if ($cadCliente->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadCliente->errors
				));
				return;
			}else{
				if ($empresa != '') {
					$this->redirectTo($this->configs->baseURI.'cliente/empresa/'.$empresa);
				}else{
					$this->redirectTo($this->configs->baseURI.'cliente/result');
				}
			}
		}
	}

	public function editarAction($cliente='')
	{
		$clienteData = Client::find_by_id($cliente);

		$this->view
		->setFile('cadastrar')
		->setVars([
			'cliente' => $clienteData,
			'idCliente' => $clienteData->idusercliente
		]);

		$user_id = $this->auth->getUserId();

		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		if (!empty($post)) {

			$arquivos = $_FILES['arquivos'];
			$qtdArq = count($arquivos['name']);

			if (count($qtdArq)>0) {
				$arquivosBd = array();
				for ($i=0; $i < $qtdArq; $i++) {
					$extencao = explode(".", $arquivos['name'][$i]);
					$arquivoFull = array(
						'name' => $arquivos['name'][$i],
						'type' => $arquivos['type'][$i],
						'tmp_name' => $arquivos['tmp_name'][$i],
						'error' => $arquivos['error'][$i],
						'size' => $arquivos['size'][$i]
					);
					$uploadUserImage = new upload($arquivoFull);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = end($extencao);
						$uploadUserImage->resize = false;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'clients' . DS . $cliente . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed === true) {
							$uploadUserImage->clean();
							$arquivosBd[] = array("nome"=>$arquivoFull['name'], "arquivo"=>$image_name.".".end($extencao));
						}else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar os arquivos',
								$uploadUserImage->error
							));
						}
					}
				}

				$arquivosBdAntigo = json_decode($clienteData->arquivos);
				if (count($arquivosBdAntigo) > 0) {
					$arquivosBdNovo = array_merge($arquivosBdAntigo, $arquivosBd);
				}else{
					$arquivosBdNovo = $arquivosBd;
				}
				$atualizaBd = Client::addArquivo(json_encode($arquivosBdNovo), $cliente);

				if ($atualizaBd->status == false) {
					$this->load('Helpers\Alert', array(
						'danger',
						'Ops! Não foi possível armazenar os arquivos. <br> Informe o erro abaixo ao administrador:',
						$atualizaBd->errors
					));
				}
			}

			$edit = array(
				'data_up' => date('Y-m-d h:i:s'),
				'user_up' => $user_id
			);

			$post = array_merge($post, $edit);

			$atualizaCliente = Client::atualizar($post, $cliente);

			if ($atualizaCliente->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaCliente->errors
				));
			}else{
				$this->redirectTo($this->configs->baseURI.'cliente/result');
			}
		}
	}

	public function buscaAction($busca='')
	{
		$dados = $this->request->post();
		if ($dados == "") {
			$this->redirectTo($this->configs->baseURI.'cliente');
		}else{
			if ($dados['nome'] != "") {
				$condition = 'nome LIKE CONCAT("%", ? ,"%")';
				$busca = $dados['nome'];
			}
			if ($dados['cpf'] != "") {
				$condition = 'cpf = ?';
				$busca = $dados['cpf'];
			}
			$this->view
			->setFile('index')
			->setVars([
				'clientes' => Client::find(
					'all',
					array(
						'conditions' => array($condition, $busca)
					)
				)
			]);
		}
	}

	public function enviarDividaAction($value='')
	{
		//Remove filtro de string array dividas
		$this->request->setCustomFilters([
			'dividas' => [
				'filter' => FILTER_SANITIZE_NUMBER_INT,
				'flags' => FILTER_FORCE_ARRAY
			]
		]);

		//retorna dados do form
		$dados = $this->request->post();

		//valida os dados do form
		if (count($dados['dividas'])==0) {
			$this->redirectTo($this->configs->baseURI.'cliente/result/erro1');
		}
		if (count($dados['cobradora'])=='') {
			$this->redirectTo($this->configs->baseURI.'cliente/result/erro0');
		}

		$erro = false;
		foreach ($dados['dividas'] as $value) {
			$atualiza = Client::defcobrador($dados['cobradora'], $value);
			if ($atualiza->status == false) {
				$erro = true;
			}
		}
		if ($erro == true) {
			$this->redirectTo($this->configs->baseURI.'cliente/result/erro2');
		}else{
			$this->redirectTo($this->configs->baseURI.'cliente/result');
		}
	}

	public function filtrarAction($cliente='', $msn='')
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente)
		]);

		if ($msn != "") {
			$this->load('Helpers\Alert', array(
				'success',
				'Dívida excluida com sucesso!'
			));
		}
	}

	public function empresaAction($empresa="")
	{
		$post = $this->request->post();
		if (!is_null($post['empresa'])) {
			$empresa = $post['empresa'];
		}
		$this->view
		->setFile('admin')
		->setVars([
			'clientes' => Client::find('all',array('conditions' => array('companies_id = ?', $empresa),'order' => 'data_cad desc')),
			'empresa' => $empresa
		]);
	}

	public function cadLogAction($cliente)
	{
		//Remove filtro de string array dividas
		$this->request->setCustomFilters([
			'transf' => [
				'filter' => FILTER_SANITIZE_NUMBER_INT,
				'flags' => FILTER_FORCE_ARRAY
			]
		]);

		//Recupera dados do POST
		$post = $this->request->post();

		$tipo = $post['tipo'];

		if (isset($post['protocolo']) && $post['protocolo'] != "") {
			$insertProtoc = Client::protocolo($post['protocolo'], $cliente);

			if ($insertProtoc->status == false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Erro ao inserir o protocolo'
				));
			}
		}

		//Remove campos post fora do padrão
		unset($post['tipo']);
		unset($post['vencimento']);
		unset($post['valor']);
		unset($post['protocolo']);

		if (!empty($post)) {

			if (is_array($post['transf']) && $post['transf'][0]!="") {
				//Transfere cliente para novo status
				$tranfCliente = Client::transf($cliente, $post['transf'][0]);

				//Se acontecer algum erro na transferencia mostra erro
				if ($tranfCliente->status === false) {
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível enviar o pagamento.<br />Verifique os erros abaixo:',
						$tranfCliente->errors
					));
					return;
				}
			}
			unset($post['transf']);

			$user_id = $this->auth->getUserId();

			$cadLog = Call::cadastrar($post, $user_id, $cliente);
			$msn = "";

			//Verifica a necessidade de gerar boleto
			if ($tipo == 'geraBoleto') {

				$clienteData = Client::find_by_id($cliente);

				if (!$clienteData->user_boleto) {
					$cadCliente = Payment::cad_cliente($clienteData);
					$idClienteBoleto = $cadCliente->id;
				}else{
					$idClienteBoleto = $clienteData->user_boleto;
				}

				$erroBoleto = true;
				foreach ($_POST['vencimento'] as $key => $value) {
					if ($value != '') {
						$dadosBoleto['user_boleto'] = $idClienteBoleto;
						$dadosBoleto['tipo'] = 'BOLETO';
						$dadosBoleto['vencimento'] = $value;
						$dadosBoleto['valor'] = $_POST['valor'][$key];
						$dadosBoleto['descricao'] = 'Boleto pagamento negociação DivCred';
						$dadosBoleto['id_atd'] = $cadLog->atd->id;
						$dadosBoleto['id_cli'] = $clienteData->id;
						$geraBoleto = Payment::gera_pagamento($dadosBoleto);

						if ($geraBoleto->status != true) {
							$erroBoleto = true;
						}
					}else{
						break;
					}
				}
				if ($erroBoleto == true) {
					$msn = "<br />Boletos enviado com sucesso!";
				}else{
					$msn = false;
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível enviar o pagamento.<br />Verifique os erros abaixo:',
						$geraBoleto->errors
					));
				}
			}

			if ($cadLog->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadLog->errors
				));
			}else{
				if ($cadLog->atd->status_id != 35) {
					//Remove cobrador do cliente
					$atualiza = Client::defcobrador('0', $cliente);
				}

				//Redireciona pra página de resultado
				$this->redirectTo($this->configs->baseURI.'cliente/result');
			}
		}
	}

	public function cadastrarDividaAction($cliente_id)
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente_id)
		]);

		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		if (!empty($post)) {
			if (isset($_POST['tipo'])) {
				$tipos = $_POST['tipo'];
				unset($post['tipo']);
			}else{
				$tipos = null;
			}

			$cadastrarDivida = Debt::cadastrar($post, $cliente_id, $user_id);

			if (count($tipos) > 0) {
				$tipoDivida = Types_debt::atualizar($cadastrarDivida->divida->id, $tipos);
			}

			if ($cadastrarDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadastrarDivida->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Dívida cadastrada com sucesso!',
					$cadastrarDivida->errors
				));
				$this->view
				->setFile('cobranca')
				->setVars([
					'clientes' => Client::find_by_id($cliente_id)
				]);
			}
		}
	}

	public function editarDividaAction($cliente_id, $divida_id)
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente_id)
		]);

		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		if (!empty($post)) {
			$edit = array(
				'data_up' => date('Y-m-d h:i:s'),
				'user_up' => $user_id
			);

			$post = array_merge($post, $edit);

			if (isset($_POST['tipo'])) {
				$tipos = $_POST['tipo'];
				unset($post['tipo']);
			}else{
				$tipos = null;
			}

			$atualizarDivida = Debt::atualizar($post, $divida_id);

			if (count($tipos) > 0) {
				$tipoDivida = Types_debt::atualizar($divida_id, $tipos);
			}

			if ($atualizarDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$atualizarDivida->errors
				));
			}else{
				$this->view
				->setFile('cobranca')
				->setVars([
					'clientes' => Client::find_by_id($cliente_id)
				]);
			}
		}
	}

	public static function atd($cliente='')
	{
		$atd = Call::buscaCliente($cliente);
		if (!is_null($atd)) {
			$data_inicio = new DateTime($atd->data_cad);
			$data_fim = new DateTime();
			$dateInterval = $data_inicio->diff($data_fim);
			$dias = $dateInterval->days;
			if ($dias == 0) {
				return 'atd1';
			}elseif ($dias >= 1 && $dias <= 5) {
				return 'atd2';
			}elseif ($dias > 5) {
				return 'atd3';
			}else{
				return '';
			}
		}
	}

	public function excluirAction($clienteAct = '', $acao = '')
	{
		if (is_numeric($clienteAct) && $acao == '') {
			$cliente = Company::find_by_id($clienteAct);
			$this->load('Helpers\Alert', array(
				'warning',
				'Tem certeza que deseja excluir o cliente abaixo descrito?'
			));
			$this->view->setFile('index')
			->setFile('admin_excluir')
			->setVars([
				'clientes' => Client::find_by_id($clienteAct)
			]);
		}

		if (is_numeric($clienteAct) && $acao == 'confirm') {
			$pag = Payment::delete_all(array('conditions' => array('cliente_id = ?', $clienteAct)));
			$atd = Call::delete_all(array('conditions' => array('cliente_id = ?', $clienteAct)));
			$dividas = Debt::find('all', array('conditions' => array('cliente_id = ?', $clienteAct)));
			foreach ($dividas as $divida) {
				$typeDividas = Types_debt::delete_all(array('conditions' => array('divida_id = ?', $divida->id)));
			}
			$div = Debt::delete_all(array('conditions' => array('cliente_id = ?', $clienteAct)));
			$cliente = Client::find_by_id($clienteAct);
			$cliente->delete();
			$this->redirectTo($this->configs->baseURI.'cliente/result');
		}
	}

	public function excluirDividaAction($dividaAct = '', $cliente='')
	{
		if (is_numeric($dividaAct)) {
			$divida = Debt::find_by_id($dividaAct);
			$typeDividas = Types_debt::delete_all(array('conditions' => array('divida_id = ?', $dividaAct)));
			$divida->delete();

			$this->redirectTo($this->configs->baseURI.'cliente/filtrar/'.$cliente.'/excuida');
		}
	}
}