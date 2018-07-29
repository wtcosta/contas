<?php
class BoletoController extends \HXPHP\System\Controller
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

		$boletos = Payment::all();

		$relat = new \stdClass;
		$relat->total = 0;
		$relat->pagos = 0;
		$relat->pendentes = 0;
		$relat->vencidos = 0;
		$relat->estornados = 0;

		foreach ($boletos as $boleto) {
			/*
			if ($boleto->status != 'RECEIVED' && $boleto->status != "REFUNDED") {
				$newdados = json_decode(Payment::consultaStatusAsaas($boleto->pagamento_id));
				if ($boleto->status != $newdados->status) {
					Payment::alteraStatus($boleto->pagamento_id, $newdados->status);
				}
			}
			*/
			$relat->total = $relat->total+$boleto->valor;
			switch ($boleto->status) {
				case 'PENDING':
				$relat->pendentes = $relat->pendentes+$boleto->valor;
				break;
				case '':
				$relat->pendentes = $relat->pendentes+$boleto->valor;
				break;
				case 'CONFIRMED':
				$relat->pagos = $relat->pagos+$boleto->valor;
				break;
				case 'RECEIVED':
				$relat->pagos = $relat->pagos+$boleto->valor;
				break;
				case 'OVERDUE':
				$relat->vencidos = $relat->vencidos+$boleto->valor;
				break;
				case 'REFUNDED':
				$relat->estornados = $relat->estornados+$boleto->valor;
				break;
			}
		}

		$this->view
		->setFile('index')
		->setVars([
			'boletos' => Payment::all(),
			'relatorio' => $relat
		]);
	}

	public function editadoAction($value='')
	{
		/*
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load('Helpers\Alert', array(
			'success',
			'Usuário editado com sucesso!'
		));

		$this->view
		->setFile('index')
		->setVars([
			'user' => $user,
			'users' => User::all()
		]);
		*/
	}

	public function excluirAction($boleto_id)
	{
		$post = $this->request->post();

		if ($post['boleto_id'] == $boleto_id) {
			$dadosBoleto = Payment::find(array('conditions' => array('pagamento_id = ?', $boleto_id)));
			if ($dadosBoleto->status == 'PENDING' || $dadosBoleto->status == 'OVERDUE') {
				$excluir = Payment::excluirBoleto($boleto_id);
				if ($excluir->deleted == true) {
					Payment::delete_all(array('conditions' => array('pagamento_id = ?', $boleto_id)));
					$this->load('Helpers\Alert', array(
						'success',
						'Boleto excluido com sucesso!'
					));
				}else{
					$this->load('Helpers\Alert', array(
						'danger',
						'Erro ao excluir o boleto junto ao sistema ASAAS<br />Contate o administrador.'
					));
				}
			}else{
				$this->load('Helpers\Alert', array(
					'danger',
					'Boletos com o status '.Payment::status($dadosBoleto->status).' não pode ser excluido!<br />Contate o administrador.'
				));
			}
		}else{
			$this->load('Helpers\Alert', array(
				'danger',
				'Dados não localizado para exclusão<br />Volte a listagem dos boletos e repita o processo.'
			));
		}

		$this->view
		->setFile('index')
		->setVars([
			'boletos' => Payment::all()
		]);
	}
}