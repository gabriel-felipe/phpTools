<?php
/*
Eu gosto de planejar meus bancos de dados usando planilhas, geralmente uso o google docs para isso.
As planilhas seguem o formato
----------------------------------.
tabela a   | tabela b | tabela c  |
----------------------------------.
campo tb a |campo tb b|campo tb c |
----------------------------------.
Feito isso no google docs eu exporto para csv e rodo esse script que cria a sql básica para o banco. Ele usa alguns filtros nos nomes para sugerir os tipos de dados.
Normalmente eu tenho que editar uns 20% dos tipos na mão. 

As tabelas são nomeadas seguindo o seguinte padrão:
chaves primarias tem o nome da tabela com o sufixo _id
A príncipio ele não cria as conexões de chaves estrangeiras.
Esse script não deve ser utilizado para se ter uma versão final do sql para gerar o banco, apenas para agilizar o processo inicial.

Exemplo de output:
imagine o seguinte csv (db_exemplo.csv):
"
perfil_frete,regra_frete,padrao_regra,estado,estado_regra
perfil_frete_id,regra_frete_id,padrao_regra_id,estado_id,estado_id
seller_id,perfil_frete_id,nome_metodo,sigla,regra_frete_id
titulo,padrao_regra_id,parametros,estado,
,parametros,,,
"
o resultado será algo como:
"
CREATE TABLE perfil_frete(perfil_frete_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,seller_id int(11) ,titulo varchar(255) );
CREATE TABLE regra_frete(regra_frete_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,perfil_frete_id int(11) ,padrao_regra_id int(11) ,parametros varchar(255) );
CREATE TABLE padrao_regra(padrao_regra_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,nome_metodo varchar(130) ,parametros varchar(255) );
CREATE TABLE estado(estado_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,sigla varchar(255) ,estado varchar(255) );
CREATE TABLE estado_regra(estado_id int(11) ,regra_frete_id int(11) );
"
*/
	$arquivo = file("db_exemplo.csv");
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