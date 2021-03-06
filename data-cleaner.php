<?php
/*
3 classes pequenas para agilizar o processo de validação e limpeza de dados e a segurança do site.
Normalmente eu uso apenas a última classe, ela se encarrega de chamar as outras duas.

Bem como isso funciona? 
Basicamente já limparemos e validaremos os dados na hora de pegar eles.


Como é o Processo Padrão:
$email = $_POST['email'];
--limpa os dados do email--
$nome = $_POST['nome'];
--limpa os dados do nome--
$telefone = $_POST['telefone'];
--limpa os dados do telefone--
if(validação de email and validacao de nome){
	--envia email---
}
--------------------------
Processo Com a Classe
$email = data::post('email','email','email');
$nome = data::post('nome','alpha','alpha');
$telefone = data::post('telefone','int','int',array(8,11));
if($email and $nome){
	envia email
}
Existem atalhos para as principais entradas de dados.
data::get
data::post
data::session
data::cookie
*/
class validate {
	static function email($str){
		
	 	return preg_match("/^.+@.+\..+/",$str) ? $str : false;
	}
	static function str($str, $min=1,$max=10000){
		return (preg_match("/^(.|\s)+$/",$str) and strlen($str) >= $min and strlen($str) <= $max) ? $str : false;
	}
	static function int($str, $min=1, $max=10000){
		return (int)(preg_match("/^[\d]{".$min.",".$max."}$/",$str)) ? $str : false;	
	}
	static function float($str, $min=1, $max=10000){
		return (preg_match("/^[\d.,+-eE]{".$min.",".$max."}$/",$str)) ? $str : false;	
	}
	static function alpha_numeric_nospace($str,$min=1,$max=10000){
		return (preg_match("/^[áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.\d]{".$min.",".$max."}$/",$str)) ? $str : false;		
	}
	static function alpha_nospace($str,$min=1,$max=10000){
		return (preg_match("/^[áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.]{".$min.",".$max."}$/",$str)) ? $str : false;		
	}
	static function alpha_numeric($str,$min=1,$max=10000){
		return (preg_match("/^[áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.\d\s]{".$min.",".$max."}$/",$str)) ? $str : false;		
	}
	static function alpha($str,$min=1,$max=10000){
		return (preg_match("/^[áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.\s]{".$min.",".$max."}$/",$str)) ? $str : false;		
	}
	static function custom($str, $regex){
		return (preg_match($regex,$str)) ? $str : false;		
	}
	static function url($str){
		 return filter_var($str,FILTER_VALIDATE_URL);
	}
	static function absolute_url($str){
		if($str){

			return (strpos($str,"http://") !== false or strpos($str,"https://") !== false) ? true : false;
		} else {
			return false;
		}
	}
}
class sanitize {
	static function email($str){
		return filter_var($str,FILTER_SANITIZE_EMAIL);
	}
	static function int($str){
		return preg_replace("/[^\d]/", "",$str);
	}
	static function float($str){
		return filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT);	
	}
	static function special_chars($str){
		return filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);	
	}
	static function alpha($str){
		return preg_replace("/[^áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.\s]/", "",$str);
	}
	static function url($str){
		return filter_var($str, FILTER_SANITIZE_URL);	
	}
	static function alpha_numeric($str){
		return preg_replace("/[^áéíóúãẽĩõũÁÉÍÓÚÃẼĨÕŨÀàçÇA-z@-_.\s\d,]/", "",$str);
	}
	static function no_special($str){
		return preg_replace("/[^A-z@-_.\d]/", "",$str);
	}
	static function no_script($str){ //parse scripts tags. So you can protect against xss
		return preg_replace("/(<[^>]*script.*>)/", htmlentities("$1"),$str);
	}
	static function url_protocol($str){ //It forces a url protocol. If you have a url input, and user didnt't provide http:// it puts it on front. If user didn't provided .com or stuff, it puts .com in the end.
/*google return http://google.com
  google.com return http://google.com
  http://google return http://google.com
  https://google.com return https://google.com */

		if(!empty($str)){
	 		$url = filter_var($str, FILTER_SANITIZE_URL);
	 		if(!preg_match("/http|mailto|https|ssh/",$url)){
	 			$url = "http://$url";
	 		}
	 		if(!preg_match("/\./", $url)){
	 			$url = "$url.com";
	 		}
	 		return $url;
	 	} else {
	 		return '';
	 	}
	}
	static function sql($str){
		return mysql_real_escape_string($str); //Man i would suggest you don't trust this to much. Prepared sql querys are the best. Maybe you can use both?
	}
	static function no_accents_n_spaces($str){ //It also removes html tags and put strings to lower case.

		$a = array('À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å','á');
		$e = array('È','É','Ê','Ë','è','é','ê','ë');
		$c = array('Ç', 'ç');
		$i = array('Ì','Í','Î','Ï','ì','í','î','ï');
		$n = array('Ñ', 'ñ');
		$o = array('Ò','Ó','Ô','Õ','Ö','ò','ó','ô','õ','ö');
		$u = array('Ù','Ú','Û','Ü','ù','ú','û','ü');
		$y = array('Ý', 'ý','ÿ');
		$all = array('a' => $a, 'e' => $e, 'c' => $c, 'i' => $i, 'n' => $n, 'o' => $o, 'u' => $u, 'y' => $y );	
		$var = strtolower($str);
	    foreach($all as $k=>$tmp){
			foreach($tmp as $l){
			${$k}[] = htmlentities($l,ENT_COMPAT,'UTF-8');
			$var = str_replace(${$k},$k, $var);
			}
		}
		$var = str_replace(" ","-", $var);
		
		return $var;
	}
	static function color($str){
		return preg_replace("/[^A-z,()#\d]/", "",$str);
	}
}
class data {
	static function get($value,$sanitize=false,$validate=false, $validateParams = false){
		if(isset($_GET[$value])){
			return self::cleaner($_GET[$value],$sanitize,$validate,$validateParams);	
		} else {
			return false;
		}
		
	}
	static function post($value,$sanitize=false,$validate=false, $validateParams = false){
		if(isset($_POST[$value])){
			return self::cleaner($_POST[$value],$sanitize,$validate,$validateParams);	
		} else {
			return false;
		}
	}
	static function session($value,$sanitize=false,$validate=false, $validateParams = false){
		if(isset($_SESSION[$value])){
			return self::cleaner($_SESSION[$value],$sanitize,$validate,$validateParams);	
		} else {
			return false;
		}
		
	}
	static function cookie($value,$sanitize=false,$validate=false, $validateParams = false){
		if(isset($_COOKIE[$value])){
			return self::cleaner($_COOKIE[$value],$sanitize,$validate,$validateParams);	
		} else {
			return false;
		}
		
	}
	static function cleaner($value,$sanitize=false,$validate=false,$validateParams=false){
		if(isset($value) and !empty($value)){
			if($sanitize){
				if(method_exists('sanitize', $sanitize)){
					$value = sanitize::$sanitize($value);
					if($validate){
						if(method_exists('validate', $validate)){
							$params = array($value);
							if(is_array($validateParams)){
								$params = array_merge($params, $validateParams);
							}
							$valida = call_user_func_array(array("validate", $validate), $params);
							if(!$valida){
								return false;
							} else {
								return $value;
							}
						} else {
							$method = $validate[0];
							throw new Exception("Erro ao validar $att, método de validação $method não existe =(", 1);
						}
					} else{
						return $value;
					}
				} else {	
					throw new Exception("Error method $sanitize doesn't exist into sanitizer class", 1);
				}
			} else {
				return $value;
			}
		} else {
			return false;
		}
	}
}

?>