<?php //Connection Handler
	class conexion{
		var $DSN = "virtual";
		var $USR = "";
		var $PWD = "";
		var $CN = null;
		function Conectar(){
			$this->CN = odbc_connect($this->DSN,$this->USR,$this->PWD) or die("Error de Acceso a la Base de datos");
		}
		function Desconectar(){
			odbc_close($this->CN);
		}
		function getCN(){
			return $this->CN;
		}
		function SQL_QRY($StringSQL){
			if($this->CN==null)
				die("No se inicio la conexion");
			return odbc_exec($this->CN, $StringSQL);
		}
	}
?>