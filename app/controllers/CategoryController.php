<?php
class CategoryController extends \HXPHP\System\Controller
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
		->setTitle('WtContas - Categorias')
		->setVar('categorias', Category::all());
	}

	public function cadastrarAction()
	{
		//Redireciona para uma view
		$this->view->setFile('index');

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post)) {
			$cadCategoria = Category::cadastrar($post);
			if ($cadCategoria->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadCategoria->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Cadastro realizado com sucesso!'
				));
				$this->view
				->setVar('categorias', Category::all());
			}
		}
	}

	public function editarAction($cat)
	{
		$post = $this->request->post();

		if (!empty($post)) {
			$atualizarCad = Category::atualizar($cat, $post);

			if ($atualizarCad->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível editar o cadastro. <br> Verifique os erros abaixo:',
					$atualizarCad->errors
				));
			}else{
				$this->redirectTo($this->configs->baseURI.'usuarios/editado');
			}
		}

		$this->view
		->setVar('categorias', Category::all());
	}
}