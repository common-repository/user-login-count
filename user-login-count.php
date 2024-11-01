<?php
/*
Plugin Name: User login count 
Description: Add a sortable column to the users list on Single Site WordPress to show user login count.
Version: 3.2.4
Author: Rahul Gupta
Author URI: 
*/

define('USER_LOGIN_COUNT_URL',plugin_dir_url( __FILE__ ));
define('USER_LOGIN_COUNT_PATH',plugin_dir_path( __FILE__ ));


function user_login_count_script_enque() {

	wp_enqueue_script( 'reset',  USER_LOGIN_COUNT_URL . 'assests/js/reset.js');

	$ulc_array = array(

		'ajaxurl'  => admin_url('admin-ajax.php'),
		'site_url' => site_url(),
		'nonce'    => wp_create_nonce('ajaxnonce'),
	);
	wp_localize_script( 'reset', 'rwbObj', $ulc_array );

}
add_action( 'admin_enqueue_scripts', 'user_login_count_script_enque' );

//create table

 function user_login_install() {
        global $wpdb;
        $table_name = "loginCount";
        $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.$table_name."` (
             `id` int(11) NOT NULL AUTO_INCREMENT,
             `user_id` int(11) NOT NULL,
             `count` int(11) NOT NULL,
             PRIMARY KEY ( `id` )
            ) ENGINE=MyISAM";

        $wpdb->query( $sql );
    }
	
	function user_login_uninstall() {
        global $wpdb;
        $table_name = "loginCount";
        $sql="DROP table `".$wpdb->prefix.$table_name."` ";					
        $wpdb->query($sql);
    }

	register_activation_hook( __FILE__,'user_login_install');
	register_deactivation_hook( __FILE__, 'user_login_uninstall');

// Register the column - Login Count
	function userlogin( $column ) {
		$column['userlogin'] = 'User Login Attempts';
	
		return $column;
	}
// Display the column content
   function userlogin_column( $val, $column_name, $user_id ) {
	 
		if ( 'userlogin' != $column_name )
           return $val;
		   global $wpdb;
		$table_name   = $wpdb->prefix."loginCount"; 
	    $result = $wpdb->get_results($wpdb->prepare("select count(user_id) from $table_name WHERE user_id = %d",$user_id),ARRAY_A);
      	$res =  $result[0]['count(user_id)'];
		$resetButton  =  '<a href="javascript:void(0)" data-id="'.$user_id.'" class="resetuserdata">Reset</a>';
		if($res > 0){
			return $res.'&nbsp;&nbsp;&nbsp;&nbsp;'.$resetButton;
		}else{

			return $res.'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
  }
  
  	function count_user_login_ulc( $user_login, $user ) {
		enrty_count($user->ID);
	  }
	add_action( 'wp_login', 'count_user_login_ulc', 10, 2 );
	
	function enrty_count($id){
		
		global $user,$wpdb;
		 $table_name   = $wpdb->prefix."loginCount";
			 $userId       = $id; 
			 $defaultValue = 1;
			 $wpdb->insert( 
						$table_name, 
						array( 
							'user_id'   => $userId, 
							'count'     => $defaultValue
						), 
						array( 
							'%d', 
							'%d'
						) 
					);
	 }
  
  function userlogin_column_sortable($columns) {
			  $custom = array(
			  'userlogin'    => 'login',
			  );
		  return wp_parse_args($custom, $columns);
 }

 function userlogin_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'userlogin' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
							'meta_key' => 'userlogin',
							'orderby' => 'meta_value'
					) );
			}
			return $vars;
  }
add_filter( 'manage_users_columns', 'userlogin' );	
add_filter( 'manage_users_sortable_columns', 'userlogin_column_sortable' );
add_filter( 'request','userlogin_column_orderby');
add_filter( 'manage_users_custom_column', 'userlogin_column', 15, 3 );

/* reset counter */
add_action('wp_ajax_ulc_reset_counter','process_ulc_reset_counter');
function process_ulc_reset_counter(){

	global $wpdb;
	$userId     = intval($_POST['userId']);
	$table  	= $wpdb->prefix.'loginCount'; 
	$deleteRow  = $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE user_id = %d",$userId)); 

	echo json_encode(array('msg'=>'sucess'));

die;	
}
?>