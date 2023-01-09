<?php 

function my_wp_nav_menu_args( $args = '' ) {
	$args[ 'container' ] = false;
	return $args;
}

function loadCssStyle() {
	wp_register_style( 'style_tems', get_template_directory_uri() . '/style.css', array(), '1.0', 'all' );
	wp_enqueue_style( 'style_tems' );
}

function loadUikitStyle() {
	wp_register_style( 'style_uikit', get_template_directory_uri() . '/css/uikit.min.css', array(), '3.14.1', 'all' );
	wp_enqueue_style( 'style_uikit' );
}

function loadYookassaStyle() {
	wp_register_style( 'style_yookassa', get_template_directory_uri() . '/css/yookassa_construct_form.css', array(), '0.0.4', 'all' );
	wp_enqueue_style( 'style_yookassa' );	
}

// include custom jQuery
function shapeSpace_include_custom_jquery() {
	wp_deregister_script('jquery');
	wp_enqueue_script( 'jquery', get_stylesheet_directory_uri() . '/js/jquery-3.6.0.min.js', array(), null, true );
}

function loadUikitJs() {
	wp_register_script( 'uikitjs', get_stylesheet_directory_uri() . '/js/uikit.min.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'uikitjs' );
}

function loadUikitIocn() {
	wp_register_script( 'uikiticon', get_stylesheet_directory_uri() . '/js/uikit-icons.min.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'uikiticon' );
}

function loadJQeryCooke() {
	wp_register_script( 'cooke', get_stylesheet_directory_uri() . '/js/jquery.cookie.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'cooke' );
}

function loadYookassaForm() {
	wp_register_script( 'yookassa', '//yookassa.ru/integration/simplepay/js/yookassa_construct_form.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'yookassa' );
}

function loadFunctions() {
	wp_register_script( 'functions', get_stylesheet_directory_uri() . '/js/function.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'functions' );
}

// This theme uses wp_nav_menu() in two locations.
register_nav_menus( array(
	'primary' => __( 'Primary Menu', 'THEMEPASSANGER' ),
) );

add_action( 'after_setup_theme', function(){ show_admin_bar( false ); });
add_action( 'init', 'leng_setcookie' );
add_action( 'wp_enqueue_scripts', 'loadCssStyle' );
add_action( 'wp_enqueue_scripts', 'loadUikitStyle' );
add_action( 'wp_enqueue_scripts', 'loadYookassaStyle' );
add_action( 'wp_enqueue_scripts', 'shapeSpace_include_custom_jquery');
add_action( 'wp_enqueue_scripts', 'loadUikitJs' );
add_action( 'wp_enqueue_scripts', 'loadUikitIocn' );
add_action( 'wp_enqueue_scripts', 'loadJQeryCooke' );
add_action( 'wp_enqueue_scripts', 'loadYookassaForm' );
add_action( 'wp_enqueue_scripts', 'loadFunctions' );
add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );
remove_filter( 'authenticate', 'wp_authenticate_username_password',  20, 3 );
add_action( 'wp_ajax_register_user', 'action_register_user' );
add_action( 'wp_ajax_nopriv_register_user', 'action_register_user' );
add_action( 'wp_ajax_login_user', 'action_login_user' );
add_action( 'wp_ajax_nopriv_login_user', 'action_login_user' );
add_action( 'wp_ajax_quick_model', 'action_quick_model' );
add_action( 'wp_ajax_nopriv_quick_model', 'action_quick_model' );
add_action( 'wp_ajax_uuid_pay', 'action_uuid_pay' );
add_action( 'wp_ajax_nopriv_uuid_pay', 'action_uuid_pay' );
add_action( 'template_redirect', 'prefix_redirect_function', 9 );
add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );
remove_filter( 'comment_text', 'wpautop' );

// MY FUNCTIONS //------------------------------------------------------------//

function leng_setcookie(){
	if(!isset($_COOKIE['leng'])){
		setcookie( 'leng', 'ru', 3 * time()+3600, COOKIEPATH, COOKIE_DOMAIN );
	}
	
	if(!defined('DOING_AJAX') && is_admin() && !current_user_can('administrator')){
		wp_redirect(home_url());
		exit();
	}
}

function get_current_user_role(){
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	
	return $wp_roles->role_names[$role];
}

function getLogo(){
	$data = [
		'image' => get_stylesheet_directory_uri().'/images/logo.png',
		'href'  => site_url(),
		'alt'   => get_bloginfo('name')
	];
	
	return($data);
}

function setLeng(){
	$leng = (isset($_COOKIE['leng'])) ? $_COOKIE['leng'] : 'ru';
	return($leng);
}

function get_enpost($id){
	$title = get_post_meta( $id, 'title_en', true );
	$content = get_post_meta( $id, 'content_en', true );
	
	$data = [
		'title' => $title,
		'content' => $content
	];

	return($data);
}

function getTile($id, $leng){
	$query = new WP_Query( 'category__in=' . $id . '&posts_per_page=' . get_category( $id )->category_count . '&order=ASC' );
	$cat_title = get_the_category_by_ID($id);
	if($id == 1 && $leng == 'en') $cat_title = 'Categories';
	if ($query->have_posts()){
		while ($query->have_posts()):
			$query -> the_post();
			$id = get_the_id();
			$title = get_the_title();
			$title_en   = get_post_meta( $id, 'title_en', true );
			$content = get_the_content();
			$content_en = get_post_meta( $id, 'content_en', true );
			$thumb_id = get_post_thumbnail_id();
			$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
			$item[] = [
				'id'      => $id,
				'title'   => ($leng == 'ru') ? $title : $title_en,
				'content' => ($leng == 'ru') ? $content : $content_en,
				'free'    => get_post_meta( $id, 'free', true ),
				'url'     => get_permalink($id),
				'icon'    => $thumb_url[0]
			];
		
		endwhile;
		wp_reset_postdata();
	}
	
	$data = [
		'title' => $cat_title,
		'cards' => $item
	];
	
	return($data);	
}

function get_airlins( $id, $leng ){
	$query = new WP_Query( 'category__in=' . $id . '&posts_per_page=' . get_category( $id )->category_count . '&order=DESC' );
	$cat_title = get_the_category_by_ID($id);
	if($id == 4 && $leng == 'en') $cat_title = 'An incomplete list of major Russian airlines';
	if($id == 5 && $leng == 'en') $cat_title = 'Major airlines in the world';
	if ($query->have_posts()){
		while ($query->have_posts()):
			$query -> the_post();
			$id         = get_the_id();
			$title      = get_the_title();
			$content    = get_the_content();
			$thumb_id   = get_post_thumbnail_id();
			$thumb_url  = wp_get_attachment_image_src( $thumb_id, 'full', true );
			$link_ru    = get_post_meta( $id, 'air_link_ru', true );
			$title_en   = get_post_meta( $id, 'title_en', true );
			$content_en = get_post_meta( $id, 'content_en', true );
			$link_en    = get_post_meta( $id, 'air_link_en', true );
			$item[] = [
				'id'      => $id,
				'title'   => ($leng == 'ru') ? $title : $title_en,
				'image'   => $thumb_url[0],
				'content' => ($leng == 'ru') ? $content : $content_en,
				'link'    => ($leng == 'ru') ? $link_ru : $link_en
			];

		endwhile;
		wp_reset_postdata();
	}

	$data = [
		'title' => $cat_title,
		'items' => $item
	];
	
	return($data);
}

function get_quest( $id, $leng ){
	$query = new WP_Query( 'category__in=' . $id . '&posts_per_page=' . get_category( $id )->category_count . '&order=DESC' );
	$cat_title = get_the_category_by_ID($id);
	if($id == 6 && $leng == 'en') $cat_title = 'Questions';
	if($id == 7 && $leng == 'en') $cat_title = 'Errors';
	if ($query->have_posts()){
		while ($query->have_posts()):
			$query -> the_post();
			$id         = get_the_id();
			$title      = get_the_title();
			$content    = get_the_content();
			$title_en   = get_post_meta( $id, 'title_en', true );
			$content_en = get_post_meta( $id, 'content_en', true );
			$free       = get_post_meta( $id, 'free', true );
			$item[] = [
				'id'      => $id,
				'title'   => ($leng == 'ru') ? $title : $title_en,
				'content' => ($leng == 'ru') ? $content : $content_en,
				'free'    => $free
			];

		endwhile;
		wp_reset_postdata();
	}
	
	$data = [
		'title' => $cat_title,
		'items' => $item
	];
	
	return($data);
}

function guidv4($data = null) {
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function action_login_user(){
	$error = [];
	if(isset($_POST['leng'])):
	$leng = sanitize_text_field($_POST['leng']);
	$email = sanitize_text_field($_POST['email']);
	$password = sanitize_text_field($_POST['password']);
	if(!preg_match('/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/', $email))$error[] = 'email_error';
	if(strlen($password) < 1)$error[] = 'password_error';
	endif;

	if(count($error) > 0 ){
		$result = ['result' => 'error','message' => $error];
	}else{
		$auth = wp_authenticate( $email, $password );
		if (is_wp_error($auth)){
			$error[] = 'error_login';
			$result = ['result' => 'error','message' => $error];
		}else{
			$result = ['result' => 'success','message' => []];
			wp_set_auth_cookie($auth -> ID);
		}
	}
	
	echo(wp_json_encode($result));
	wp_die();
}

function action_register_user(){
	$error = [];
	if(isset($_POST['leng'])):
	$leng = sanitize_text_field($_POST['leng']);
	$logname = sanitize_text_field($_POST['logname']);
	$email = sanitize_text_field($_POST['email']);
	$password = sanitize_text_field($_POST['password']);
	$pasdoes = sanitize_text_field($_POST['pasdoes']);
	if(!preg_match('/^[A-Za-z]{3,12}/', $logname)) $error[] = 'loginerror';
	if(!preg_match('/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/', $email))$error[] = 'email_error';
	if(!preg_match('/^[A-Za-z-0-9]{6,12}/', $password))$error[] = 'password_error';
	if($password !== $pasdoes)$error[] = 'pasdoes_error';
	endif;
	
	if(count($error) > 0 ){
		$result = ['result' => 'error','message' => $error];
	}else{
		$user_id = wp_create_user( $logname, $password, $email );
		if (is_wp_error($user_id)){
			$error[] = $user_id -> get_error_message();
			$result = ['result' => 'error','message' => $error];
		}else{
			$result = ['result' => 'success','message' => []];
			wp_set_auth_cookie($user_id);
		}
	}
	
	echo(wp_json_encode($result));
	wp_die();
}

function action_quick_model(){
	if(isset($_POST['leng'])):
		$leng = sanitize_text_field($_POST['leng']);
		$id = $_POST['post_id'];
		$data = get_post_field( 'post_content', $id, 'row' );
	endif;
	
	echo($data);
	wp_die();
}

if(isset($_GET['shopsuccess'])):
	$uuid = sanitize_text_field($_GET['uuid']);
	if(strlen($uuid) > 10){
		$current_user = wp_get_current_user();
		if($current_user->id > 0):
			$id_user = $current_user->id;
			global $wpdb;
			$guid = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}guid WHERE user_id = {$id_user}");
			if(count($guid)){
				if($uuid == $guid[0]->uuid):
					wp_update_user([
  						'ID' => $id_user,
  						'role' => 'contributor'
					]);
					echo('<script>location.replace("https://airpassenger.site");</script>');
				endif;
			}
		endif;
	}
endif;

function action_uuid_pay(){
	$uuid = guidv4();
	$current_user = wp_get_current_user();
	if($current_user->id > 0):
	global $wpdb;
	$guid = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}guid WHERE user_id = {$current_user->id}");
	if(count($guid)){
		$wpdb->update("{$wpdb->prefix}guid", ['uuid' => (String)$uuid], ['id' => $guid[0]->id],['%s']);
	}else{
		$wpdb->insert("{$wpdb->prefix}guid",['user_id' => $current_user->id,'uuid' => (String)$uuid],['%d', '%s']);
	}
	
	$data = [
		'email' => $current_user->user_email,
		'uuid' => (String)$uuid
	];
	endif;

	echo(wp_json_encode($data));	
	wp_die();
}

function prefix_redirect_function(){
	$id = get_the_ID();
	$role = get_current_user_role();
	if(($role !== 'Contributor')):
		if ($id == 49 || $id == 52 || $id == 55 || $id == 58 || $id == 61 || $id == 65 || $id == 68 || $id == 71) {
    		wp_redirect( home_url() );
    		exit;
  		}
	endif;
}

function get_quick( $id, $page_id, $leng ){
	$query = new WP_Query( 'category__in=' . $id . '&posts_per_page=' . get_category( $id )->category_count . '&order=ASC' );
	if ($query->have_posts()){
		while ($query->have_posts()):
			$query -> the_post();
			$id         = get_the_id();
			$title      = get_the_title();
			$title_en   = get_post_meta( $id, 'title_en', true );
			$content    = get_the_content();
			$content_en = get_post_meta( $id, 'content_en', true );
			$weight     = get_post_meta( $id, 'weight', true );
			$icon_coor  = get_post_meta( $id, 'icon_coor', true );
			$accord_id  = get_post_meta( $id, 'accord_id', true );
			$item[] = [
				'id'        => $id,
				'title'     => ($leng == 'ru') ? $title : $title_en,
				'content'   => ($leng == 'ru') ? $content : $content_en,
				'weight'    => $weight,
				'icon_coor' => $icon_coor,
				'accord_id' => $accord_id
			];

		endwhile;
		wp_reset_postdata();
	}

	$data = [
		'title' => ($leng == 'ru') ? get_the_title($page_id) : get_post_meta( $page_id, 'title_en', true ),
		'items' => $item
	];
	
	return($data);
}