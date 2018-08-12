<?php
class UsuariosController extends \HXPHP\System\Controller
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
		$this->auth->roleCheck(array(
			'administrator'
		));

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('HXPHP - Administrativo')
		->setFile('index')
		->setVars([
			'user' => $user,
			'users' => User::all()
		]);
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
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		
		$this->load('Helpers\Alert', array(
			$tipo,
			urldecode($msn)
		));

		//Redireciona para uma view
		$this->view
		->setFile('index')
		->setVars([
			'user' => $user,
			'users' => User::all()
		]);
	}

	public function cadastrarAction()
	{
		//Redireciona para uma view
		$this->view->setFile('index');

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post)) {
			$cadastrarUsuario = User::cadastrar($post);

			if ($cadastrarUsuario->status === false) {
				$mensagem = urlencode('Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:<br />'.$cadastrarUsuario->errors);
				$this->redirectTo($this->configs->baseURI.'usuarios/result/danger/'.$mensagem);
			}else{
				$this->redirectTo($this->configs->baseURI.'usuarios/result/success/Cadastro realizado com sucesso!');
			}
		}
	}

	public function bloquearAction($user_id)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->status = 0;
				$user->save();

				$this->redirectTo($this->configs->baseURI.'usuarios/result/warning/Cadastro bloqueado com sucesso!');
			}
		}
	}

	public function desbloquearAction($user_id)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->status = 1;
				$user->save();

				$this->redirectTo($this->configs->baseURI.'usuarios/result/success/Cadastro desbloqueado com sucesso!');
			}
		}
	}

	public function excluirAction($user_id)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->delete();

				$this->redirectTo($this->configs->baseURI.'usuarios/result/danger/Cadastro excluido com sucesso!');
			}
		}
	}
}