<?php
/*
Plugin Name: Keen Rating
Plugin URI: 
Description: A simple star wordpress rating plugin
Version: 1.0.0
Author: Mahedi Hasan
Author URI: 
License: GPLv2 or later
Text Domain: keen-rating
*/
defined( 'ABSPATH' ) || exit;

if(!defined('KEEN_PLUGIN_VERSION')){
    define('KEEN_PLUGIN_VERSION','1.0.0');
}
if(!defined('KEEN_PLUGIN_DIR')){
    define('KEEN_PLUGIN_DIR',plugin_dir_url( __FILE__ ));
}
 if(!function_exists('keen_star_rating_plugin_scripts')){
    function keen_star_rating_plugin_scripts(){

    wp_enqueue_style('keen-rating-css', KEEN_PLUGIN_DIR. 'assets/css/style.css');

    wp_enqueue_script('keen-rating-script', KEEN_PLUGIN_DIR. 'assets/js/script.js',array('jquery'),'1.0.0',true);
   
    wp_enqueue_style( 'dashicons' );

    wp_localize_script( 'keen-rating-script', 'keen_rating_object', [
         'ajax_url' => admin_url('admin-ajax.php')
    ]);
    }
    add_action('wp_enqueue_scripts','keen_star_rating_plugin_scripts');
}

//Create the rating interface.
add_shortcode( 'rating', 'keen_rating_field' );
function keen_rating_field () {

    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "keen_rating"; 
    $post_id = get_the_ID();
    $total_row = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE (post_id = '".$post_id."')");

 ?>
    
	<div class="comments-rating">
        <div class="rating-container" data-id="<?php echo $post_id ?>">
        <?php echo loadRatingMarkup($post_id); ?>
        </div>
        <div id="error"></div>
    </div> 
    
    <div class="result">(<?php echo $total_row; ?>)
    </div>  
    <?php
    
}

register_activation_hook(__FILE__, 'Create_Custom_Database_Rating_Table');
function Create_Custom_Database_Rating_Table(){
    global $wpdb;
    $table_name = $wpdb->prefix . "keen_rating"; 
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      time timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
      post_id mediumint(9) NOT NULL,
      rating mediumint(9) NOT NULL,
      ip varchar(55) NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function loadRatingMarkup($id){

    if(!isset($id)){
        return;
    }
    $average_rat = 0;

    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "keen_rating"; 
    $html = '';

    $total_row = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE (post_id = '".$id."')");

    $rating_sum = $wpdb->get_var("SELECT SUM(rating) FROM $table_name WHERE (post_id = '".$id."')");
   
   if(isset($rating_sum) && isset($total_row)){
        $average_rat = ceil($rating_sum/$total_row);
   }

    for ( $i = 1; $i <= 5; $i++ ):
        $average = $i <= $average_rat ? 'average' : '';
        $html .= sprintf('<span id="star" class="fas fa-star %1s"></span>', $average, $id, $i);
    endfor;
    return $html;
}

/**
 * Star Rating Plugin Display Data
 */
function keen_rating_ajax_display_data(){
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "keen_rating"; 

    $post_id = $_POST['postid']; 
    $rating = $_POST['rating'];
    $ip = $_SERVER['REMOTE_ADDR'];

    
    if(isset($post_id) && isset($ip)){

        $check_user_like = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE (post_id = '".$post_id."' AND ip = '". $ip ."')");
       
       if($check_user_like == true){
         echo 'You already rated this post';
       }
       else{

         $wpdb->insert(
             ''.$table_name.'',
               array(
                 'post_id' => $post_id,
                 'rating' => $rating,
                 'ip' => $ip
               ),
               array(
                   '%d',
                   '%d',
                   '%s'
               )
           );

       } 

    }
    
    $total_row = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE (post_id = '".$post_id."')");

    $ratingData = [ 'star' => loadRatingMarkup($post_id), 'user' => $total_row];
    wp_send_json($ratingData);
  }

add_action('wp_ajax_keen_rating_ajax_display_data','keen_rating_ajax_display_data');
add_action('wp_ajax_nopriv_keen_rating_ajax_display_data','keen_rating_ajax_display_data');

/** Admin Section */

function register_settings()
{
    add_option('option_name', 'This is my option.');
    register_setting('options_group', 'option_name', 'keenshot_callback');
}
add_action('admin_init', 'register_settings');
function register_star_rating_options_page()
{
    add_options_page('Page Title', 'Keen star rating', 'manage_options', 'keen_star_rating', 'options_page');
}
add_action('admin_menu', 'register_star_rating_options_page');
function options_page()
{
?>
    <div>       
        <h1>Shortcode: [rating]</h1>
    </div>
<?php
}