<?php
class IndexController extends \HXPHP\System\Controller
{
    function __construct($foo = null)
    {
    	parent::__construct($configs);
    	
    	$this->redirectTo($this->configs->baseURI.'home');
    }
}