<?php
//Constantes
$configs = new HXPHP\System\Configs\Config;
ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';

$configs->title = 'WtFinanças';

$configs->env->add('development');

$configs->env->development->mail->setFrom([
  'from' => $configs->title,
  'from_mail' => 'contato@wesleyteixeira.com.br'
  ]);

$configs->env->development->baseURI = '/contas/';

$configs->env->development->database->setConnectionData(array(
  'host' => 'localhost',
  'user' => 'root',
  'password' => 'Wtc0304!',
  'dbname' => 'wt_contas'
));

$configs->env->development->auth->setURLs('/contas/empresa', '/contas/login');

$configs->env->development->menu->setMenus(array(
  'Administrativo/dashboard' => [
    'Usuário/dashboard' => '%baseURI%/usuarios',
    'Log/history' => '%baseURI%/log',
    'Status/check-circle-o' => '%baseURI%/status',
    'Triagem/bars' => '%baseURI%/sequencia'
  ],
  'Empresa/university' => '%baseURI%/empresa',
  'Clientes/user' => '%baseURI%/cliente',
  'Boletos/clipboard' => '%baseURI%/boleto'
), 'administrator');

$configs->env->development->menu->setMenus(array(
  'Cliente/user' => '%baseURI%/cliente'
), 'cobrança');

$configs->env->development->menu->setMenus(array(
  'Cliente/user' => '%baseURI%/cliente'
), 'extrajudicial');

$configs->env->development->menu->setMenus(array(
  'Cliente/user' => '%baseURI%/cliente'
), 'juridico');

$configs->env->development->menu->setMenus(array(
  'Cliente/user' => '%baseURI%/cliente'
), 'empresa');

$configs->env->development->menu->setMenus(array(
  'Cliente/user' => '%baseURI%/cliente'
), 'cliente');

$configs->env->development->menu->setConfigs([
    'container_class' => 'menu_section active',
    'menu_class' => 'nav side-menu',
    'menu_item_active_class' => 'active',
    'dropdown_class' => 'nav child_menu',
]);

/*
  //Globais
  $configs->title = 'Titulo customizado';

  //Configurações de Ambiente - Desenvolvimento
  $configs->env->add('development');

  $configs->env->development->baseURI = '/hxphp/';

  $configs->env->development->database->setConnectionData([
  'driver' => 'mysql',
  'host' => 'localhost',
  'user' => 'root',
  'password' => '',
  'dbname' => 'hxphp',
  'charset' => 'utf8'
  ]);

  $configs->env->development->mail->setFrom([
  'from' => 'Remetente',
  'from_mail' => 'email@remetente.com.br'
  ]);

  $configs->env->development->menu->setConfigs([
  'container' => 'nav',
  'container_class' => 'navbar navbar-default',
  'menu_class' => 'nav navbar-nav'
  ]);

  $configs->env->development->menu->setMenus([
  'Home/home' => '%siteURL%',
  'Subpasta/folder-open' => [
  'Home/home' => '%baseURI%/admin/have-fun/',
  'Teste/home' => '%baseURI%/admin/index/',
  ]
  ]);

  $configs->env->development->auth->setURLs('/hxphp/home/', '/hxphp/login/');
  $configs->env->development->auth->setURLs('/hxphp/admin/home/', '/hxphp/admin/login/', 'admin');

  //Configurações de Ambiente - Produção
  $configs->env->add('production');

  $configs->env->production->baseURI = '/';

  $configs->env->production->database->setConnectionData([
  'driver' => 'mysql',
  'host' => 'localhost',
  'user' => 'usuariodobanco',
  'password' => 'senhadobanco',
  'dbname' => 'hxphp',
  'charset' => 'utf8'
  ]);

  $configs->env->production->mail->setFrom([
  'from' => 'Remetente',
  'from_mail' => 'email@remetente.com.br'
  ]);
 */

  return $configs;