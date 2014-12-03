<?php
	//set_time_limit(0);
	include_once 'conexion.php';	
	class _model{
		function chk_login($Usuario,$Password){
			/*
				Verifica las credenciales para el proceso de autenticacion 
				Parametros:
				*$Usuario-> numero de cedula del alumno
				*$password -> clave compuesta por lo general de 5 digitos asignados por la institucion
			*/
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = "SELECT CEDULA, PASSWORD FROM ALUMNOS.dbf WHERE CEDULA = '$Usuario' AND PASSWORD = '$Password' ";
			//echo $QRY;
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{
				$count=0;
				while(odbc_fetch_row($RS)){
					$count++;
				}
				if($count>0){
					return array(
						"success" => true,
						"login" => true
					);
				}else{
					return array(
						"success" => true,
						"login" => false
					);
				}
			}
		}
		function get_login($Usuario,$Password){
			/*
				Devuelve los datos del usuario autenticado 
				Parametros:
				*$Usuario-> numero de cedula del alumno
				*$password -> clave compuesta por lo general de 5 digitos asignados por la institucion
			*/
			$CNN = new conexion();
			$CNN->Conectar();
			$CNN2 = new conexion();
			$CNN2->Conectar();
			$QRY = "SELECT CODALU, NOMBRE FROM ALUMNOS.dbf WHERE CEDULA = '$Usuario' AND PASSWORD = '$Password'";
			$QRY2 = "SELECT PERIODO ,semestre FROM peract.dbf";
			$QRY2 = "SELECT * FROM ALUMAT WHERE CODALU = (SELECT CODALU FROM ALUMNOS WHERE CEDULA = '$Usuario')
								";
			$RS = $CNN->SQL_QRY($QRY);
			$RS2 = $CNN2->SQL_QRY($QRY2);
			if($RS===false || $RS2===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{
				$count=0;
				$data = array();
				$PER = '';
				$SEM = '';
				while(odbc_fetch_row($RS2)){
					$PER=odbc_result($RS2,'PERIODO');
					$SEM=odbc_result($RS2,'SEMESTRE');
				}
				while(odbc_fetch_row($RS)){
					return array(
						"success" => true,
						"login" => true,
						"codigo" => odbc_result($RS,'CODALU'),
						"nombre" => utf8_encode(trim(odbc_result($RS,'NOMBRE'))),
						"periodo" => $PER,
						"semestre" => $SEM 
					);
					$count++;
				}
				if($count==0){
					return array(
						"success" => true,
						"login" => false
					);
				}
			}
		}
		function lst_materias($codigo,$periodo,$semestre){
			/*
				Retorna el listado de materias con informacion detallada de notas del 1er,2do y 3er parcial
				Parametros:
				*$codigo-> codigo del alumno
				*$periodo -> periodo actual que el alumno este cursando
				*$semestre-> semestre actual que el alumno este cursando
				
				Dentro de esta opcion el alumno puede escoger si ver el listado historico de materias o las materias actuales
			*/
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = "
			SELECT 
				* 
			FROM 
				NOTAS, MATERIA, CARRERA
			WHERE
				NOTAS.CODMAT = MATERIA.CODMAT 
			AND NOTAS.CODCAR = CARRERA.CODCAR
			AND NOTAS.PERIODO LIKE '$periodo' 
			AND NOTAS.CODALU LIKE '$codigo'
			AND NOTAS.SEMESTRE LIKE '$semestre'
			ORDER BY
				NOTAS.CODCAR
			";
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{
				$data = array();
				$index = 1;
				while(odbc_fetch_row($RS)){
					array_push($data,array(
						"index" => $index,
						"periodo" => odbc_result($RS,'PERIODO'),
						"semestre" => odbc_result($RS,'SEMESTRE'),
						"carrera_cod" => "",
						"carrera_det" => "CARRERA - " . trim(odbc_result($RS,'DESC')),
						"materia_cod" => odbc_result($RS,'CODMAT'),
						"materia_det" => odbc_result($RS,'DESMAT'),
						"Iparcial" => number_format(odbc_result($RS,'IPARCIAL')),
						"IIparcial" => number_format(odbc_result($RS,'IIPARCIAL')),
						"IIIparcial" => number_format(odbc_result($RS,'IIIPARCIAL')),
						"supletorio" => number_format(odbc_result($RS,'SUPLETORIO')),
					));
					$index++;
				}
				return array(
					"success" => true,
					"data" => $data
				);
			}
		}
		function lst_asistencias($codigo,$periodo,$semestre){ 
			/*
				Retorna el listado de materias que el alumno este cursando actualmente donde tambien calcula el porcentaje de asistencia por cada materia
				Parametros:
				*$codigo-> codigo del alumno
				*$periodo -> periodo actual que el alumno este cursando
				*$semestre-> semestre actual que el alumno este cursando
			*/
			$CNN = new conexion();
			$CNN->Conectar();
			$codPar = 0;
			$codMat = '';
			$QRY = "
			SELECT 
				* 
			FROM 
				NOTAS, MATERIA, CARRERA
			WHERE
				NOTAS.CODMAT = MATERIA.CODMAT 
			AND NOTAS.CODCAR = CARRERA.CODCAR
			AND PERIODO LIKE '$periodo' 
			AND CODALU LIKE '$codigo'
			AND SEMESTRE LIKE '$semestre'
			ORDER BY
				NOTAS.CODCAR
			";
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{
				$data = array();
				$data1 = array();
				$index = 1;
				
				while(odbc_fetch_row($RS)){
					$codPar = odbc_result($RS,'CODPAR');
					$codMat = odbc_result($RS,'CODMAT');
					$data1 = $this->asistencias_x_materia($codigo,$periodo,$semestre,$codMat,$codPar);//calculate percentage assistance by grades
					array_push($data,array(
						"index" => $index,
						"periodo" => odbc_result($RS,'PERIODO'),
						"semestre" => odbc_result($RS,'SEMESTRE'),
						"carrera_cod" => "ANA",
						"carrera_det" => "CARRERA - " . trim(odbc_result($RS,'DESC')),
						"materia_cod" => odbc_result($RS,'CODMAT'),
						"materia_det" => odbc_result($RS,'DESMAT'),
						"total_horas_dictadas"=>$data1["totalHours"],
						"total_horas_asistidas"=>$data1["totalHoursAttended"],
						"porcentaje_asistencia"=>$data1["totalPercentage"]
					));
					$index++;
				}
				return array(
					"success" => true,
					"data" => $data
				);
			}
		}
		function asistencias_x_materia($codigo,$periodo,$semestre,$codigo_materia,$codigo_par){
		/*
			Calcula el porcentaje de asistencia por cada materia que el alumno este viendo
			Parametros:
			*$codigo-> codigo del alumno
			*$periodo-> periodo o año actual
			*$semestre -> a,b o cada
			*$codigo_materia -> codigo de la materia que esta viendo actualmente el alumno
			*$codigo_par -> codigo del paralelo donde se esta viendo la materia ('A','B' o 'C')
		*/
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = '';
			$totalHoursAttended = 0; //total hour's alumn assisted to class
			$totalHours = 0; //total hours
			//query classes attended
			$QRY = "			
				SELECT 
					SUM(hordic) as totalHours
				FROM 
					CLADIC 
				WHERE 
					periodo  like  '$periodo'
				and
					semestre like '$semestre' 
				and
					codmat like '$codigo_materia' 
				and
					codpar like '$codigo_par' 
			";			
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{ 
				while(odbc_fetch_row($RS)){
					$totalHours = number_format(odbc_result($RS,'totalHours')); 
				}
				
			}
			//query classes attended
			$QRY = "			
				SELECT 
					SUM(horasi1+horasi2) as total
				FROM 
					DETASI 
				WHERE 
					periodo  like  '$periodo'
				AND 
					semestre like '$semestre' 
				AND	
					codmat like '$codigo_materia' 
				AND
					codpar like '$codigo_par' 
				AND 
					codalu like '$codigo'
			";			
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{ 
				while(odbc_fetch_row($RS)){
					$totalHoursAttended = odbc_result($RS,'total'); 
				}
			}
			$total1 = ($totalHoursAttended * 100);
			$totalPercentageAssistance =($total1 / $totalHours);	
			return array(
				"success" => true,
				"totalHours"=>number_format($totalHours),
				"totalHoursAttended"=>number_format($totalHoursAttended),
				"totalPercentage"=>number_format ($totalPercentageAssistance,2).'%'
			);			
		}
		function lst_deudas($codigo,$periodo,$semestre){
			/*
				Retorna el listado de pagos y deudas que el alumno tiene con la institucion dentro del periodo actual
				Parametros:
				*$codigo-> codigo del alumno
				*$periodo -> periodo actual que el alumno este cursando
				*$semestre-> semestre actual que el alumno este cursando
			*/
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = "
				SELECT 
					'EXT' AS PAGO,
					D.DESITE,
					A.VALOR as VALOR,
					0 AS DESCUENTO,
					A.VALOR as APAGAR,
					0 AS PAGADO,
					a.codext AS ENLACE,
					A.CODALU AS ENLACE1,
					'000000A' AS ENLACE2,
					'00A' AS ENLACE3
				FROM 
					extras A,
					ALUMNOS C,
					ITEMCOB D
				WHERE 
					A.CODALU = '$codigo'
				AND	C.PERIODO = '$periodo'
				AND C.SEMESTRE = '$semestre'
				AND	a.codalu = c.codalu
				AND	A.CODITE = D.CODITE
				AND	a.fecha < date()
				UNION ALL
				SELECT 
					'NOR' AS PAGO,
					D.DESITE,
					A.VALOR AS VALOR,
					C.DESCUENTO,
					a.valor - (a.valor*c.descuento/100) as APAGAR,
					0 AS PAGADO,
					'0000000A' AS ENLACE,
					C.CODALU AS ENLACE1,
					A.CODCAT AS ENLACE2,
					A.CODITE AS ENLACE3
				FROM 
					DETCOB A,
					COBROS B,
					ALUMAT C,
					ITEMCOB D 
				WHERE 
						C.CODALU = '$codigo'
				AND	C.PERIODO = '$periodo'
				AND	A.CODCAT+A.CODPEN = B.CODCAT+B.CODPEN
				AND	A.CODCAT = C.CODCAT
				AND	A.CODITE = D.CODITE
				AND	b.fecven <= date()
			
			";
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return array(
					"success" => false,
					"msg" => odbc_errormsg($CNN->getCN()));
			}else{
				$data = array();
				$total = 0;
				while(odbc_fetch_row($RS)){
					array_push($data,array(
						'PAGO' => odbc_result($RS,'PAGO'),
						'ENLACE' => odbc_result($RS,'ENLACE'),
						'ENLACE1' => odbc_result($RS,'ENLACE1'),
						'ENLACE2' => odbc_result($RS,'ENLACE2'),
						'ENLACE3' => odbc_result($RS,'ENLACE3'),
						'DESITE' =>	odbc_result($RS,'DESITE'),
						'VALOR' => odbc_result($RS,'VALOR'),
						'DESCUENTO' => odbc_result($RS,'DESCUENTO'),
						'APAGAR' => odbc_result($RS,'APAGAR'),
						'PAGADO' => odbc_result($RS,'PAGADO'),
						'TOTAL' => 0,
					));
				}
				$final = array();
				for($i=0;$i<count($data);$i++){
					if($data[$i]['PAGO']=="NOR"){
						$data[$i]['PAGADO'] = $this->getPagos($data[$i]['ENLACE1'].$data[$i]['ENLACE2'].$data[$i]['ENLACE3']);
					}else{
						$data[$i]['PAGADO'] = $this->getPagosExt($data[$i]['ENLACE1']);
					}
					$data[$i]['TOTAL'] = "$ " . number_format($data[$i]['APAGAR']-$data[$i]['PAGADO'] ,2) . "  ";
					
					$total+=$data[$i]['APAGAR']-$data[$i]['PAGADO'];
					if($data[$i]['APAGAR']-$data[$i]['PAGADO']>0){
						array_push($final,$data[$i]);
					}
				}			
				return array(
					"success" => true,
					"total" => "$ " . number_format($total,2),
					"data" => $final
				);
			}
		}
		function getPagos($codigo_enlace){
			$valor=0;
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = "SELECT SUM(PAGOS.VALOR) AS VALOR FROM PAGOS WHERE PAGOS.CODALU+PAGOS.CODCAT+PAGOS.CODITE = '$codigo_enlace'";
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return 0;
			}else{
				$a = odbc_fetch_array($RS);
				$valor = ($a['valor']==null?0:$a['valor']);
			}
			return $valor;
		}
		function getPagosExt($codigo_enlace){
			$valor=0;
			$CNN = new conexion();
			$CNN->Conectar();
			$QRY = "SELECT SUM(pagext.VALOR) AS VALOR FROM pagext WHERE pagext.codext = '$codigo_enlace'";
			$RS = $CNN->SQL_QRY($QRY);
			if($RS===false){
				return 0;
			}else{
				$a = odbc_fetch_array($RS);
				$valor = ($a['valor']==null?0:$a['valor']);
			}
			return $valor;
		}
	}
?>
