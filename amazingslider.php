<?php
/*
Plugin Name: Amazing Slider 3
Plugin URI: http://amazingslider.com
Description: Amazing Slider WordPress Plugin 3
Version: 4.2
Author: Magic Hills Pty Ltd
Author URI: http://amazingslider.com
License: Copyright 2013 Magic Hills Pty Ltd, All Rights Reserved
*/

define('AMAZINGSLIDER_VERSION_3', '4.2');

define('AMAZINGSLIDER_URL_3', plugin_dir_url( __FILE__ ));

define('AMAZINGSLIDER_PATH_3', plugin_dir_path( __FILE__ ));

//
//--- Codigo Agregado
define('RUTA_DATA_INICIAL',plugin_dir_url( __FILE__ ).'data/');
define('RUTA_ARCHIVO_HTML',plugin_dir_path( __FILE__ ).'data/slider.html');

include('simple_html_dom.php');

function load_wp_media_files() {
  wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );


//--- Fin codigo agregado
//


class AmazingSlider_Plugin_3
{
		
	function __construct() {
		
		$this->init();
	}
	
	public function init()
	{
				
		add_action( 'admin_menu', array($this, 'register_menu') );
		
		add_shortcode( 'amazingslider3', array($this, 'shortcode_handler') );
		
		add_action( 'init', array($this, 'register_script') );
	}
		
	function register_script()
	{
		$script_url = AMAZINGSLIDER_URL_3 . '/data/sliderengine/amazingslider.js';
		wp_register_script('amazingslider-script-3', $script_url, array('jquery'), AMAZINGSLIDER_VERSION_3, false);
		wp_enqueue_script('amazingslider-script-3');
		
		$initscript_url = AMAZINGSLIDER_URL_3 . '/data/sliderengine/initslider-3.js';
		wp_register_script('amazingslider-initscript-3', $initscript_url, array('jquery'), AMAZINGSLIDER_VERSION_3, false);
		wp_enqueue_script('amazingslider-initscript-3');
		
		$style_url = AMAZINGSLIDER_URL_3 . 'data/sliderengine/amazingslider-3.css';
		wp_register_style('amazingslider-style-3', $style_url);
		wp_enqueue_style('amazingslider-style-3');
		
		if ( is_admin() )
		{
			wp_register_style('amazingslider-admin-style-3', AMAZINGSLIDER_URL_3 . 'amazingslider.css');
			wp_enqueue_style('amazingslider-admin-style-3');
		}
	}
	
	function shortcode_handler($atts)
	{
		return $this->generate_codes();
	}
	
	function generate_codes()
	{
		$file = AMAZINGSLIDER_PATH_3 . '/data/slider.html';
		$content = file_get_contents($file);
		
		$dest_url = AMAZINGSLIDER_URL_3 . '/data/';
		
		$slidercode = "";
		$pattern = '/<!-- Insert to your webpage where you want to display the slider -->(.*)<!-- End of body section HTML codes -->/s';

		if ( preg_match($pattern, $content, $matches) ){
			$slidercode = str_replace("%DESTURL%", $dest_url, $matches[1]);
		}
		
		return $slidercode;
	}
	
	function register_menu()
	{
		add_menu_page( 
				__('Amazing Slider 3', 'amazingslider3'), 
				__('Amazing Slider 3', 'amazingslider3'), 
				'manage_options', 
				'amazingslider3_show_slider', 
				array($this, 'show_slider'),
				AMAZINGSLIDER_URL_3 . 'images/logo-16.png' );
				
	}
	
	public function show_slider()
	{
		
		?>
		<div class="wrap">
		<div id="icon-amazingslider" class="icon32"><br /></div>
			
		<h2><?php _e( 'View Slider', 'amazingslider3' ); ?></h2>
				
		<div class="updated"><p style="text-align:center;"> To embed the slider into your page, use shortcode <strong><?php echo esc_attr('[amazingslider3]'); ?></strong></p></div>
		
		<div class="updated"><p style="text-align:center;"> To embed the slider into your template, use php code <strong><?php echo esc_attr('<?php echo do_shortcode(\'[amazingslider3]\'); ?>'); ?></strong></p></div>
				
		<?php
			echo $this->generate_codes();
		?>

		</div>
		
		<?php $this->guardarDatosArchivo(); ?>
		<?php $this->crearElementosAdicionales(); ?>

		<?php
	}


	/*
	Crea la interfaz adicional para el plugin, agrega estilos, javascript y los controles
	del formulario en una estructura de tabla necesaria para administrar los slides.
	*/
	private function crearElementosAdicionales()
	{
		?>
		
		<br/>
		<h2>Configurar Slides</h2>	

		<style>
			.slides label{
				display: inline-block;
				min-width: 70px;
			}
			.slides tr{
				display: block;
				padding:10px;
				padding-bottom: 20px;
				margin-bottom: 10px;
				border : 1px solid #cecece;
			}
		</style>


		<?php $this->scriptMediaWordpress() ?>


		<form name="form_guardar" method="post" action="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>" >
			<table class="slides">
				<?php $this->leerDatosArchivo(); ?>
			</table>

			<input class="button button-primary" name="guardar" type="submit" value="Grabar Datos" />
		</form>
		<?php
	}



	/*
	Funcion para mostrar los controles de formulario 
	con los datos obtenidos del archivo slider.html
	*/
	private function leerDatosArchivo()
	{

		$plantillaControles	= '	
			<tr valign="top" class="slide">
				<td>
					<strong>SLIDE </strong><a href="#">Eliminar</a><hr/>
					<label>Url</label> : <input class="upload_url" type="text" size="36" name="upload_url[]" value="{url}"   /><br/>
					<label>Titulo</label> : <input class="upload_title" type="text" size="36" name="upload_title[]" value="{titulo}"   /><br/>
					<label>Imagen</label> : <input class="upload_image" type="text" size="36" name="upload_image[]" value="{imagen}"   />
					<input class="upload_image_button button button-primary" type="button" value="Subir Imagen" />
				</td>
			</tr>';

		$html 		= file_get_html( RUTA_ARCHIVO_HTML );
		$urlInicial	= RUTA_DATA_INICIAL;

		foreach($html->find('.amazingslider-slides li') as $e)
		{
			
			$e_url 		= $e->find('a')[0];
			$url 		= $e_url->href;
			$e_imagen 	= $e->find('img')[0];
			$imagen 	= str_replace("%DESTURL%", $urlInicial,$e_imagen->src);
			$titulo 	= $e_imagen->title;

			$buscar 	= Array("{url}","{titulo}","{imagen}");
			$reemplazar = Array($url,$titulo,$imagen);

			$controles 	= str_replace($buscar, $reemplazar, $plantillaControles);

			echo $controles;
		}

	} // Fin leerDatosArchivo


	/*
	Funcion para guardar los datos en el archivo slider.html
	obtenidos de lo que se a cambiado en los controles generados
	*/
	private function guardarDatosArchivo()
	{

		if (isset($_POST['guardar']))
		{
			$plantilla 	= '<li><a href="{url}"><img src="{imagen}" title="{titulo}" alt="{titulo}" /></a></li>'; //alt , title : tienen el mismo valor
			$nuevoContenido  ="";
			
			foreach($_POST['upload_image'] as $i=>$imagen)
			{
				$url 	= $_POST['upload_url'][$i]?esc_url($_POST['upload_url'][$i]):"#";
				$titulo = sanitize_text_field($_POST['upload_title'][$i]);
				$imagen = esc_url($imagen);

				$buscar 	= Array("{url}","{imagen}","{titulo}");
				$reemplazar = Array($url,$imagen,$titulo);

				$nuevoContenido .= str_replace($buscar, $reemplazar , $plantilla);
			}

			$contenidoArchivo 	= file_get_contents(RUTA_ARCHIVO_HTML);
			$patron 			= '/(\<ul class\=\"amazingslider\-slides\".*?\>)(.*?)(\<\/ul\>)/s';
			$reemplazar 		= '$1'.$nuevoContenido.'$3';

			$contenidoArchivo = preg_replace($patron,$reemplazar,$contenidoArchivo);

			//Abrimos el archivo para escritura.
			try
			{
				$archivo = fopen(RUTA_ARCHIVO_HTML, "w");
				fwrite($archivo , $contenidoArchivo);
				fclose($archivo);
				
				echo $this->mensaje("Se guardaron correctamente los cambios");								
			}
			catch(Exception $e)
			{
				echo $this->mensaje("Error : ".$e->getMessage(),true);
			}

		}

	} // Fin guardarDatosArchivo


	/*
	Script para que aparezca la ventana de medios de Wordpress
	y poder subir una nueva imagen o buscar una existente
	*/
	private function scriptMediaWordpress()
	{
		?>
			<script language="JavaScript">

			jQuery(document).ready(function($){
				
				$('.slide').on('click','.upload_image_button',function() {

					var frame,
						slide 	= $(this).parent(), 
						ruta 	= $('.upload_image', slide);

				    if ( frame ) {
				      frame.open();
				      return;
				    }

				    frame = wp.media({
				      title: 'Selecciona una imagen para el Slide',
				      button: {
				        text: 'Usar esta imagen'
				      },
				      multiple: false
				    });

				    frame.on( 'select', function() {
				      
				      var attachment = frame.state().get('selection').first().toJSON();
				      ruta.val(attachment.url);

				    });

				    frame.open();

				});

			});

		</script>
		<?php
	} // Fin scriptMediaWordpress


	/*
	Funcion para emitir un mensaje de exito o error
	*/
	private function mensaje( $mensaje , $error=false )
	{
		$plantilla 	= '<div class="{clase}"><p style="text-align:center;"><strong>{mensaje}</strong></p></div>';
		$clase 		= $error?"error notice":"updated";		
		
		$mensaje 	= str_replace(Array("{clase}","{mensaje}"), Array($clase,$mensaje), $plantilla);

		return $mensaje;

	} //Fin mensaje




	
}

$amazingslider_plugin_3 = new AmazingSlider_Plugin_3();

