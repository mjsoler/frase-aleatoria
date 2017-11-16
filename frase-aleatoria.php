<?php
/*
Plugin Name: Frase-aleatoria
Plugin URI: https://github.com/sgmj-web/frase-aleatoria
Description: Plugin que muestra una frase aleatoria en la cabecera del sitio web. La frase la obtiene de la API: https://api.chucknorris.io/
Version: 1.0
Author: María Jesús Soler
Author URI: http://mariajesussoler.esy.es/
License: MIT License
*/
 
 
 
/* Función que incluye el css del plugin en la cabecera */
if(!function_exists("mj_incluir_css")){

	function mj_incluir_css() {

		wp_enqueue_style('mj-frase-aleatoria-style',plugins_url('css/frase-aleatoria.css',__FILE__), false ); 
	}
}

add_action('wp_enqueue_scripts', 'mj_incluir_css');
 
 
 
/* Función que incluye el js del plugin al final de la página y hace accesible la variable que contiene la frase desde el js */
if(!function_exists("mj_incluir_script")){

	function mj_incluir_script() {

		$bloque_frase=mj_frase_aleatoria();

		wp_enqueue_script('mj-frase-aleatoria',plugins_url('js/frase-aleatoria.js',__FILE__),array('jquery'), '1',true);
		wp_localize_script('mj-frase-aleatoria','mj_frase',array('mensaje'=>$bloque_frase));
	}
}

add_action('wp_enqueue_scripts', 'mj_incluir_script');
 
 
 
/* Función obtiene la frase de la api (la general)
   Retorna string con la frase */
if(!function_exists("mj_frase_aleatoria")){
	
	function mj_frase_aleatoria() {
	 
	 	
		$url='https://api.chucknorris.io/jokes/random';
		$respuesta= wp_remote_get(esc_url_raw($url));
	 
	 	/* convertimos la respuesta en un array (solo el cuerpo de la respuesta) */
	 	$api_respuesta=json_decode(wp_remote_retrieve_body($respuesta),true );
	 	$bloque_frase="<div class='frase-aleatoria'>".$api_respuesta['value']."</div>";
	 
	 	return $bloque_frase;
	}	
}
?> 