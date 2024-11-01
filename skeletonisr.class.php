<?php 

/*
Plugin Name: Skeletonisr
Plugin URI: http://labs.d2tstudio.com/wordpress/plugins/skeletonisr/
Description: Simple. Bootstraps your theme with the Skeleton Framework.
Version: 1.2
Author: Derry Birkett for D2T Studio
Author URI: http://derrybirkett.com
Author Email: me@derrybirkett.com
License: GPL2

*/

class skeletonisr
	{
	
	private $basename;
	private $text_domain;
	private $locale_folder;
	private $nicename;
	private $short_description;
	private $long_description;	
    private $prefix; 
    private $affix;
    
    private $plugin_path;
    private $plugin_url;
    private $plugin_folder;
    private $plugin_filename;  

	function __construct()
		{
		
			/*--------------------------------------------------*/
			/* Basename this bad boy!
			/*--------------------------------------------------*/
			$this->basename					= "skeletonisr";			// Used for code reference
	        
			/*--------------------------------------------------*/
			/* l10n
			/*--------------------------------------------------*/
		    // 1. Set local folder
		    $this->i10n_dir = "locale";
		        
		    // 2. Set text domain
			if(defined('FRAMEWORK')) 
				{
					$this->text_domain = FRAMEWORK;
				} else {
					$this->text_domain = $this->basename;
				}
					    
			/*--------------------------------------------------*/
			/* With L10n we can now also play nice
			/*--------------------------------------------------*/					        
			// Give it a nicename for user facing purposes
	        $this->nicename					= __(ucfirst($this->basename),$this->text_domain); 	// Used for titles and user facing stuff
	        
	        // What are you?
	        $this->short_description		= __('Bootstrap with Skeleton Framework.',$this->text_domain);	
	        $this->long_description			= __('Bootstrap with Skeleton Framework.',$this->text_domain);		

			/*--------------------------------------------------*/
			/* Let the world know we exist
			/*--------------------------------------------------*/
			define($this->basename, true);
		
			/*--------------------------------------------------*/
			/* Our local vars & some widget vars (for widgets)
			/*--------------------------------------------------*/
	        $this->prefix				= 		$this->basename 	. "_";
	        $this->affix				= "_" . $this->basename;
	        $this->plugin_folder		= 		$this->basename 	. "/";	       
	        $this->capability			= "activate_plugins"; // Set access level

			/*--------------------------------------------------*/
			/* For widgets - If necessary
			/*--------------------------------------------------*/
	        $this->widget_title			= $this->nicename;
	        $this->widget_description 	= $this->long_description;
	        $this->widget_name			= $this->basename;
	        $this->use_views			= false;

			/*--------------------------------------------------*/
			/* FRAMEWORK SWITCH
			/* ----------------
			/* Vars switch if plugin is bundled with my framework
			/*--------------------------------------------------*/
			if(defined('XFRAME'))
				{
					// Setup defaults for use in framework
					$this->plugin_filename 	= __FILE__;	
					$this->plugin_path 		= trailingslashit(XFRAME_PLUGINS_DIR) . $this->plugin_folder;
					$this->plugin_url 		= trailingslashit(XFRAME_PLUGINS_URI) . $this->plugin_folder;
					$this->locale			= $this->plugin_path . "/" . $this->plugin_folder . "/". $this->i10n_dir ."/";	        

				} else {
		        	// Set up default vars as isolated plugin
		        	$this->plugin_filename 	= __FILE__;
		        	$this->plugin_path 		= plugin_dir_path( __FILE__ );
		        	$this->plugin_url 		= plugin_dir_url( __FILE__ );
		        	$this->locale			= dirname( plugin_basename( __FILE__ )) . "/" . $this->i10n_dir  . "/";
		        	
				}
	

			/*--------------------------------------------------*/
			/* WORDPRESS Hooks
			/*--------------------------------------------------*/
	        register_activation_hook( $this->plugin_filename, array(&$this, 'activate') );
	        register_deactivation_hook( $this->plugin_filename, array(&$this, 'deactivate') );
	        
	        // Set up l10n
			load_plugin_textdomain( $this->text_domain, false, $this->locale );
	        
	        // Hook
	        add_action( 'init', array(&$this, 'init') );
		}


    function activate( $network_wide ) 
   		 {
        
	   	 }
    
    function deactivate( $network_wide ) 
    	{
        
    	}
    
    function init()
    	{
    	
	        // Enqueue plugin files (if not in admin side)
	       	if ( !is_admin() )	 $this->d2t_enqueue_plugin_files();
	       	
				       	
			// PressTrends WordPress Action
			add_action('admin_init', array($this, 'presstrends_Skeletonisr_plugin'));

        
    	}		
	function d2t_enqueue_plugin_files()
		{
			/********************
			* LOAD PLUGIN ASSETS
			* Load assets required for chosen plugin
			* v1.0
			* Copyright 2012 Derry Birkett for d2tstudio.com
			********************/				

			wp_enqueue_style('skeleton_base', 			$this->plugin_url 	. 'Skeleton/stylesheets/base.css', 			false, '1.0');
			wp_enqueue_style('skeleton_css',			$this->plugin_url 	. 'Skeleton/stylesheets/skeleton.css', 		false, '1.0');
			// wp_enqueue_style('skeleton_layout',			$this->plugin_url 	. 'Skeleton/stylesheets/layout.css', 		false, '1.0');			

		}	
		
		/**
		* PressTrends Plugin API
		*/
			function presstrends_Skeletonisr_plugin() {
		
				// PressTrends Account API Key
				$api_key = '5qe0bv7336ou5jyqflmluqb5yiwodyc5pzg8';
				$auth    = '97y4tmep7dgok2h2b1wg24z06zkwzwk3h';
		
				// Start of Metrics
				global $wpdb;
				$data = get_transient( 'presstrends_cache_data' );
				if ( !$data || $data == '' ) {
					$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
					$url      = $api_base . $auth . '/api/' . $api_key . '/';
		
					$count_posts    = wp_count_posts();
					$count_pages    = wp_count_posts( 'page' );
					$comments_count = wp_count_comments();
		
					// wp_get_theme was introduced in 3.4, for compatibility with older versions, let's do a workaround for now.
					if ( function_exists( 'wp_get_theme' ) ) {
						$theme_data = wp_get_theme();
						$theme_name = urlencode( $theme_data->Name );
					} else {
						$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
						$theme_name = $theme_data['Name'];
					}
		
					$plugin_name = '&';
					foreach ( get_plugins() as $plugin_info ) {
						$plugin_name .= $plugin_info['Name'] . '&';
					}
					// CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
					$plugin_data         = get_plugin_data( __FILE__ );
					$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
					$data                = array(
						'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
						'posts'           => $count_posts->publish,
						'pages'           => $count_pages->publish,
						'comments'        => $comments_count->total_comments,
						'approved'        => $comments_count->approved,
						'spam'            => $comments_count->spam,
						'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
						'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
						'theme_version'   => $plugin_data['Version'],
						'theme_name'      => $theme_name,
						'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
						'plugins'         => count( get_option( 'active_plugins' ) ),
						'plugin'          => urlencode( $plugin_name ),
						'wpversion'       => get_bloginfo( 'version' ),
					);
		
					foreach ( $data as $k => $v ) {
						$url .= $k . '/' . $v . '/';
					}
					wp_remote_get( $url );
					set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
				}
			}

	}
new skeletonisr();  
?>