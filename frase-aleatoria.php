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
 
 

/* Función que incluye los archivos necesarios para activar colorpicker en la parte de administración */
if(!function_exists("mj_incluir_color_pickers")){

	function mj_incluir_color_pickers( $hook_suffix ) {
    
    	wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'mj-color-pickers', plugins_url('js/activa-color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
}

add_action( 'admin_enqueue_scripts', 'mj_incluir_color_pickers' );



/*Función que controla que cuando se escoge la búsqueda libre, se seleccione la categoria cualquiera*/
if(!function_exists("mj_control_opciones")){

	function mj_control_opciones(){

		$buscar=get_option('buscar');

		if($buscar!=""){
			update_option('categoria', 'cualquiera');
		}
	}
}

add_action('admin_menu','mj_control_opciones');


 
/* Función obtiene la frase de la api según las opciones de configuración escogidas
 * Retorna string con la frase */
if(!function_exists("mj_frase_aleatoria")){

	function mj_frase_aleatoria() {

		$categoria=get_option('categoria');
		$buscar=get_option('buscar');
		$color=esc_attr(get_option('color'));

		//si buscar esta vacio buscamos una frase al azar o por categoria
		if($buscar==""){

			//si la categoria es cualquiera buscamos una frase al azar en general
			if($categoria=="cualquiera"){
				$url='https://api.chucknorris.io/jokes/random';

			}else{	//si se selecciona una categoria buscamos una frase al azar por esa categoria
				$url="https://api.chucknorris.io/jokes/random?category=".$categoria;
			}

			$respuesta= wp_remote_get(esc_url_raw($url));
	 
	 		// convertimos la respuesta en un array (solo el cuerpo de la respuesta) 
		 	$api_respuesta=json_decode(wp_remote_retrieve_body($respuesta),true );
		 	$bloque_frase="<div class='frase-aleatoria' style='color:".$color."'>".$api_respuesta['value']."</div>";

		}else{// si buscar contiene algo, buscamos las frases relacionadas y mostramos una al azar
			$url='https://api.chucknorris.io/jokes/search?query='.$buscar;

			$respuesta= wp_remote_get(esc_url_raw($url));
	 
		 	// convertimos la respuesta en un array (solo el cuerpo de la respuesta)
		 	$api_respuesta=json_decode(wp_remote_retrieve_body($respuesta),true );
		 	
		 	//escogemos solo una frase relacionada al azar
		 	$num_total_frases=$api_respuesta['total']-1;
		 	$aleatorio=rand(0,$num_total_frases);
		 	$bloque_frase="<div class='frase-aleatoria' style='color:".$color."'>".$api_respuesta['result'][$aleatorio]['value']."</div>";
		}

	 	return $bloque_frase;
	}	
}



/* Función que obtiene las categorias disponibles en la API e incluye la categoria cualquiera
 * Retorna un array con todas las categorias
 */
if(!function_exists("mj_categorias_frase_aleatoria")){

	function mj_categorias_frase_aleatoria() {

		$url="https://api.chucknorris.io/jokes/categories";
		$respuesta= wp_remote_get(esc_url_raw($url));

		/* convertimos la respuesta en un array (solo el cuerpo de la respuesta) */
		$api_respuesta=json_decode(wp_remote_retrieve_body($respuesta),true );
		array_push ($api_respuesta , 'cualquiera');
		
		return $api_respuesta;
	}	
}



/* Función para añadir una página al menú de administrador de wordpress */
if(!function_exists("mj_menu_plugin")){

	function mj_menu_plugin(){

		add_menu_page(	'Ajustes plugin Frase Aleatoria',			//Título de la página
						'Frase Aleatoria',							//Título del menú
						'administrator',							//Rol que puede acceder
					  	'opciones_frase_aleatoria',					//Id de la página de opciones
					  	'mj_pagina_opciones_frase_aleatoria',		//Función que muestra el formulario de configuración del plugin
					  	'dashicons-admin-generic');					//Icono del menú
	}
}

add_action('admin_menu','mj_menu_plugin');



/* Función que muestra el formulario de opciones de configuración del plugin. 
 * Manteniendo la estética de los menús de wordpress 
 */
if(!function_exists("mj_pagina_opciones_frase_aleatoria")){

	function mj_pagina_opciones_frase_aleatoria(){

		//obtenemos todas las categorias de la API
		$categorias=mj_categorias_frase_aleatoria();
		?>
			<div class="wrap">
				<h2>Configuración de Frase Aleatoria</h2>
				<form method="post" action="options.php">
					<?php 
						//definimos el nombre del grupo que contiene los campos 
						settings_fields('opciones_frase_aleatoria-group');
						@do_settings_sections('opciones_frase_aleatoria-group' ); 
					?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="color"> Color del texto: </label>
								</th>
								<td>
									<input type="text" name="color" id="color" data-default-color="#dd8500" value="<?php echo esc_attr(get_option('color')); ?>" class="my-color-picker" />
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="categoria"> Categoria: </label>
								</th>
								<td>
									<select name="categoria" id="categoria">
										<?php
											$categoria_seleccionada=get_option('categoria');

											foreach ($categorias as $categoria) {

												if($categoria_seleccionada==$categoria){
													echo '<option value="'.$categoria.'" selected>'.ucwords($categoria).'</option>';
												}else{
													echo '<option value="'.$categoria.'">'.ucwords($categoria).'</option>';
												}
											}
									?>
									</select>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="buscar">Buscar por: </label>
								</th>
								<td>
									<input type="text" name="buscar" id="buscar" value="<?php echo esc_attr(get_option('buscar')); ?>" />
									<p class="description">Si rellena este campo, la categoria será cualquiera</p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php @submit_button()?>				
				</form>
			</div>
		<?php
	}
}



/* Función que registra las opciones del formulario de la configuración del plugin */
if(!function_exists("mj_opciones_frase_aleatoria_init")){

	function mj_opciones_frase_aleatoria_init(){
		register_setting('opciones_frase_aleatoria-group','color');
		register_setting('opciones_frase_aleatoria-group','categoria');
		register_setting('opciones_frase_aleatoria-group','buscar');
	}
}

add_action('admin_init','mj_opciones_frase_aleatoria_init');

?> 