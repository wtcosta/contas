<?php
class RetornoController extends \HXPHP\System\Controller
{
	public function __construct($configs)
	{
		parent::__construct($configs);

		var_dump($_POST);

		$dados = json_decode($_POST);

		if ($dados['event'] == 'PAYMENT_RECEIVED') {
			$cob = $dados['payment'];

			$atualiza = Payment::alteraStatus($cob['id'], $cob['status']);
		}
	}
}