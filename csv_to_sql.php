<?php
	$arquivo = file("db_perfil_frete.csv");
	ini_set('display_errors',1); 
 	error_reporting(E_ALL);
 	ini_set('html_errors', 'On');
	function str_clean($var){
		$a = Array('À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å');
		$e = Array('È','É','Ê','Ë','è','é','ê','ë');
		$c = Array('Ç', 'ç');
		$i = Array('Ì','Í','Î','Ï','ì','í','î','ï');
		$n = Array('Ñ', 'ñ');
		$o = Array('Ò','Ó','Ô','Õ','Ö','ò','ó','ô','õ','ö');
		$u = Array('Ù','Ú','Û','Ü','ù','ú','û','ü');
		$y = Array('Ý', 'ý','ÿ');
		$var = str_replace($a,"a", $var);
		$var = str_replace($e,"e", $var);
		$var = str_replace($c,"c", $var);
		$var = str_replace($i,"i", $var);
		$var = str_replace($n,"n", $var);
		$var = str_replace($o,"o", $var);
		$var = str_replace($u,"u", $var);
		$var = str_replace($y,"y", $var);
		$var = str_replace(" ","_", $var);
		$var = str_replace(" ","_", $var);
		$var = str_replace("\n","",$var);
		$var = strtolower($var);
		return $var;
	}
	
	$tabelas = explode(",",$arquivo[0]);
	
	$db = array();
	$tabelasSql = "";
	foreach($tabelas as $k=>$v){
		$tabelas[$k] = str_clean(trim($v));
		$db[str_clean(trim($v))] = array();
	}
	$tabelasColunas = array();
	foreach ($tabelas as $tabela)
		$tabelasColunas[$tabela] = array();
	
	
	foreach($arquivo as $n=>$linha){
		if($n > 0){
			$cols = explode(",",$linha);
			foreach($cols as $k=>$v){
				if(trim($v) and !empty($v)){
					if(!array_search(str_clean(trim($v)), $db[$tabelas[$k]])){
						$table = $tabelas[$k];
						$col = str_clean($v);
						$foreign = false;
						$primary = false;
						if(strpos($col, '_id')){
							if($col == $table."_id"){
								$primary = true;
							} else {
								$foreign = true;
							
							}
							$type = "int(11)";
						} elseif(strpos($col,"data") !== false or strpos($col,"hora") !== false) {
							$type = "int(20)";
						} elseif(strpos($col,"nome") !== false or strpos($col,"email") !== false){
							$type = "varchar(130)";
						} elseif(strpos($col,"telefone") !== false) {
							$type = 'varchar(15)';
						} elseif(strpos($col, "cpf") !== false or strpos($col, "cnpj") !== false){
							$type = 'int(15)';
						} elseif(strpos($col, "descricao") !== false){
							$type = 'varchar(1024)';
						}else {
							$type = 'varchar(255)';
						}
						$tabelasColunas[$table][] = array('nome'=>$col,'type'=>$type,'foreign'=>$foreign,'primary'=>$primary);
					

					}
				}
			}
		}
	}
	foreach($tabelasColunas as $tbname => $table){
		$cols = "";
		foreach($table as $col){
			$primary = ($col['primary']) ? "NOT NULL AUTO_INCREMENT PRIMARY KEY" : "";
			$cols .= $col['nome']." ".$col['type']." ".$primary.",";

		}
		$cols = substr($cols,0,-1);
		echo "CREATE TABLE $tbname($cols);<br />";
	}
	
	
	
?>