<?php

/*
	Descripcion : 	Archivo complemento para la administración de slides en el plugin amazinslider,
					básicamente se trabaja sobre el archivo slider.html, leyendo el archivo y obteniendo los slides
					y luego grabando los slides modificados en este mismo archivo.

	Autor 	: Jhon Marreros Guzmán
	Web 	: http://decodecms.com
	Correo 	: jmarreros@gmail.com
*/

include('simple_html_dom.php'); //dependencia para facilitar la lectura del DOM

//Configurar rutas de ser necesario
define('RUTA_DATA_INICIAL',plugin_dir_url( __FILE__ ).'../data/'); 
define('RUTA_ARCHIVO_HTML',plugin_dir_path( __FILE__ ).'../data/slider.html');
define('SIN_THUMBNAILS',true);

//Agregar funcionalidad de ventana de medios de WordPress
function load_wp_media_files() {
  wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );


/*
Clase Principal, al instanciar se leen los dos principales métodos
*/
class adminSlides
{

	function __construct() {

		$this->borrarThumbnails();
		$this->guardarDatosArchivo();
		$this->crearElementosAdicionales();
	
	}

	/*Funcion para borrar los Thumbnails para que no se muestren
	miniaturas incompletas, esta función depende de la asignación de la constante 	asignación SIN_THUMBNAILS */
	private function borrarThumbnails()
	{
		if ('SIN_THUMBNAILS')
		{
			$nuevosThumbnails	= "";
			$contenidoArchivo 	= file_get_contents(RUTA_ARCHIVO_HTML);
			$patron 			= '/(\<ul class\=\"amazingslider\-thumbnails\".*?\>)(.*?)(\<\/ul\>)/s';

			//Verificamos si existe este patron y capturamos los grupos
			if ( preg_match_all($patron,$contenidoArchivo,$coincidencias) )
			{
				//Verificamos si tiene contenido, entonces reemplazamos.
				if ( $coincidencias[1] )
				{
					$reemplazar 		= '$1'.$nuevosThumbnails.'$3';

					$contenidoArchivo = preg_replace($patron,$reemplazar,$contenidoArchivo);

					//Abrimos el archivo para escritura.
					try
					{
						$archivo = fopen(RUTA_ARCHIVO_HTML, "w");
						fwrite($archivo , $contenidoArchivo);
						fclose($archivo);
						
					}
					catch(Exception $e)
					{
						echo $this->mensaje("Error al eliminar miniaturas: ".$e->getMessage(),true);
					}

				} //coincidencia[1]

			} //preg_match_all

		}//SIN_THUMBNAILS

	}

	/*
	Crea la interfaz adicional para el plugin, agrega estilos, javascript y los controles
	del formulario en una estructura de tabla necesaria para administrar los slides.
	*/
	private function crearElementosAdicionales()
	{
		?>
		
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
			.slides .eliminar{
				margin-left: 10px;
				margin-top: -8px;
			}
			.button.agregar{
				margin-bottom:20px;
			}
		</style>

		<br/>
		<h2>Configurar Slides</h2>	
		<a href="#" class="button agregar"  >Agregar Slide</a>
		<a href="#" class="button refrescar" >Refrescar Slides</a>

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
					<strong>SLIDE </strong><a class="button eliminar" href="#">Eliminar</a><hr/>
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
				
				//Ventana de Medios
				$('.slide .upload_image_button').live('click',function() {

					var frame,
						contenedor 	= $(this).parent(),
						ruta 		= $('.upload_image', contenedor);

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


				//Agregar Slide
				$('.agregar').click(function(e){
					e.preventDefault();
					
					if ($('.slides .slide').length <= 12){

						slide = $('.slides .slide').last();

						if ( slide.find('.upload_image').val() !== '' ){

							slide = $('.slides .slide').last().clone();
							slide.find('input[type="text"]').val('');
							slide.appendTo('.slides');

						}
						else{
							alert("Debe colocar una imagen en el último slide");
						}
					}
					else{
						alert("Sólo puede haber 12 slides como máximo");
					}

				});


				//Eliminar Slide
				$('.slide .eliminar').live('click',function(e){
					e.preventDefault();

					if ( $('.slides .slide').length > 1 ){
						slide = $(this).parent().parent();
						slide.remove();
					}
					else{
						alert("Debe haber por lo menos 1 slide");
					}

				});

				//Refrescar Slides
				$('.refrescar').click(function(e){
					e.preventDefault();
					location.reload();
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


}//Fin Clase adminSlides