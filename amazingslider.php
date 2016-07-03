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


//<---Agregado admin_slides
include ('admin_slides/admin_slides.php'); 
//

define('AMAZINGSLIDER_VERSION_3', '4.2');

define('AMAZINGSLIDER_URL_3', plugin_dir_url( __FILE__ ));

define('AMAZINGSLIDER_PATH_3', plugin_dir_path( __FILE__ ));

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
		if ( preg_match($pattern, $content, $matches) )
			$slidercode = str_replace("%DESTURL%", $dest_url, $matches[1]);
		
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
		
		<?php

		//<---Agregado admin_slides
		$adminSlides = new adminSlides();
		// 
	}
	
}

$amazingslider_plugin_3 = new AmazingSlider_Plugin_3();
