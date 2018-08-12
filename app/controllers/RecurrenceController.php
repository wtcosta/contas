<?php
class RecurrenceController extends \HXPHP\System\Controller
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

		$this->auth->redirectCheck();

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view
		->setTitle('WtContas - Recorrencia')
		->setVar('rec', Recurrence::All());
	}

	/**
	 * Function para tratar retorno
	 *
	 * $tipo - 'danger', 'success', 'alert'
	 * $msn - 'mensagem a ser exibida'
	 * @var string
	 **/
	public function resultAction($tipo='', $msn='')
	{
		$this->load('Helpers\Alert', array(
			$tipo,
			urldecode($msn)
		));

		//Redireciona para uma view
		$this->view
		->setFile('index')
		->setVars([
			'rec' => Recurrence::All()
		]);
	}

	public function cadastrarAction()
	{
		//Redireciona para uma view
		$this->view->setFile('index');

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post)) {
			$cadRec = Recurrence::cadastrar($post);
			if ($cadRec->status === false) {
				$mensagem = urlencode('Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:<br />'.$cadRec->errors);
				$this->redirectTo($this->configs->baseURI.'recurrence/result/danger/'.$mensagem);
			}else{
				$this->redirectTo($this->configs->baseURI.'recurrence/result/success/Cadastro realizado com sucesso!');
			}
		}
	}
}