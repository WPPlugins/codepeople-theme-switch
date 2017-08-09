<?php
/*
Plugin Name: Mobile Theme Switch
Plugin URI: http://wordpress.dwbooster.com/content-tools/theme-switch-in-mobile-and-desktop
Version: 1.0.3
Author: CodePeople
Author URI: http://wordpress.dwbooster.com/content-tools/theme-switch-in-mobile-and-desktop
Text Domain: codepeople-theme-switch-text
Description: "Mobile Theme Switch" allows to swap the active theme to another one, when your website is loaded on a mobile device. With "Mobile Theme Switch" plugin, you can select different themes dependent to the screen width, without having to activate the theme previously. "Mobile Theme Switch" will use the correct theme for the conditions where the website was loaded.
*/

require_once 'banner.php';
$codepeople_promote_banner_plugins[ 'codepeople-mobile-theme-switch' ] = array(
	'plugin_name' => 'Theme Switch in Mobile and Desktop',
	'plugin_url'  => 'https://wordpress.org/support/plugin/codepeople-theme-switch/reviews/#new-post'
);


if(!function_exists('cpts_get_site_url')){
    function cpts_get_site_url(){
        $url_parts = parse_url(get_site_url());
        return rtrim(
                        ((!empty($url_parts["scheme"])) ? $url_parts["scheme"] : "http")."://".
                        $_SERVER["HTTP_HOST"].
                        ((!empty($url_parts["path"])) ? $url_parts["path"] : ""),
                        "/"
                    )."/";
    }
}

if( !function_exists( 'codepeople_theme_switch_register' ) )
{
	function codepeople_theme_switch_register( $theme_name )
	{


		if( is_admin() )
		{
			$themes = wp_get_themes();
			foreach( $themes as $index => $theme )
			{
				if( $theme->name == $theme_name )
				{
					set_transient( 'codepeople_theme_switch_registered', $index, 0 );
					break;
				}
			}
		}
	}
}

add_action('switch_theme', 'codepeople_theme_switch_register');
add_action('admin_menu', 'codepeople_theme_switch_menu');

// Initialize the admin panel
if (!function_exists("codepeople_theme_switch_menu")) {
	function codepeople_theme_switch_menu() {
		if (function_exists('add_options_page')) {
			if( is_admin() )
			{
				add_options_page('Theme Switch in Mobile and Desktop', 'Theme Switch in Mobile and Desktop', 'manage_options', 'codepeople_theme_switch_slug', 'codepeople_theme_switch_admin_page');
			}
		}
	}
}

if(!function_exists('codepeople_theme_switch_valid_page')){
	function codepeople_theme_switch_valid_page()
	{
		return (!is_admin() && (empty( $GLOBALS['pagenow'] ) || !in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) && empty( $_REQUEST[ 'theme_switch_preview' ] ) && empty( $_REQUEST[ 'theme_switch_stylesheet' ] ) );
	}
}

// Print and process the admin page
if(!function_exists('codepeople_theme_switch_admin_page')){
	function codepeople_theme_switch_admin_page(){
		$plugin_dir = plugin_dir_url(__FILE__);
		$mssg = '';
		if( isset( $_POST['cpts_settings'] ) && wp_verify_nonce( $_POST['cpts_settings'], plugin_basename( __FILE__ ) ) )
		{
			$cpts_profiles = array();

			if( !empty( $_POST[ 'cpts_screen_width' ] ) )
			{
				$cpts_profile = new stdClass;
				$cpts_profile->theme = $_POST[ 'cpts_theme' ];
				$cpts_profile->screen = $_POST[ 'cpts_predefined_screen' ];
				$cpts_profile->width = $_POST[ 'cpts_screen_width' ];
				$cpts_profiles[] = $cpts_profile;
			}

			$cpts_options = array(
				'profiles' 			=> $cpts_profiles,
				'loading_text'		=> $_POST[ 'cpts_loading_text' ],
				'not_in_crawler' 	=> ( ( isset( $_POST[ 'cpts_not_in_crawler' ] ) ) ? true : false ),
				'devices' 			=> ( ( isset( $_POST[ 'cpts_devices' ] ) ) ? $_POST[ 'cpts_devices' ] : 'all' )
			);

			update_option( 'cpts_options', $cpts_options );
			$mssg = '<div class="updated"><p><strong>'.__( 'Settings Updated', 'codepeople-theme-switch-text' ).'</strong></p></div>';
		}

		?>
		<p style="border:1px solid #E6DB55;margin-bottom:10px;padding:5px;background-color: #FFFFE0;">
			To get commercial version of Mobile Theme Switch, <a href="http://wordpress.dwbooster.com/content-tools/mobile-theme-switch" target="_blank">CLICK HERE</a><br />
			For reporting an issue or to request a customization, <a href="http://wordpress.dwbooster.com/contact-us" target="_blank">CLICK HERE</a><br />
			If you want test the premium version of Mobile Theme Switch go to the following links:<br/> <a href="http://demos.net-factor.com/mobile-theme-switch/wp-login.php" target="_blank">Administration area: Click to access the administration area demo</a><br/>
			<a href="http://demos.net-factor.com/mobile-theme-switch/" target="_blank">Public page: Click to access the Store Page</a>
		</p>
		<?php

		// Load the administration script
		wp_enqueue_style('codepeople-theme-switch-admin-style', $plugin_dir.'/styles/styles.css' );
		wp_enqueue_script('codepeople-theme-switch-admin-script', $plugin_dir.'/js/admin.js', array('jquery'));
		wp_localize_script('codepeople-theme-switch-admin-script', 'cptsObj', array( 'home' => cpts_get_site_url() ) );

		$predefined_screens = array(
								array(
									'id'	 => 'custom',
									'title'  => 'Custom Screen',
									'width'  => ''
								),
								array(
									'id'	 => 'screen1',
									'title'  => 'Screen width < 768px',
									'width'  => '768'
								),
								array(
									'id'	 => 'screen2',
									'title'  => 'Screen width < 480px',
									'width'  => '480'
								),
								array(
									'id'	 => 'screen3',
									'title'  => 'Screen width < 320px',
									'width'  => '320'
								),
								array(
									'id'	 => 'blackberrybold',
									'title'  => 'Screen width < 240px',
									'width'  => '240'
								)
							);

        $themes = wp_get_themes();
		$themes_uri = get_theme_root_uri();
		$active_theme = wp_get_theme();

		$cpts_options = get_option( 'cpts_options' );
		if( $cpts_options )
		{
			$cpts_profiles = $cpts_options[ 'profiles' ];
			$cpts_loading_text = $cpts_options[ 'loading_text' ];
			$cpts_not_in_crawler = ( isset( $cpts_options[ 'not_in_crawler' ] ) ) ? $cpts_options[ 'not_in_crawler' ] : true;
			$cpts_devices = ( isset( $cpts_options[ 'devices' ] ) ) ? $cpts_options[ 'devices' ] : 'all';

			foreach( $cpts_profiles as $key => $profile )
			{
				if( property_exists( $profile, 'theme' ) )
				{
					$selected_theme = wp_get_theme( $profile->theme );
					if( is_wp_error( $selected_theme ) )
					{
						unset( $cpts_profiles[ $key ] );
					}
				}
				else
				{
					unset( $cpts_profiles[ $key ] );
				}
			}

			if( !empty( $cpts_profiles ) )
			{
				$active_theme = wp_get_theme( $cpts_profiles[ 0 ]->theme );
			}
		}
		else
		{
			$cpts_profiles = array();
			$cpts_loading_text = 'Do you want load an optimized version of website for your screen?';
			$cpts_not_in_crawler = true;
			$cpts_devices = 'all';
		}

	print $mssg;
?>
	<form method="post" id="cpts_theme_switch_settings" >
		<h1>Theme Switch Settings</h1>

		<div class="postbox" style="margin-right:20px;">
            <h3 class='hndle' style="padding:5px;"><span>Theme to Load</span></h3>
			<div class="inside">
				<div class="cp-section">
					<div>
						<h4>Apply theme-switch to</h4>
						<div>
							<label style="margin-right:10px;"><input type="radio" name="cpts_devices" value="all" <?php if( !isset($cpts_devices) || $cpts_devices == 'all') print 'CHECKED'; ?> /> Desktop and Mobiles</label>
							<label style="margin-right:10px;"><input type="radio" name="cpts_devices" value="mobile" <?php if( isset($cpts_devices) && $cpts_devices == 'mobile') print 'CHECKED'; ?> /> only Mobiles</label>
							<label><input type="radio" name="cpts_devices" value="desktop" <?php if( isset($cpts_devices) && $cpts_devices == 'desktop') print 'CHECKED'; ?> /> only Desktop</label>
						</div>
					</div>

					<div class="cp-themes">
						<h4>Installed Themes</h4>
<?php
					foreach( $themes as $index => $theme )
					{
?>
						<div class="cp-theme">
							<div class="cp-theme-screenshot">
							<?php
								if( file_exists( $theme->theme_root.'/'.$theme->template.'/screenshot.png' ) )
								{
									print '<img src="'.$themes_uri.'/'.urlencode( $index ).'/screenshot.png" />';
								}
							?>
							</div>
							<div class="cp-theme-title">
								<input type="radio" name="cpts_theme" value="<?php print $index; ?>" template="<?php print $theme->template; ?>" stylesheet="<?php print $theme->stylesheet; ?>" <?php if( $active_theme->stylesheet == $theme->stylesheet ) echo 'CHECKED'; ?> /> <span><?php print $theme->Name; ?></span>
							</div>
						</div>
					<?php
					}
					?>
						<div class="clear"></div>
					</div><!-- End Themes-->
					<div class="cp-screen" >
						<h4>Screen Sizes</h4>
						<div>
							<select id="cpts_predefined_screen" name="cpts_predefined_screen" onchange="cptsLoadScreenSizes( this );" >
							<?php
							foreach( $predefined_screens as $screen )
							{
								$attr = '';
								if( !empty( $screen[ 'width' ] ) )
								{
									$attr = 'w="'.$screen[ 'width' ].'"';
								}
								print '<option '.$attr.' value="'.$screen[ 'id' ].'" '.( ( !empty( $cpts_profiles ) && $cpts_profiles[ 0 ]->screen == $screen[ 'id' ] ) ? 'SELECTED' : '' ).' >'.__( $screen[ 'title' ], 'codepeople-theme-switch-text' ).'</option>';
							}
							?>
							</select>
						</div>
						<div>
							<div><label> Width: <input type="text" id="cpts_screen_width" name="cpts_screen_width" class="short" value="<?php echo ( ( !empty( $cpts_profiles ) ) ? $cpts_profiles[ 0 ]->width : '' ); ?>" /> px </label></div>
						</div>
						<div>
							<input type="button" value="Preview" onclick="cptsDisplayPreview();" class="button" />
						</div>
						<div class="clear"></div>
					</div><!-- End Screen -->

				</div><!-- End Section -->
				<div class="cp-preview">
					<div class="cp-preview-container">
					</div>
				</div><!-- End Preview-->
				<div class="cp-profiles">
					<h4>Themes Selected</h4>
					<div style="color:Red;">The premium version of plugin to allows define multiple themes for different screen sizes, <a href="http://wordpress.dwbooster.com/content-tools/mobile-theme-switch" target="_blank">CLICK HERE</a></div>
				</div><!-- End Profiles -->
			</div><!-- End postbox inside -->
		</div><!-- End postbox -->
		<div class="postbox" style="margin-right:20px;">
            <h3 class='hndle' style="padding:5px;"><span>General Settings</span></h3>
			<div class="inside">
				<div><label style="color:#DADADA;"><input type="checkbox" DISABLED /> Load theme dynamically</label> <span style="color:Red;">The feature is available only in the premium version of Mobile Theme Switch, <a href="http://wordpress.dwbooster.com/content-tools/mobile-theme-switch" target="_blank">CLICK HERE</a></span></div>
				<div><label>Text to display when theme is not loaded dynamically<textarea name="cpts_loading_text" rows="6" style="width:100%;"><?php print $cpts_loading_text; ?></textarea></label></div>
				<div><label><input type="checkbox" name="cpts_not_in_crawler" <?php if( $cpts_not_in_crawler ) print 'CHECKED'; ?> /> Don't load the alternative themes with crawlers</label></div>
			</div><!-- End postbox inside -->
		</div><!-- End postbox -->
		<div><input type="submit" value="Save settings" class="button-primary" /></div>
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cpts_settings' ); ?>
	</form>
<?php
	}
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'codepeople_theme_switch_settings_link');

if(!function_exists('codepeople_theme_switch_settings_link')){
	function codepeople_theme_switch_settings_link($links){
		$settings_link = '<a href="options-general.php?page=codepeople_theme_switch_slug">'.__('Settings').'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
}

add_action( 'plugins_loaded', 'codepeople_theme_switch_init' );
add_action( 'setup_theme', 'codepeople_mobile_switch_theme_by_device' );

if( !function_exists( 'codepeople_theme_switch_init' ) )
{
	function codepeople_theme_switch_is_mobile()
	{
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		return ( preg_match( '/tablet|(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|nexus|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent ) || preg_match( '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4 ) ) );
	}

	function codepeople_theme_switch_init()
	{
		load_plugin_textdomain('codepeople-theme-switch-text', false, dirname(__FILE__) . '/languages/');

		if( session_id() == "" ) session_start();
		if( isset( $_REQUEST[ 'theme_switch_accepted' ] ) ) $_SESSION[ 'theme_switch_accepted' ] = 1;
		if( !empty( $_REQUEST[ 'theme_switch_width' ] ) && is_numeric($_REQUEST[ 'theme_switch_width' ]) ) $_SESSION[ 'theme_switch_width' ] = intval($_REQUEST[ 'theme_switch_width' ]);
		if( !empty( $_GET[ 'theme_switch_denied' ] ) ) $_SESSION[ 'theme_switch_denied' ] = $_GET[ 'theme_switch_denied' ];

		if( codepeople_theme_switch_valid_page() )
		{
			$cpts_options = get_option( 'cpts_options' );

			// Check if the webpage is being visited by a crawler
			if(
				isset( $_SERVER['HTTP_USER_AGENT'] ) &&
				preg_match( '/bot|crawl|slurp|spider/i', $_SERVER[ 'HTTP_USER_AGENT' ] ) &&
				(
					!isset( $cpts_options['not_in_crawler' ] ) ||
					$cpts_options['not_in_crawler' ]
				)
			)
			{
				return;
			}

			if(
				empty( $_SESSION[ 'theme_switch_denied' ] ) &&
				$cpts_options &&
				count( $cpts_options[ 'profiles' ] ) &&
				(
					!isset( $cpts_options[ 'devices' ] ) ||
					$cpts_options[ 'devices' ] == 'all' ||
					($cpts_options[ 'devices' ] == 'mobile' && codepeople_theme_switch_is_mobile() ) ||
					($cpts_options[ 'devices' ] == 'desktop' && !codepeople_theme_switch_is_mobile() )

				)
			)
			{
				$width = 0;
				foreach( $cpts_options[ 'profiles' ] as $profile )
				{
					$width = max( $width, $profile->width );
				}

				// Javascript to get the screen sizes
				wp_enqueue_script( 'codepeople_theme_switch_script', plugins_url( '', __FILE__ ).'/js/public.js', array( 'jquery' ) );
				wp_localize_script( 'codepeople_theme_switch_script',
									'codepeople_theme_switch',
									array(
										'message' => ( ( !empty( $cpts_options[ 'loading_text' ] ) ) ? $cpts_options[ 'loading_text' ] : __( 'Do you want load an optimized version of website for your screen?', 'codepeople-theme-switch-text' ) ),
										'width' => $width,
										'url' => rtrim( get_site_url( get_current_blog_id() ), '/' ).'/',
										'decision_taken' => ( isset( $_SESSION[ 'theme_switch_accepted' ] ) )? true : false
									)
								);
			}
		}
	}
}

/**
 * Tell Wordpress to switch the theme based in the device where the webpage is loaded.
 */
if(!function_exists("codepeople_mobile_switch_theme_by_device")){
	function codepeople_mobile_switch_theme_by_device(){

		if( session_id() == '' ) session_start();

		if( get_transient( 'codepeople_theme_switch_registered' ) == false )
		{
			$theme = wp_get_theme();
			set_transient( 'codepeople_theme_switch_registered', $theme->stylesheet, 0 );
		}

		if(
			codepeople_theme_switch_valid_page() &&
			empty( $_SESSION[ 'theme_switch_denied' ] ) &&
			!empty( $_SESSION[ 'theme_switch_width' ] ) &&
			!empty( $_SESSION[ 'theme_switch_accepted' ] )

		)
		{
			$cpts_options = get_option( 'cpts_options' );
			$profiles = $cpts_options[ 'profiles' ];
			$width = $_SESSION[ 'theme_switch_width' ];
			$tmp_width = PHP_INT_MAX;

			foreach( $profiles as $profile )
			{
				if( $profile->width <= $tmp_width  && $width <= $profile->width )
				{
					$tmp_width = $profile->width;
					$theme_obj = wp_get_theme( $profile->theme );
					if( !is_wp_error( $theme_obj ) )
					{
						$theme = $theme_obj->template;
						$switch_stylesheet = $theme_obj->stylesheet;
					}
				}
			}
		}

		if(
			!empty( $_REQUEST[ 'theme_switch_preview' ] ) &&
			!empty( $_REQUEST[ 'theme_switch_stylesheet' ] ) &&
			current_user_can( 'manage_options' )
		)
		{
			$switch_stylesheet = trim($_REQUEST[ 'theme_switch_stylesheet' ]);
			$_SESSION[ 'theme_switch_accepted' ] = 1;
		}

		if( !empty( $switch_stylesheet ) )
		{
			switch_theme( $switch_stylesheet );
		}
		elseif( ($registered_theme = get_transient( 'codepeople_theme_switch_registered' )) != false )
		{
			switch_theme( $registered_theme );
		}
	}
} // codepeople_mobile_switch_theme_by_device

?>