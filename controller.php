<?php
	//header('Content-Type:text/plain');
	set_time_limit(0);
	include_once 'model.php';
	error_reporting(-1);
	$mdl = new _model();
	$action=(isset($_GET['action']))?$_GET['action']:null;
	$start=(isset($_GET['start']))?$_GET['start']:0;
	$limit=(isset($_GET['limit']))?$_GET['limit']:10;
	$filter=isset($_GET['filtro'])?strtoupper($_GET['filtro']):'%';
	switch($action){
		case 'chk_login';
			$Usuario=str_replace("'","''",$_GET['CI']);
			$Password=str_replace("'","''",$_GET['PwD']);
			Parse($mdl->chk_login($Usuario,$Password));
		break;
		case 'get_login';
			$Usuario=str_replace("'","''",$_GET['CI']);
			$Password=str_replace("'","''",$_GET['PwD']);
			Parse($mdl->get_login($Usuario,$Password));
		break;
		case 'lst_materias';
			$codigo=str_replace("'","''",$_GET['cod_est']);
			$periodo=str_replace("'","''",$_GET['per']);
			$semestre=str_replace("'","''",$_GET['sem']);
			Parse($mdl->lst_materias($codigo,$periodo,$semestre));
		break;
		case 'lst_asistencias';
			$codigo=str_replace("'","''",$_GET['cod_est']);
			$periodo=str_replace("'","''",$_GET['per']);
			$semestre=str_replace("'","''",$_GET['sem']);
			Parse($mdl->lst_asistencias($codigo,$periodo,$semestre));
		break;
		case 'lst_deudas';
			$codigo=str_replace("'","''",$_GET['cod_est']);
			$periodo=str_replace("'","''",$_GET['per']);
			$semestre=str_replace("'","''",$_GET['sem']);
			Parse($mdl->lst_deudas($codigo,$periodo,$semestre));
		break;		
		default:
			Parse(array(
				"success" => false,
				"msg" => "No se especifico ninguna opcion Valida"
			));
		break;
	}	
	function Parse($X){
		echo json_encode($X);
	}
?>