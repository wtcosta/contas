<?php
class EmpresaController extends \HXPHP\System\Controller
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

		if ($role->role == 'cobrança' || $role->role == 'extrajudicial' || $role->role == 'juridico') {
			$this->redirectTo('cliente', false, false);
		}
		if ($role->role == 'cliente') {
			//$cliente = Client::find(array('conditions' => array('idUserCliente = ?', $user_id)));
			$this->redirectTo('cliente', false, false);
		}
		if ($role->role == 'empresa') {
			//$empresa = Company::find(array('conditions' => array('idUserEmpresa = ?', $user_id)));
			$this->redirectTo('cliente', false, false);
		}

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$boletos = Payment::all();

		$relat = new \stdClass;
		$relat->total = 0;
		$relat->pagos = 0;
		$relat->vencidos = 0;
		$relat->totalClientes = count(Client::all());
		$relat->totalDebitos = count(Debt::all());

		foreach ($boletos as $boleto) {
			$relat->total++;
			switch ($boleto->status) {
				case 'CONFIRMED':
				$relat->pagos++;
				break;
				case 'RECEIVED':
				$relat->pagos++;
				break;
				case 'OVERDUE':
				$relat->vencidos++;
				break;
			}
		}

		$this->view
		->setFile('index')
		->setVars([
			'empresa' => Company::all(),
			'relatorio' => $relat
		]);
	}

	public function resultAction($tipo='', $msn='', $erro='')
	{
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		if ($tipo == 'danger') {
			$this->view->setFile('cadastrar');
			$this->load('Helpers\Alert', array(
				'danger',
				$msn,
				$erro
			));
		}else{
			$this->view
			->setFile('index')
			->setVars([
				'empresa' => Company::all()
			]);
			$this->load('Helpers\Alert', array(
				'success',
				str_replace("_", " ", $msn),
				$erro
			));
		}
	}

	public function cadastrarAction()
	{
		$erro = false;

		//Redireciona para uma view
		$this->view->setFile('cadastrar');

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post) && $post['email'] !== null) {

			$user_id = $this->auth->getUserId();

			$docs = $post['documentos'];
			unset($post['documentos']);

			$cadEmpresa = Company::cadastrar($post, $user_id);

			if ($cadEmpresa->status === false) {
				$erro = true;
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadEmpresa->errors
				));
			}else{
				Document::cadastrar($docs, $cadEmpresa->emp->id);

				if (isset($_FILES['contrato']) && !empty($_FILES['contrato']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->contrato)) {
								unlink($dir_path . $cadEmpresa->emp->contrato);
							}
							$cadEmpresa->emp->contrato = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível atualizar a sua imagem de perfil',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['contrato_social']) && !empty($_FILES['contrato_social']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato_social']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->contrato_social)) {
								unlink($dir_path . $cadEmpresa->emp->contrato_social);
							}
							$cadEmpresa->emp->contrato_social = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com contrato social',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['cartao_cnpj']) && !empty($_FILES['cartao_cnpj']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['cartao_cnpj']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->cartao_cnpj)) {
								unlink($dir_path . $cadEmpresa->emp->cartao_cnpj);
							}
							$cadEmpresa->emp->cartao_cnpj = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com cartão CNPJ',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['comprovante_endereco']) && !empty($_FILES['comprovante_endereco']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['comprovante_endereco']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->comprovante_endereco)) {
								unlink($dir_path . $cadEmpresa->emp->comprovante_endereco);
							}
							$cadEmpresa->emp->comprovante_endereco = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com o comprovante de endereço.',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['autorizacao_terceiro']) && !empty($_FILES['autorizacao_terceiro']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['autorizacao_terceiro']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->autorizacao_terceiro)) {
								unlink($dir_path . $cadEmpresa->emp->autorizacao_terceiro);
							}
							$cadEmpresa->emp->autorizacao_terceiro = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo de autorização da conta de terceiro.',
								$uploadUserImage->error
							));
						}
					}
				}

				if ($erro === false) {
					$this->redirectTo($this->configs->baseURI.'empresa/result/sucesso/Empresa_cadastrada_com_sucesso!');
				}
			}
		}
	}

	public function editarAction($empresa)
	{
		$erro = false;

		$this->view->setFile('cadastrar');

		$user_id = $this->auth->getUserId();

		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		$users = User::all();
		$option2 = array();
		foreach ($users as $value) {
			$option2[$value->id] = $value->name;
		}

		$this->view
		->setVars([
			'emp' => Company::find($empresa),
			'option2' => $option2
		]);

		if (!empty($post)) {

			$edit = array(
				'data_up' => date('Y-m-d h:i:s'),
				'user_up' => $user_id
			);

			$post = array_merge($post, $edit);

			$atualizaEmp = Company::atualizar($post, $empresa);

			Document::cadastrar($_POST['documentos'], $atualizaEmp->emp->id);

			if ($atualizaEmp->status === false) {
				$erro = true;
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaEmp->errors
				));
			}else{
				if (isset($_FILES['contrato']) && !empty($_FILES['contrato']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->contrato)) {
								unlink($dir_path . $atualizaEmp->emp->contrato);
							}
							$atualizaEmp->emp->contrato = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível atualizar a sua imagem de perfil',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['contrato_social']) && !empty($_FILES['contrato_social']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato_social']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->contrato_social)) {
								unlink($dir_path . $atualizaEmp->emp->contrato_social);
							}
							$atualizaEmp->emp->contrato_social = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com contrato social',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['cartao_cnpj']) && !empty($_FILES['cartao_cnpj']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['cartao_cnpj']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->cartao_cnpj)) {
								unlink($dir_path . $atualizaEmp->emp->cartao_cnpj);
							}
							$atualizaEmp->emp->cartao_cnpj = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com cartão CNPJ',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['comprovante_endereco']) && !empty($_FILES['comprovante_endereco']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['comprovante_endereco']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->comprovante_endereco)) {
								unlink($dir_path . $atualizaEmp->emp->comprovante_endereco);
							}
							$atualizaEmp->emp->comprovante_endereco = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com o comprovante de endereço.',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['autorizacao_terceiro']) && !empty($_FILES['autorizacao_terceiro']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['autorizacao_terceiro']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->autorizacao_terceiro)) {
								unlink($dir_path . $atualizaEmp->emp->autorizacao_terceiro);
							}
							$atualizaEmp->emp->autorizacao_terceiro = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}else {
							$erro = true;
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo de autorização da conta de terceiro.',
								$uploadUserImage->error
							));
						}
					}
				}

				if ($erro === false) {
					$this->redirectTo($this->configs->baseURI.'empresa/result/sucesso/Empresa_editada_com_sucesso!');
				}
			}
		}
	}

	public function excluirAction($emp_id)
	{
		if (is_numeric($emp_id)) {
			$empresa = Company::find_by_id($emp_id);
			$cliente = Client::find('all', array('conditions' => array('companies_id = ?', $emp_id)));
			if (count($cliente) > 0) {
				$this->load('Helpers\Alert', array(
					'error',
					'Oops! Essa empresa possui cliente cadastrado<br />Exclua todos os cliente para prosseguir!'
				));
			}else{
				if (!is_null($empresa)) {
					$div = Document::delete_all(array('conditions' => array('companies_id = ?', $emp_id)));
					$empresa->delete();

					$this->load('Helpers\Alert', array(
						'success',
						'Empresa excluida com sucesso!'
					));
					$this->view->setFile('index')
					->setVars([
						'empresa' => Company::all()
					]);
				}
			}
		}
	}
}