<?php
/*
Plugin Name: KD Coming Soon
Plugin URI: http://kallidan.is-best.net
Description: Coming Soon plugin. Rresponsive and fluit landing page for Wordpress web sites.
Version: 1.7
Author: Kalli Dan.
Author URI: http://kallidan.is-best.net
License: GPL2
*/
/*
	KD Coming Soon

	Copyright (c) 2015-2017 Kalli Dan. (email : kallidan@yahoo.com)

	KD Coming Soon is free software: you can redistribute it but NOT modify it
	under the terms of the GNU Lesser Public License as published by the Free Software Foundation,
	either version 3 of the LGPL License, or any later version.

	KD Coming Soon is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Lesser Public License for more details.

	You should have received a copy of the GNU Lesser Public License along with KD Coming Soon.
	If not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( "KD_CS_VERSION", 1.7 );
define( "KD_CS_OPTION_VER", "kd_cs_version" );

class CS30111Redirects {
	function redirect() {
		global $wp;
		global $wpdb;

		$site_title  = get_option('blogname');
		$userrequest = str_ireplace(get_option('home'), '', $this->get_address());
		$csPrew   	 = (empty($_REQUEST['csPrew'])) ? "" : $_REQUEST['csPrew'];

		$kd_settings=get_option('kd_cs_settings');
		$cs_active	  = $kd_settings['cs_active'];
		$cs_theme	  = $kd_settings['cs_theme'];
		$cs_sl_durat  = $kd_settings['cs_sl_durat'];
		$cs_fd_durat  = $kd_settings['cs_fd_durat'];
		$cs_color	  = $kd_settings['cs_color'];
		$cs_date	  	  = $kd_settings['cs_date'];
		$cs_name		  = $kd_settings['cs_name'];
		$cs_nameinfo  = $kd_settings['cs_nameinfo'];
		$cs_email	  = $kd_settings['cs_email'];
		$cs_title	  = str_replace('\\', "", $kd_settings['cs_title']);
		$cs_subtitle  = str_replace('\\', "", $kd_settings['cs_subtitle']);
		$cs_emailbtn  = str_replace('\\', "", $kd_settings['cs_emailbtn']); 
		$cs_placetitle= str_replace('\\', "", $kd_settings['cs_placetitle']);
		$cs_emailsucc = str_replace('\\', "", $kd_settings['cs_emailsucc']);
		$cs_emailerr  = str_replace('\\', "", $kd_settings['cs_emailerr']);
		$cs_emailfail = str_replace('\\', "", $kd_settings['cs_emailfail']);
		$cs_ftitle	  = str_replace('\\', "", $kd_settings['cs_ftitle']);
		$cs_fsubtitle = str_replace('\\', "", $kd_settings['cs_fsubtitle']);
		$cs_note		  = str_replace('\\', "", $kd_settings['cs_note']);

		$destination = plugins_url('/assets/templates/' . $cs_theme . '.html',__FILE__);

		/* don't allow people to accidentally lock themselves out of admin and/or display preview of site */ 
		if($userrequest == '/wp-login.php' || is_admin() || ($cs_active != 'on' && !$csPrew)){
			$storedrequest = "";
			$pattern = '/^' . str_replace( '/', '\/', rtrim( $storedrequest, '/' ) ) . '/';
			$destination = str_replace('*','$1',$destination);
			$output = preg_replace($pattern, $destination, $userrequest);
			if ($output !== $userrequest) {
				// pattern matched, perform redirect
				if(is_admin()){
					$do_redirect = $userrequest;
				}else{
					$do_redirect = $userrequest;
				}
			}
		}else{
			//check if date has expired...
			$day_exp = getCSdays($cs_date);

			if($day_exp < 0){
				// replace default Wordpress web pages with our template...
				$bgs = '';
				$slides = get_option('kd_slides_ids');
				$sids = explode('~', $slides);
				if(isset($sids) && $slides !=""){
					foreach($sids as $slide_id){
						$query = $wpdb->prepare( "SELECT post_title, guid FROM ".$wpdb->prefix."posts WHERE ID = %d", $slide_id);
						$row = $wpdb->get_results( $query, ARRAY_A );
						if($row[0]['guid'] !=""){
							if($bgs != ""){ $bgs.= ','; }
							$bgs.= '"'.$row[0]['guid'].'"';
						}
					}
					if($bgs){
						$bgs = "jQuery('.coming-soon').backstretch([".$bgs."], {duration:".$cs_sl_durat.", fade:".$cs_fd_durat."});\n";
					}
				}

				$data = array(
					'cs_name' => $cs_name,
					'cs_email' => $cs_email,
					'cs_emailsucc' => $cs_emailsucc,
					'cs_emailerr' => $cs_emailerr,
					'cs_emailfail' => $cs_emailfail,
					'cs_db' => array(
						'dbuser' => $wpdb->dbuser,
						'dbpassword' => $wpdb->dbpassword,
						'dbname' => $wpdb->dbname,
						'dbhost' => $wpdb->dbhost,
						'dbprefix' => $wpdb->prefix
					)
				);
				$e_data = base64_encode(serialize($data));

				$kdtoret = file_get_contents($destination);
				$kdtoret = str_replace('%SCRIPT_URL%', includes_url('/js/jquery/jquery.js'), $kdtoret);
				$kdtoret = str_replace('%SITE_URL%', plugins_url('',__FILE__), $kdtoret);
				$kdtoret = str_replace('%PLUGIN_URL%', plugins_url('/assets',__FILE__), $kdtoret);

				$nonce = wp_create_nonce("kd_cemailer_nonce");
				$kdtoret = str_replace('%ACTION_URL%', admin_url('admin-ajax.php?action=kd_cemailer&nonce='.$nonce), $kdtoret);

				$kdtoret = str_replace('%CS_BKGRND%', $bgs, $kdtoret);
				$kdtoret = str_replace('%CS_COLOR%', $cs_color, $kdtoret);
				$kdtoret = str_replace('%CS_DATE%', $cs_date, $kdtoret);

				$kdtoret = str_replace('%CS_aTITLE%', $e_data, $kdtoret);
				$kdtoret = str_replace('%SITE_TITLE%', $site_title, $kdtoret);
				$kdtoret = str_replace('%CS_NAME%', $cs_name, $kdtoret);
				$kdtoret = str_replace('%CS_SUBNAME%', '<p>'.$cs_nameinfo.'</p>', $kdtoret);

				$kdtoret = str_replace('%CS_EMAIL%', $cs_email, $kdtoret);
				$kdtoret = str_replace('%CS_EMAILBTN%', $cs_emailbtn, $kdtoret);
				$kdtoret = str_replace('%CS_EMAILPLACE%', $cs_placetitle, $kdtoret);
				$kdtoret = str_replace('%CS_eERROR%', $cs_emailerr, $kdtoret);

				$kdtoret = str_replace('%CS_TITLE%', $cs_title, $kdtoret);
				$kdtoret = str_replace('%CS_SUBTITLE%', $cs_subtitle, $kdtoret);
				$kdtoret = str_replace('%CS_FTITLE%', $cs_ftitle, $kdtoret);
				$kdtoret = str_replace('%CS_FSUBTITLE%', $cs_fsubtitle, $kdtoret);
				if($cs_note){
					$cs_note = '<span style="color:#e75967;">*</span> <span style="font-style:italic;font-size:11px;">'.$cs_note.'</span>';
				}
				$kdtoret = str_replace('%CS_NOTE%', $cs_note, $kdtoret);

				$smedia = "";
				$cs_media = get_option('kd_cs_media');
				foreach($cs_media as $key => $media){
					foreach($media as $name => $murl){
						if($murl && $murl !=""){
							$name=stripslashes($name);
							$smedia.= '<a href="'.$murl.'" data-toggle="tooltip" data-placement="top" title="'.ucFirst($name).'" target="_new"><i class="fa fa-'.$name.'"></i></a>'."\n";
						}
					}
				}
				$kdtoret = str_replace('%CS_SOCIALMEDIA%', $smedia, $kdtoret);

				echo $kdtoret;
				exit;
			}else{
				//date has expired, let's switch the coming-soon page off...
				$kd_settings['cs_active'] = "";
				update_option('kd_cs_settings', $kd_settings);
			}
		}

		//if wp-admin, display it...
		if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {
			// check if destination needs the domain prepended
			if (strpos($do_redirect, '/') === 0){
				//$do_redirect = home_url().$do_redirect;
			}
			header ('HTTP/1.1 301 Moved Permanently');
			header ('Location: ' . $do_redirect);
			exit();
		}else {
			unset($kd_settings);
		}
	}

	function get_address() {
		// return the full address
		return $this->get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	function get_protocol() {
		// Set the base protocol to http
		$protocol = 'http';
		// check for https
		if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    		$protocol .= "s";
		}	
		return $protocol;
	}
}

//check if date has expired...
function getCSdays($dagur){
	$now = time();
	$your_date = strtotime($dagur);
	$datediff = ( $now - $your_date);
	return round($datediff/(60*60*24));
}

/*-- SETUP -- */

// Add custom style for admin...
function kd_csadmin_style() {
	wp_enqueue_style( 'thickbox' );
	if(isset($_REQUEST['page'])){
		$page = $_REQUEST['page'];
		if($page=="_cs_menu_op" || $page=="_cs_menu_opa" || $page=="_cs_menu_opb" || $page=="_cs_menu_opc" || $page=="_cs_menu_opd"){
			wp_enqueue_style( 'kd_bootstrap_style', plugins_url( '/assets/bootstrap/css/bootstrap.min.css', __FILE__ ), array(),'3.2.2','all');
			wp_enqueue_style( 'kd_cscalendar_style', plugins_url( '/assets/css/jquery-datepicker.min.css', __FILE__ ), array(),'1.8.21','all');
			wp_enqueue_style( 'kd_csprettyphoto_style', plugins_url( '/assets/css/prettyPhoto.min.css', __FILE__ ), array(),'3.1.5','all');
			wp_enqueue_style( 'kd_csswitch_style', plugins_url( '/assets/css/bootstrap-switch.min.css', __FILE__ ), array(),'1.8','all');
			wp_enqueue_style( 'kd_csspinner_style', plugins_url( '/assets/css/jquery.ui.spinner.min.css', __FILE__ ), array(),'1.10.4','all');
			wp_enqueue_style( 'kd_cscolorpicker_style', plugins_url( '/assets/css/colorpicker.min.css', __FILE__ ), array(),'2.0','all');
			wp_enqueue_style( 'kd_csadmin_style', plugins_url( '/assets/css/cs_admin.css', __FILE__ ), array(),'1.0','all');
		}
	}
}
add_action( 'admin_print_styles', 'kd_csadmin_style' );

// Add custom scripts...
function kd_cs_init_method() {
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'thickbox' );
	if(is_admin()){
		if(isset($_REQUEST['page'])){
			$page = $_REQUEST['page'];
			if($page=="_cs_menu_op" || $page=="_cs_menu_opa" || $page=="_cs_menu_opb" || $page=="_cs_menu_opc" || $page=="_cs_menu_opd"){
				wp_enqueue_script( 'jquery-ui-sortable');
				wp_enqueue_script( 'jquery-ui-datepicker');
				wp_enqueue_script( 'kd_coming_soon_bo', plugins_url( '/assets/bootstrap/js/bootstrap.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_pp', plugins_url( '/assets/js/jquery.prettyPhoto.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_switch', plugins_url( '/assets/js/bootstrap-switch.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_spinner', plugins_url( '/assets/js/jquery.ui.spinner.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_color', plugins_url( '/assets/js/colorpicker.min.js', __FILE__ ));
				wp_enqueue_script( 'kd_coming_soon_adm', plugins_url( '/assets/js/cs_admin.js', __FILE__ ));
			}
		}
	}
}
add_action( 'wp_print_scripts', 'kd_cs_init_method' );

// Media Library upload engine...
function kd_wp_media_files(){
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'kd_wp_media_files' );

// Handle form submition
function kd_cemailer() {
	$subscriber_email = addslashes(trim($_REQUEST['email']));
	$emailData = unserialize(base64_decode($_REQUEST['cetitle']));
	$emailSiteName = $emailData['cs_name'] . ' - website';
	$emailToName   = $emailData['cs_name'];
	
	$array = array('valid' => 0, 'message' => "");

	if ( !wp_verify_nonce( $_REQUEST['nonce'], "kd_cemailer_nonce")) {
		$array['valid'] = "error";
		$array['message'] =  $emailData['cs_emailfail'];
	}elseif(!isEmail($subscriber_email)) {
		$array['valid'] = "error";
		$array['message'] =  '11 '.$emailData['cs_emailerr'];
	}else{
		if($emailData['cs_email'] && isEmail($emailData['cs_email'])){
			$subject = 'New Subscriber!';
			$body = "You have a new subscriber at ".$emailData['cs_name']."!\n\nEmail: " . $subscriber_email;
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: '.$emailSiteName.' <'.$subscriber_email.'>'
			);

			$status = wp_mail( $emailData['cs_email'], $subject, $body, $headers );
			if(!$status) {
				$array['valid'] = "error";
				$array['message'] = $emailData['cs_emailfail'];
			}else{
				$array['valid'] = 'success';
				$array['message'] = $emailData['cs_emailsucc'];

				$res = storeSubscription($subscriber_email, $emailData['cs_db']);
			}
		}else{
			$array['valid'] = 'success';
			$array['message'] = $emailData['cs_emailsucc'];

			$res = storeSubscription($subscriber_email, $emailData['cs_db']);
		}
   }

	echo json_encode($array);
   die();
}
add_action("wp_ajax_kd_cemailer", "kd_cemailer");
add_action("wp_ajax_nopriv_kd_cemailer", "kd_cemailer");

// Admin main setup
function kd_cs_install() {
	global $wpdb;

	$slider_ids = array();
	$sliders = array(
		0 => '1,"Slide1","inherit","slide1","'.plugins_url( '/assets/templates/slider0001.jpg', __FILE__ ).'","attachment","image/jpeg"',
		1 => '1,"Slide2","inherit","slide2","'.plugins_url( '/assets/templates/slider0002.jpg', __FILE__ ).'","attachment","image/jpeg"',
		2 => '1,"Slide3","inherit","slide3","'.plugins_url( '/assets/templates/slider0003.jpg', __FILE__ ).'","attachment","image/jpeg"'
	);

	$kd_media = array(
		0 => array('facebook'	=> ""),
		1 => array('twitter'		=> ""),
		2 => array('linkedin'	=> ""),
		3 => array('google-plus'=> ""),
		4 => array('flickr'		=> ""),
		5 => array('foursquare'	=> ""),
		6 => array('tumblr'		=> ""),
		7 => array('dribbble'	=> ""),
		8 => array('skype'		=> ""),
		9 => array('youtube'		=> "")
	);

	$nextMonth = time() + (30 * 24 * 60 * 60);
	$kd_settings['cs_date']		  = date('m/d/Y', $nextMonth);
	$kd_settings['cs_active']	  = "";
	$kd_settings['cs_color']	  = "#e75967";
	$kd_settings['cs_name']		  = get_option('blogname');
	$kd_settings['cs_email']	  = get_option('admin_email');
	$kd_settings['cs_theme']	  = "cs-templ_01";
	$kd_settings['cs_sl_durat']  = 5000;
	$kd_settings['cs_fd_durat']  = 750;
	$kd_settings['cs_nameinfo']  = "Tel: <span>your_phone_number</span> | Skype: <span>my_skype_id</span>";
	$kd_settings['cs_title']	  = "We're Coming Soon";
	$kd_settings['cs_subtitle']  = "We are working very hard on the new version of our site.<br>It will bring a lot of new features. Stay tuned!";
	$kd_settings['cs_emailbtn']  = "Subscribe";
	$kd_settings['cs_placetitle']= "Your email address...";
	$kd_settings['cs_emailsucc'] = "Thanks for your subscription!";
	$kd_settings['cs_emailerr']  = "Please enter a valid email address.";
	$kd_settings['cs_emailfail'] = "Sorry we could not send your subscription!<br>Please try again...";
	$kd_settings['cs_ftitle']	  = "Subscribe to our newsletter";
	$kd_settings['cs_fsubtitle'] = "Sign up now to our newsletter and you'll be one of the first to know when the site is ready:";
	$kd_settings['cs_note']		  = "We won't use your email for spam, just to notify you of our launch.";

	//insert default slider images...
	$fields = 'post_author, post_title, post_status, post_name, guid, post_type, post_mime_type';
	foreach($sliders as $key => $value){
		$result = $wpdb->query("INSERT INTO ".$wpdb->prefix."posts (".$fields.") values (".$value.")");
		$err = $wpdb->error;
		if($result && !$err){
			$slider_ids[] = $wpdb->insert_id;
		}else{
			//echo "Insert Failed!<BR>\nERROR:".mysql_error()."<BR>\n";
		}
	}
	$kd_sliders = implode('~', $slider_ids);

	//insert settings...
	add_option("kd_cs_settings", $kd_settings);
	add_option('kd_slides_ids', $kd_sliders);
	add_option('kd_cs_media', $kd_media);
	add_option('kd_cs_emails', "");
	add_option( KD_CS_OPTION_VER, KD_CS_VERSION, '', 'no');
}
register_activation_hook(__FILE__,'kd_cs_install');

function kd_comingsoon_upgrade() {
	// Check db version and update it if required...
	if ( get_option( KD_CS_OPTION_VER, NULL ) != KD_CS_VERSION) {
		kd_cs_install();
	}
}
add_action('plugins_loaded', 'kd_comingsoon_upgrade');

function kd_cs_uninstall() {
	global $wpdb;

	delete_option( "kd_cs_settings" );
	delete_option( "kd_slides_ids" );
	delete_option( "kd_cs_media" );
	delete_option( "kd_cs_emails" );
	delete_option( KD_CS_OPTION_VER );
}
register_uninstall_hook( __FILE__, 'kd_cs_uninstall' );

$redirect_plugin = new CS30111Redirects();
if(isset($redirect_plugin)) {
	// add the redirect action, high priority
	add_action('init', array($redirect_plugin,'redirect'), 1);

	// add Ajax action for form submition
   wp_register_script( "my_voter_script", WP_PLUGIN_URL.'/kd-coming-soon/assets/js/scripts.js', array('jquery') );
   wp_localize_script( 'my_voter_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( 'my_voter_script' );
}

/*-- MENUS --*/

function kd_cs_add_menu() {
	$file = dirname( __FILE__ ) . '/kd_coming_soon.php';
	$icon = plugins_url('/assets/img/icl.png',__FILE__);
	//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	$main_page 		= add_menu_page('KD Coming Soon', 'KD Coming Soon', 'administrator', '_cs_menu_op', 'kd_cs_menu_op', $icon);
	//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
	$settings_page = add_submenu_page( '_cs_menu_op', 'Settings', 'Settings', 'administrator', '_cs_menu_opa', 'kd_cs_menu_op');
	$slide_page 	= add_submenu_page( '_cs_menu_op', 'Add slides', 'Slides', 'administrator', '_cs_menu_opb', 'kd_cs_menu_op');
	$social_page 	= add_submenu_page( '_cs_menu_op', 'Social Media', 'Social Media', 'administrator', '_cs_menu_opc', 'kd_cs_menu_op');
	$subscr_page 	= add_submenu_page( '_cs_menu_op', 'Subscriptions', 'Subscriptions', 'administrator', '_cs_menu_opd', 'kd_cs_menu_op');

	if ( class_exists( "WP_Screen" ) ) {
		add_action( 'load-' . $main_page, 'kd_cs_help' );
		add_action( 'load-' . $settings_page, 'kd_cs_help' );
		add_action( 'load-' . $slide_page, 'kd_cs_help' );
		add_action( 'load-' . $social_page, 'kd_cs_help' );
		add_action( 'load-' . $subscr_page, 'kd_cs_help' );
	} else if ( function_exists( "add_contextual_help" ) ) {
		add_contextual_help( $main_page, kd_cs_get_setting_help_text() );
		add_contextual_help( $settings_page, kd_cs_get_setting_help_text() );
		add_contextual_help( $slide_page, kd_cs_get_slides_help_text() );
		add_contextual_help( $social_page, kd_cs_get_social_help_text() );
		add_contextual_help( $subscr_page, kd_cs_get_subscriber_help_text() );
	}
}
add_action('admin_menu', 'kd_cs_add_menu');

// Add link to plugin page
function kd_cs_add_menu_setting($links) { 
  $settings_link = '<a href="admin.php?page=_cs_menu_op">Setup</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
add_filter("plugin_action_links_". plugin_basename(__FILE__), 'kd_cs_add_menu_setting' );

// Init the help menu functionality
function kd_cs_help() {
	$screen = get_current_screen();
	$screen->add_help_tab( array ( 'id' => 'kd_cs-setting-help',
	                               'title' => __( 'Settings', 'kd_cs-help' ),
	                               'content' => kd_cs_get_setting_help_text() ));
	$screen->add_help_tab( array ( 'id' => 'kd_cs-slides-help',
	                               'title' => __( 'Slider Content', 'kd_cs-help' ),
	                               'content' => kd_cs_get_slides_help_text() ));
	$screen->add_help_tab( array ( 'id' => 'kd_cs-social-help',
	                               'title' => __( 'Social Media', 'kd_cs-help' ),
	                               'content' => kd_cs_get_social_help_text() ));
	$screen->add_help_tab( array ( 'id' => 'kd_cs-subscribers-help',
	                               'title' => __( 'Subscribers', 'kd_cs-help' ),
	                               'content' => kd_cs_get_subscriber_help_text() ));
}
function kd_cs_get_setting_help_text() {
	$help_text = '<p><strong>' . __( 'Settings:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'Select the date <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> should be active and fill in all required fields.<br/>
						When done, hit the <span style="background-color:#2ea2cc;color:#fff;padding:2px 6px;">save settings</span> button.
						<p>
						<table>
							<tr><td colspan="2">Fields description:</td></tr>
							<tr>
								<td style="width:200px;vertical-align:top;font-weight:bold;">Date Active</td>
								<td style="vertical-align:top;">-</td>
								<td>This is the date the <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> page will be shown.<br>
								When this date is reached your orginal Wordpress site will be displayed and <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> automatically turned off.</td>
							</tr>
							<tr>
								<td style="vertical-align:top;font-weight:bold;">Email Address</td>
								<td style="vertical-align:top;">-</td>
								<td>This is the email address to which <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> will send new subscriptions.<br>
								If you don\'t want to get email notification for every new subscription leave this field empty.<br>
								The email address is encrypted and not shown or displayed anywhere on the site unless you explicitly enter it into some settings fields.</td>
							</tr>
						</table>
						</p>', 'kd_cs-help' ) . '</p>';
	return $help_text;
}
function kd_cs_get_slides_help_text() {
	$help_text = '<p><strong>' . __( 'Slider Content:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'To add a photo to the slider click the <span style="background-color:#5cb85c;color:#fff;padding:2px 6px;">add slides</span> button.
						The Media Manager will popup where you can upload and/or select photos to add.<br/>
						To view full version of a photo click any image.<br/>
						To remove photo hover the mouse over a image and click the <img src="'.plugins_url('/assets/img/delete.png',__FILE__).'" style="padding: 0px 0px 3px 0px;" align="absmiddle"> icon.<br/>
						When done, hit the <span style="background-color:#2ea2cc;color:#fff;padding:2px 6px;">save now</span> button.', 'kd_cs-help' ) . '</p>';
	return $help_text;
}
function kd_cs_get_social_help_text() {
	$help_text = '<p><strong>' . __( 'Social Media:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'Fill in the fields you want with the url to your social media portal.<br/>
						You can re-order the media links by click an social icon and drag it up or down to the location you want.<br/>
						When done, hit the <span style="background-color:#2ea2cc;color:#fff;padding:2px 6px;">save now</span> button.', 'kd_cs-help' ) . '</p>';
	return $help_text;
}
function kd_cs_get_subscriber_help_text() {
	$help_text = '<p><strong>' . __( 'Subscribers:', 'kd_cs-help' ) . '</strong></p>';
	$help_text .= '<p>' . __( 'To export all subscribers to a comma seperated file (.scv) hit the <span style="background-color:#2ea2cc;color:#fff;padding:2px 6px;">export all</span> button.<br/>
						To remove subscription email(s) check the ceckbox for the email(s) in question and hit the <span style="background-color:#d9534f ;color:#fff;padding:2px 6px;">delete selected</span> button.', 'kd_cs-help' ) . '</p>';
	return $help_text;
}

/*-- ADMIN -- */
function kd_cs_menu_op() {
	global $wp;
	global $wpdb;

	$def_icl_w = 250;
	$def_icl_h = 133;

	add_thickbox();
	$upload_dir = wp_upload_dir();
	$slide_url  = $upload_dir['baseurl'];
	$slide_path = $upload_dir['basedir'];

	$page = '_cs_menu_op';
	if(isset($_REQUEST['page'])){
		$page = $_REQUEST['page'];
	}
	?>

	<script type='text/javascript'>
		def_icl_w = <?php echo $def_icl_w;?>;
		def_icl_h = <?php echo $def_icl_h;?>;
		jQuery(document).ready(function(){
			jQuery('#csTabs a[href="#<?php echo $page;?>"]').tab('show');
		});
	</script>

	<div id="my-content-id" style="display:none;">
		<p style="padding:40px 0 0 16px;"><img id="ticker_content" src="#"></p>
	</div>
	<a id="thickWin_view" href="#TB_inline?width=350&height=270&inlineId=my-content-id" class="thickbox"></a>

	<div id="cspreview">
		<iframe id="csPreviewContent" border="0" marginheight="0" frameborder="0" scrolling="no" seamless />
			<div style="text-align:center;padding-top:50;font-weight:bolder;font-size:18px;">Your browser does not support previews.</div>
		</iframe>
	</div>

	<div class="wrap">
		<h2 class="cstitle"><img src="<?php echo plugins_url('/assets/img/icon.png',__FILE__);?>" alt="" align="absmiddle">KD Coming Soon
		<span style="color:#444;font-size:18px"></span></h2>
	</div>

	<?php
	if( isset($_POST['kdupdcs']) || isset($_POST['kdsetsub']) || isset($_POST['kdupdcsslides']) || isset($_POST['kddelcs'])){
		$msg = "";
		if( isset($_POST['kdupdcs'])){
			$msg = 'Social links saved';
		}elseif( isset($_POST['kdsetsub'])){
			$msg = 'Settings saved';
		}elseif( isset($_POST['kdupdcsslides'])){
			$msg = 'Slides saved';
		}elseif( isset($_POST['kddelcs'])){
			$msg = 'Subscriptions deleted';
		}
		if($msg){?>
	<div class="updated success-message" style="padding: 20px;">
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<?php echo '<img src="'.plugins_url('/assets/img/dialog_info.gif',__FILE__).'" alt="" width="32" height="32" align="top">';?>
				</td>
				<td style="padding:0 0 0 20px;">
					<strong><?php echo $msg; ?></strong>
				</td>
			</tr>
		</table>
	</div>
	<script type='text/javascript'>
		jQuery(document).ready(function(){ timeIt('updated'); });
	</script>
		<?php }} ?>

	<div class="wrap">
		<div id="csTabs">
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation"><a href="#_cs_menu_op" class="" role="tab" data-toggle="tab">Welcome</a></li>
				<li role="presentation"><a href="#_cs_menu_opa" role="tab" data-toggle="tab">Settings</a></li>
				<li role="presentation"><a href="#_cs_menu_opb" role="tab" data-toggle="tab">Slider</a></li>
				<li role="presentation"><a href="#_cs_menu_opc" role="tab" data-toggle="tab">Social Media</a></li>
				<li role="presentation"><a href="#_cs_menu_opd" role="tab" data-toggle="tab">Subscriptions</a></li>
			</ul>

			<div class="tab-content">
<!-- WELCOME -->
				<div role="tabpanel" class="tab-pane active" id="_cs_menu_op">
					<h3>Welcome</h3>
					<h5></h5>
					<img src="<?php echo plugins_url('/assets/img/welcome.png',__FILE__);?>" alt="" style="width:100%;height:auto;">
					<div class="wrap" style="width:100%;text-align:center;margin:25px 0px;">
						For support and further information about <span style="color:#21759b;font-weight:bold;">KD Coming Soon</span> see the plugins homepage at <a target="_new" href="http://kallidan.is-best.net">http://kallidan.is-best.net</a>.
					</div>
				</div>
<!-- SETTINGS -->
				<div role="tabpanel" class="tab-pane" id="_cs_menu_opa">
					<h3>Settings</h3>
					<h5>Fill out all required fields.</h5>
					<?php 
					if(isset($_POST['kdsetsub'])){
						$kd_settings=get_option('kd_cs_settings');
						$kd_settings['cs_date']		  = $_POST['cs_date'];
						$kd_settings['cs_active']	  = $_POST['cs_active'];
						$kd_settings['cs_color'] 	  = $_POST['cs_color'];
						$kd_settings['cs_email']	  = $_POST['cs_email'];
						$kd_settings['cs_name']	  	  = str_replace("'", '\\\'', $_POST['cs_name']);
						$kd_settings['cs_nameinfo']  = str_replace("'", '\\\'', $_POST['cs_nameinfo']);
						$kd_settings['cs_title']	  = str_replace("'", '\\\'', $_POST['cs_title']);
						$kd_settings['cs_subtitle']  = str_replace("'", '\\\'', $_POST['cs_subtitle']);
						$kd_settings['cs_emailbtn']  = str_replace("'", '\\\'', $_POST['cs_emailbtn']);
						$kd_settings['cs_emailsucc'] = str_replace("'", '\\\'', $_POST['cs_emailsucc']);
						$kd_settings['cs_emailerr']  = str_replace("'", '\\\'', $_POST['cs_emailerr']);
						$kd_settings['cs_emailfail'] = str_replace("'", '\\\'', $_POST['cs_emailfail']);
						$kd_settings['cs_placetitle']= str_replace("'", '\\\'', $_POST['cs_placetitle']);
						$kd_settings['cs_ftitle']	  = str_replace("'", '\\\'', $_POST['cs_ftitle']);
						$kd_settings['cs_fsubtitle'] = str_replace("'", '\\\'', $_POST['cs_fsubtitle']);
						$kd_settings['cs_note'] 	  = str_replace("'", '\\\'', $_POST['cs_note']);
						update_option('kd_cs_settings', $kd_settings);
					}
					$kd_settings=get_option('kd_cs_settings');
					$cs_date	  	  = $kd_settings['cs_date'];
					$cs_active	  = $kd_settings['cs_active'];
					$cs_color 	  = $kd_settings['cs_color'];
					$cs_email	  = $kd_settings['cs_email'];
					$cs_name	  	  = str_replace('\\', "", $kd_settings['cs_name']);
					$cs_nameinfo  = str_replace('\\', "", $kd_settings['cs_nameinfo']);
					$cs_title	  = str_replace('\\', "", $kd_settings['cs_title']);
					$cs_subtitle  = str_replace('\\', "", $kd_settings['cs_subtitle']);
					$cs_emailbtn  = str_replace('\\', "", $kd_settings['cs_emailbtn']);
					$cs_placetitle= str_replace('\\', "", $kd_settings['cs_placetitle']);
					$cs_emailsucc = str_replace('\\', "", $kd_settings['cs_emailsucc']);
					$cs_emailerr  = str_replace('\\', "", $kd_settings['cs_emailerr']);
					$cs_emailfail = str_replace('\\', "", $kd_settings['cs_emailfail']);
					$cs_ftitle	  = str_replace('\\', "", $kd_settings['cs_ftitle']);
					$cs_fsubtitle = str_replace('\\', "", $kd_settings['cs_fsubtitle']);
					$cs_note		  = str_replace('\\', "", $kd_settings['cs_note']);
					if($cs_active){ $checked = ' checked'; }else{ $checked=""; }
					?>
					<form id="csSettings" name="csSettings" method="post" action="admin.php?page=_cs_menu_opa">
						<table class="csadmin">
							<tr>
								<td style="width:200px;">Coming Soon Active <span class="cserror cs-sw">*</span></td>
								<td>
									<div class="make-switch switch-mini" data-on="primary" data-off="default"  data-on-label="On" data-off-label="Off">
										<input type="checkbox" id="cs_active" name="cs_active"<?php echo $checked;?>>
									</div>
								 </td>
							</tr>
							<tr>
								<td>Date Active <span class="cserror cs-sw">*</span></td>
								<td>
									<input type="text" id="cs_date" name="cs_date" style="width:100px;cursor:pointer;" value="<?php echo $cs_date; ?>" class="datepicker" placeholder="mm/dd/yyyy" onFocus="clearFormError('cs_date');" readonly/>
									<img  class="calimg" src="<?php echo plugins_url('/assets/img/calendar.gif',__FILE__);?>" width="20" height="20" alt="" title="" align="absmiddle" />
									<span class="cshelp-inline"></span>
									<div id="error_cs_date" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Site Name <span class="cserror cs-sw">*</span></td>
								<td>
									<input type='text' id="cs_name" name="cs_name" style="width:250px;" value="<?php echo $cs_name; ?>" placeholder="Your site name" onFocus="clearFormError('cs_name');" />
									<div id="error_cs_name" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Email Address</td>
								<td>
									<input type='text' id="cs_email" name="cs_email" style="width:250px;" value="<?php echo $cs_email; ?>" placeholder="Your email address" onFocus="clearFormError('cs_email');" />
									<span class="cshelp-inline"></span>
									<div id="error_cs_email" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Theme Color <span class="cserror cs-sw">*</span></td> 
								<td>
									<input type='text' id="cs_color" name="cs_color" style="width:100px;cursor:pointer;" value="<?php echo $cs_color; ?>" placeholder="#" onFocus="clearFormError('cs_color');" readonly/>
									<div id="colorpickerHolder" style="display:inline-block;width:20px;height:20px;background-color:<?php echo $cs_color; ?>;margin:-5px 0;"></div>
									<div id="error_cs_color" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Header Info</td>
								<td>
									<input type='text' id="cs_nameinfo" name="cs_nameinfo" style="width:100%;" value="<?php echo $cs_nameinfo; ?>" placeholder="Your phone number" onFocus="clearFormError('cs_nameinfo');" />
									<div id="error_cs_nameinfo" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Title</td>
								<td>
									<input type='text' id="cs_title" name="cs_title" style="width:100%;" value="<?php echo $cs_title; ?>" placeholder="" onFocus="clearFormError('cs_title');" />
									<div id="error_cs_title" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Sub Title</td>
								<td>
									<input type='text' id="cs_subtitle" name="cs_subtitle" style="width:100%;" value="<?php echo $cs_subtitle; ?>" placeholder="" onFocus="clearFormError('cs_subtitle');" />
									<div id="error_cs_subtitle" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Subscr Button Title <span class="cserror cs-sw">*</span></td>
								<td>
									<input type='text' id="cs_emailbtn" name="cs_emailbtn" style="width:100%;" value="<?php echo $cs_emailbtn; ?>" placeholder="" onFocus="clearFormError('cs_emailbtn');" />
									<div id="error_cs_emailbtn" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Email Placeholder Title</td>
								<td>
									<input type='text' id="cs_placetitle" name="cs_placetitle" style="width:100%;" value="<?php echo $cs_placetitle; ?>" placeholder="" onFocus="clearFormError('cs_placetitle');" />
									<div id="error_cs_placetitle" class="cserror"></div>
								</td>
							</tr>
							
							<tr>
								<td>Email Succsess Message <span class="cserror cs-sw">*</span></td>
								<td>
									<input type='text' id="cs_emailsucc" name="cs_emailsucc" style="width:100%;" value="<?php echo $cs_emailsucc; ?>" placeholder="" onFocus="clearFormError('cs_emailsucc');" />
									<div id="error_cs_emailsucc" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Email Error Message <span class="cserror cs-sw">*</span></td> 
								<td>
									<input type='text' id="cs_emailerr" name="cs_emailerr" style="width:100%;" value="<?php echo $cs_emailerr; ?>" placeholder="" onFocus="clearFormError('cs_emailerr');" />
									<div id="error_cs_emailerr" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Email Failur Message <span class="cserror cs-sw">*</span></td> 
								<td>
									<input type='text' id="cs_emailfail" name="cs_emailfail" style="width:100%;" value="<?php echo $cs_emailfail; ?>" placeholder="" onFocus="clearFormError('cs_emailfail');" />
									<div id="error_cs_emailfail" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Footer Title</td>
								<td>
									<input type='text' id="cs_ftitle" name="cs_ftitle" style="width:100%;" value="<?php echo $cs_ftitle; ?>" placeholder="" onFocus="clearFormError('cs_ftitle');" />
									<div id="error_cs_ftitle" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Footer Sub-title</td>
								<td>
									<input type='text' id="cs_fsubtitle" name="cs_fsubtitle" style="width:100%;" value="<?php echo $cs_fsubtitle; ?>" placeholder="" onFocus="clearFormError('cs_fsubtitle');" />
									<div id="error_cs_fsubtitle" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Footer Notification</td>
								<td>
									<input type='text' id="cs_note" name="cs_note" style="width:100%;" value="<?php echo $cs_note; ?>" placeholder="" onFocus="clearFormError('cs_note');" />
									<div id="error_cs_note" class="cserror"></div>
								</td>
							</tr>
						</table>
						<div style="float:left;padding:20px 0px 0px 0px;">
							<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
						</div>
						<div style="text-align:right;padding:20px 0px 0px 0px;">
							<input type="button" class="button-secondary" onClick="javascript:this.form.reset();" value="reset" /> &nbsp;
							<input type="submit" name="kdsetsub" class="button-primary" onClick="return submitCSsettings(this.form);" value="save settings" />
						</div>
					</form>
				</div>
<!-- SLIDER -->
				<div role="tabpanel" class="tab-pane" id="_cs_menu_opb">
					<h3>Slider Settings</h3>
					<h5>Fill out all required fields.</h5>
					<?php
					if(isset($_POST['kdupdcsslides'])){
						$kd_settings=get_option('kd_cs_settings');
						$kd_settings['cs_sl_durat'] = $_POST['cs_sl_durat'];
						$kd_settings['cs_fd_durat'] = $_POST['cs_fd_durat'];
						update_option('kd_cs_settings', $kd_settings);
						update_option('kd_slides_ids', $_POST['cs_slides_ids']);
					}

					$kd_settings=get_option('kd_cs_settings');
					$cs_sl_durat = $kd_settings['cs_sl_durat'];
					$cs_fd_durat = $kd_settings['cs_fd_durat'];

					$slide=get_option('kd_slides_ids');
					$ids = explode('~', $slide);
					$SLIDES = array();
					if($ids){
						foreach($ids as $slide_id){
							$query = $wpdb->prepare( "SELECT post_title, guid FROM ".$wpdb->prefix."posts WHERE ID = %d", $slide_id);
							$row = $wpdb->get_results( $query, ARRAY_A );
							if($row[0]['guid'] && $row[0]['guid'] !=""){
								$SLIDES[$slide_id] = $row[0]['guid'];
							}
						}
					}
					?>
					<form id="csSlides" name="csSlides" class="csadmin" method="post" action="admin.php?page=_cs_menu_opb">
						<input type="hidden" id="cs_slides_ids" name="cs_slides_ids" value="<?php echo $slide;?>">
						<table class="csadmin" style="margin-bottom:20px;">
							<tr>
								<td style="width:160px;">Slider duration <span class="cserror cs-sw">*</span></td>
								<td>
									<input type='text' id="cs_sl_durat" name="cs_sl_durat" style="width:100px;margin-top: -3px;" value="<?php echo $cs_sl_durat; ?>" placeholder="5000" onFocus="clearFormError('cs_sl_durat');" readonly/>
									<div id="error_cs_sl_durat" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td>Slider fade duration <span class="cserror cs-sw">*</span></td>
								<td>
									<input type='text' id="cs_fd_durat" name="cs_fd_durat" style="width:100px;margin-top: -3px;" value="<?php echo $cs_fd_durat; ?>" placeholder="750" onFocus="clearFormError('cs_fd_durat');" readonly/>
									<div id="error_cs_fd_durat" class="cserror"></div>
								</td>
							</tr>
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #ccc;margin-bottom:20px;">
									&nbsp;
								</td>
							</tr>
						</table>
						<h3>Slider Photos</h3>
						<h5>Click photo to view full size.</h5>
						<div id="cssrc">
							<?php
							if(count($SLIDES) >0){
								foreach($SLIDES as $id => $src){
									if(!$src || $src ==""){ continue; }
									$w = $def_icl_w;
									$h = $def_icl_h;
									$pos = strrpos($src, '/');
									$img = substr($src, $pos);     
									if ($pos !== false) {
										if($img=='/slider0001.jpg' || $img=='/slider0002.jpg' || $img=='/slider0003.jpg'){
											$img = plugins_url( '/assets/templates'.$img, __FILE__ );
										}else{
											$img = $slide_path.$img;
										}
										//calculate image size...
										$size = getimagesize($img);
										if($size[0] > $def_icl_w){
											$w = $def_icl_w.'px';
											$h = 'auto';
										}
										if($size[1] > $def_icl_h){
											$h = $def_icl_h.'px';
											$w = 'auto';
										}

										echo '
								<a id="sl_'.$id.'" class="outer" href="'.$src.'" rel="prettyPhoto[cs_gallery0015]">
									<img src="'.$src.'" alt="" title="" align="absmiddle" style="width:'.$w.';height:'.$h.';margin:3px;border:1px solid #ccc;" />
								</a>';
									}
								}
							}else{
								echo 'Click the <strong>add slides</strong> button to select photos...';
							}
							?>
						</div>
						<div style="float:left;padding:20px 0px 0px 0px;">
							<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
						</div>
						<div style="text-align:right;padding:20px 0px 0px 0px;">
							<a href="#" id="gallery_man" name="gallery_man" class="button-success" onClick="return false;">add slides</a> &nbsp;
							<input type="submit" name="kdupdcsslides" class="button-primary" value="save now" />
						</div>
					</form>
					<div id="dl_0" class="box1">
						<img  src="<?php echo plugins_url('/assets/img/delete.png',__FILE__);?>" alt="Remove" title="" />
					</div>
				</div>
<!-- SOCIAL MEDIA -->
				<div role="tabpanel" class="tab-pane" id="_cs_menu_opc">
					<h3>Sosical Media</h3>
					<h5>Drag media up / down to reorder.</h5>
					<?php
					if(isset($_POST['kdupdcs'])){
						update_option('kd_cs_media', $_POST['csslidecont']);
					}

					$cs_media=get_option('kd_cs_media');
					?>
					<form class="csadmin" id="qord" name="qord" method="post" action="admin.php?page=_cs_menu_opc">
						<div class="smedcont" style="width:70%;">
							<ul id="kdcsslideopt">
								<?php
								foreach($cs_media as $key => $media){
									foreach($media as $name => $murl){
										$name=stripslashes($name);
										$simg = plugins_url('/assets/img/media/'.$name.'.png',__FILE__);
										?>
										<li>
											<img src="<?php echo $simg;?>" width="32" height="32" alt="<?php echo ucfirst($name);?>" title="<?php echo ucfirst($name);?>" align="absmiddle" style="margin:3px;">
											<input type="text" id="<?php echo $name;?>" name="csslidecont[][<?php echo $name;?>]" value="<?php echo $murl;?>" style="width:70%;" placeholder="https://" onFocus="clearFormError('<?php echo $name;?>');" />
											<div id="error_<?php echo $name;?>" class="cserror"></div>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>
						<div style="float:left;padding:20px 0px 0px 0px;">
							<a href="#cspreview" onClick="javascript:previewSite('<?php echo stripslashes(get_site_url());?>','');" class="button-primary btn-success prettyprewphoto">preview</a>
						</div>
						<div style="text-align:right;padding:20px 0px 0px 0px;">
							<input type="button" class="button-secondary" onClick="javascript:this.form.reset();" value="reset" /> &nbsp;
							<input type="submit" name="kdupdcs" class="button-primary" onClick="return submitCSsocial(this.form);" value="save now" />
						</div>
					</form>
				</div>
<!-- Subscriptions -->
				<div role="tabpanel" class="tab-pane" id="_cs_menu_opd">
					<h3>Subscriptions</h3>
					<h5>Click the <b>export all</b> button to save all subscriptions as comma seperated (.csv) file on your hard disk.<br></h5>
					<?php
					if(isset($_POST['kddelcs'])){
						$cs_subscriptions = get_option('kd_cs_emails');
						foreach($_POST as $name => $value){
							$btn = explode('_', $name);
							$subscriptions = explode('~', $cs_subscriptions);
							if($btn[0]=='csdel' && $value !=""){
								$new_cs_subscr = array();
								foreach($subscriptions as $sc_emails){
									$old_btn = explode('|', $sc_emails);
									if($old_btn[1] != $value){
										$new_cs_subscr[] = $sc_emails;
									}
								}
								$cs_subscriptions = implode('~', $new_cs_subscr);
							}
						}
						update_option('kd_cs_emails', $cs_subscriptions);
					}

					$cs_subscriptions = get_option('kd_cs_emails');
					$subscriptions = explode('~', $cs_subscriptions);

					$enabled = "";
					if(count($subscriptions) <2){
						$enabled = ' disabled';
					}
					?>
					<form id="csExport" name="csExport" class="csadmin" method="post" action="admin.php?page=_cs_menu_opd">
						<table class="csadminexp">
							<tr>
								<th style="width:100px;">Date</th>
								<th style="padding-left:12px;">Email</th>
								<th style="width:30px;">Delete</th>
							</tr>
							<?php
							for($x=0;$x<=count($subscriptions);$x++){
								$dat = explode('|', $subscriptions[$x]);
								if(!isset($dat[1])){ continue; }
								if(!isset($dat[0])){ $dat[0]='--'; }
							?>
							<tr id="csrow_<?php echo $x;?>">
								<td align="center"><?php echo $dat[0];?></td>
								<td style="padding-left:12px;">
									<a href="mailto:<?php echo $dat[1];?>"><?php echo $dat[1];?></a>
								</td>
								<td>
									<input type="checkbox" id="csdel_<?php echo $x;?>" name="csdel_<?php echo $x;?>" value="<?php echo $dat[1];?>" onClick="setCheck('csdel_<?php echo $x;?>');">
								</td>
							</tr>
							<?php
							} ?>
						</table>
						<div style="text-align:right;padding:20px 0px 0px 0px;">
							<input type="submit" id="kddelcs" name="kddelcs" class="button-danger" value="delete selected" disabled /> &nbsp;
							<input type="button" name="kdexpcs" class="button-primary" onClick="return exportSubscr(this.form);" value="export all"<?php echo $enabled;?> />
						</div>
					</form>
				</div>
			</div>
		</div>				
	</div>
	<?php
}

function storeSubscription($email, $cs_db){
	global $wp;
	global $wpdb;

	$cs_subscriptions = get_option('kd_cs_emails');
	$subscriptions = explode('~', $cs_subscriptions);

	$err = 'ERROR';
	$found = 0;
	$new_emails = array();
	for($x=0;$x<=count($subscriptions);$x++){
		$dat = explode('|', $subscriptions[$x]);
		if(!isset($dat[1])){ continue; }
		if(!isset($dat[0])){ $dat[0]='--'; }
		if(isset($dat[1]) && $dat[1] == $email){
			$found=1; break;
		}
	}

	if(!$found){
		$now = date('m/d/Y');
		$subscriptions[] = $now . '|' . $email; 
		$new_emails = implode('~', $subscriptions);

		if($new_emails){
			update_option('kd_cs_emails', $new_emails);
			$err = 'OK';
		}
	}

	return $err;
}

// verify email address...
function isEmail($email) {
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

// this is here for php4 compatibility
if(!function_exists('str_ireplace')){
  function str_ireplace($search,$replace,$subject){
    $token = chr(1);
    $haystack = strtolower($subject);
    $needle = strtolower($search);
    while (($pos=strpos($haystack,$needle))!==FALSE){
      $subject = substr_replace($subject,$token,$pos,strlen($search));
      $haystack = substr_replace($haystack,$token,$pos,strlen($search));
    }
    $subject = str_replace($token,$replace,$subject);
    return $subject;
  }
}

//just for debug purpose...
function djkd($d) {
	echo '<pre>';
	print_r($d);
	echo '</pre>';
}
?>