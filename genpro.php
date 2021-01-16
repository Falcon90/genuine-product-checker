<?php
/**
 * Plugin Name: Genuine product checker
 * Plugin URI: 
 * Description: A solution to add your products serial number and guarantee and give the user capability to check if his product GENUINE or not.
 * Version: 1.1
 * Author: Mahmoud Ofeisa
 * Author URI: www.mahmoud-ofeisa.com
 */
register_activation_hook(__FILE__,'genpro_activation_function');
add_action( 'admin_init', 'genpro_register_settings' );
add_action('admin_menu', 'genpro_register_options_page');
add_action('admin_menu', 'genpro_products_list');
function genpro_activation_function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'genpro_products';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     serial varchar(255),
     guarantee varchar(255),
     reg_date date,
     UNIQUE KEY id (id)
    ) $charset_collate;";
    if(($wpdb->get_var("show tables like ".$table_name)) != $table_name) {
        $result = $wpdb->query($sql);
    }
}
function genpro_register_settings() {
    add_option( 'genpro_option_name', true );
    register_setting( 'genpro_options_group', 'genpro_option_name', 'genpro_callback' );
    add_option( 'genpro_serial_text', 'Serial' );
    register_setting( 'genpro_options_group', 'genpro_serial_text', 'genpro_callback' );
    add_option( 'genpro_guar_text', 'Guarantee' );
    register_setting( 'genpro_options_group', 'genpro_guar_text', 'genpro_callback' );
    add_option( 'genpro_formbtn_text', 'Check' );
    register_setting( 'genpro_options_group', 'genpro_formbtn_text', 'genpro_callback' );
    add_option( 'genpro_form_positive', 'Your product is GENUINE!' );
    register_setting( 'genpro_options_group', 'genpro_form_positive', 'genpro_callback' );
    add_option( 'genpro_form_negative', 'Your product is not GENUINE!' );
    register_setting( 'genpro_options_group', 'genpro_form_negative', 'genpro_callback' );
    add_option( 'genpro_container_id', 'genpro_container' );
    register_setting( 'genpro_options_group', 'genpro_container_id', 'genpro_callback' );
}
function genpro_register_options_page() {
    add_options_page('Genpro settings', 'Genpro settings', 'manage_options', 'genpro', 'genpro_options_page');
}
function genpro_options_page(){
    wp_enqueue_script( 'genpro', plugins_url( '/js/genpro.js', __FILE__ ) );
    wp_enqueue_style( 'genpro', plugins_url( '/css/genpro.css', __FILE__ ) );
  ?>
    <div>
        <?php screen_icon(); ?>
        <h2>Genuine product checker</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'genpro_options_group' ); ?>
            <h3>Options</h3>
            <table class="genpro-setting-table">
                <tr>
                    <td><input type="checkbox" id="genpro_option_name" name="genpro_option_name" <?php if(get_option('genpro_option_name')){_e("checked");}else{} ?> /><label for="genpro_option_name">Use guarantee</label></td>
                </tr>
                <tr>
                    <td><label for="genpro_serial_text">Serial number text field place holder</label></td>
                    <td><input type="text" id="genpro_serial_text" name="genpro_serial_text" value="<?php _e(get_option('genpro_serial_text')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="genpro_guar_text">Guarantee text field place holder</label></td>
                    <td><input type="text" id="genpro_guar_text" name="genpro_guar_text" value="<?php _e(get_option('genpro_guar_text')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="genpro_formbtn_text">Check button text</label></td>
                    <td><input type="text" id="genpro_formbtn_text" name="genpro_formbtn_text" value="<?php _e(get_option('genpro_formbtn_text')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="genpro_form_positive">GENUINE message after check</label></td>
                    <td><input type="text" id="genpro_form_positive" name="genpro_form_positive" value="<?php _e(get_option('genpro_form_positive')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="genpro_form_negative">not GENUINE message after check</label></td>
                    <td><input type="text" id="genpro_form_negative" name="genpro_form_negative" value="<?php _e(get_option('genpro_form_negative')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="genpro_form_negative">Check form container id </label></td>
                    <td><input type="text" id="genpro_container_id" name="genpro_container_id" value="<?php _e(get_option('genpro_container_id')) ?>" /></td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
        <h2>Use this shortcode in any page or post to show product checker: <span style="color:#d47500;margin-left:10px;font-weight:700;"><input type="text" class="genpro_shortcode" value="[genpro-check-serial]" readonly/></span><button onclick="CopyShortcode()">Copy shortcode</button> </h2> 
        <div>In case you got this error: "Publishing failed. The response is not a valid JSON response." after puplishing the page, Install this plugin and use the <a href="https://wordpress.org/plugins/classic-editor/">classic editor</a></div>
    </div>
<?php } ?>
<?php
function genpro_products_list() {
    $page_title = 'Products list';
    $menu_title = 'Products list';
    $capability = 'edit_posts';
    $menu_slug = 'products_list';
    $function = 'genpro_products_list_display';
    $icon_url = '';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}
function genpro_products_list_display(){
    wp_enqueue_style( 'genpro', plugins_url( '/css/genpro.css', __FILE__ ) );
    $exist = 3;
    $delete = 0;
    global $wpdb;
	$final_product_query =  "select * from ".$wpdb->prefix."genpro_products";
    $final_products = $wpdb->get_results($final_product_query, OBJECT);
    if ( ! isset( $_POST['nonce_submit'] ) || ! wp_verify_nonce( $_POST['nonce_submit'], 'nonce_submit_action' ) ) {
        // do nothing
    }else{
        if($_SERVER["REQUEST_METHOD"] == "POST") {
           if(isset($_POST['genpro_delete'])){
                $genpro_serial_delete = sanitize_text_field($_POST['genpro_delete']);
                $delete_product_query =  "DELETE from ".$wpdb->prefix."genpro_products where serial = '$genpro_serial_delete'";
                $delete_product_res = $wpdb->get_results($delete_product_query, OBJECT);
                $delete = 1;
            }else{
                if ( isset( $_POST['genpro_serial'] ) ) {
                    $genpro_serial = sanitize_text_field($_POST['genpro_serial']);
                }
                if ( isset( $_POST['genpro_guarantee'] ) ) {
                    $genpro_guarantee = sanitize_text_field($_POST['genpro_guarantee']);
                }
                $dt2 = new DateTime();
                $adding_date = $dt2->format('Y-m-d');
                $exist = 0;
                if(strlen($genpro_serial) >= 1){
                    foreach ( $final_products as $final_product ) {
                        if($final_product->serial == $genpro_serial){
                            $exist = 1;
                        }
                    } 
                }else{
                    $exist = 1;
                }
                if(!$exist){
                    $insert_product_query =  "INSERT INTO ".$wpdb->prefix."genpro_products (serial, guarantee, reg_date) VALUES ('$genpro_serial', '$genpro_guarantee', '$adding_date');";
                    $insert_product_res = $wpdb->get_results($insert_product_query, OBJECT);
                }
            }
            $final_products = $wpdb->get_results($final_product_query, OBJECT);
        }
    }
    if(current_user_can('administrator')){
?>
    <form method="post" action="" id="genpro-add-form">
        <h3>Adding new products:</h3>
        <table>
            <tr valign="top">
                <th><input type="text" placeholder="Serial" name="genpro_serial"/>
                <?php wp_nonce_field( 'nonce_submit_action', 'nonce_submit' ); ?>
                </th>
                <?php if(get_option('genpro_option_name')){ ?>
                    <th><input type="text" placeholder="Guarantee" name="genpro_guarantee"  />
                    <?php wp_nonce_field( 'nonce_submit_action', 'nonce_submit' ); ?>
                </th>
                <?php } ?>
                <th><input type="submit" value="Add"></th>
            </tr>
        </table>
    </form>
<?php
    if(!$exist){
        echo "<div class='genpro-alert success'>";
        echo "<h3>Product with serial '".$genpro_serial."' Added successfully!</h3>";
        echo "</div>";
    }else if($exist == 1){
        echo "<div class='genpro-alert fail'>";
        echo "<h3>Product with serial '".$genpro_serial."' was added before or not valid!</h3>";
        echo "</div>";
    }
    if($delete == 1){
        echo "<div class='genpro-alert success'>";
        echo "<h3>Product with serial '".$genpro_serial_delete."' deleted successfully!</h3>";
        echo "</div>";
        $delete = 0;
    }
	echo "<table id='genpro-prodTable'>";
	    echo "<tr class='title-row'>";
	        echo "<th>Serial</th>";
	        echo "<th>Guaranty</th>";
            echo "<th>Registration date</th>";
            echo "<th></th>";
	    echo "</tr>";
	foreach ( $final_products as $final_product ) {
	    echo "<tr class='data-row'>";
	        echo "<td>$final_product->serial</td>";
	        echo "<td>$final_product->guarantee</td>";
            echo "<td>$final_product->reg_date</td>";
            echo "<td><form method=\"post\" action=\"\">
                    <input type=\"hidden\" name=\"genpro_delete\" value=\"$final_product->serial\" />";
                    wp_nonce_field( 'nonce_submit_action', 'nonce_submit' );
                    echo "<input type=\"submit\" value=\"Delete\">
                </form></td>";
	    echo "</tr>";
	}
    echo "</table>";
    }
}
function genpro_products_list_checkout($atts) {
    wp_enqueue_style( 'genpro', plugins_url( '/css/genpro-check.css', __FILE__ ) );
    global $wpdb;
    $genuine = 3;
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if ( isset( $_POST['genpro_check_serial'] ) ) {
            $genpro_serial = sanitize_text_field($_POST['genpro_check_serial']);
        }
        if ( isset( $_POST['genpro_check_guarantee'] ) ) {
            $genpro_guarantee = sanitize_text_field($_POST['genpro_check_guarantee']);
        }
        $check_product_query = "";

        if(get_option('genpro_option_name')){
            $check_product_query =  "select * from ".$wpdb->prefix."genpro_products where serial = '$genpro_serial' && guarantee = '$genpro_guarantee'";
        }else{
            $check_product_query =  "select * from ".$wpdb->prefix."genpro_products where serial = '$genpro_serial'";
        }    
        
        $check_product_res = $wpdb->get_results($check_product_query, OBJECT);
        if(count($check_product_res)){
            $genuine = 1;
        }else{
            $genuine = 0;
        }  
    }
    ?>
    <div>
    <div id="<?php  _e(get_option('genpro_container_id'))?>">
	    <form method="post" action="" id="genpro-check-form">
            <input type="text" class="genpro_serial" placeholder="<?php  _e(get_option('genpro_serial_text')) ?>" name="genpro_check_serial"  />
            <?php if(get_option('genpro_option_name')){
            ?><input type="text" class="genpro_guar" placeholder="<?php  _e(get_option('genpro_guar_text')) ?>" name="genpro_check_guarantee"  /><?php
            }?>
            <input type="submit" class="genpro_formbtn" value="<?php  _e(get_option('genpro_formbtn_text')) ?>">
        </form>
<?php
if($genuine == 1){
    echo "<div class='genpro-alert success'><div class='alert-text'>";
    _e(get_option('genpro_form_positive'));
    echo "</div></div>";
    $genuine = 3;
}else if($genuine == 0){
    echo "<div class='genpro-alert fail'><div class='alert-text'>";
    _e(get_option('genpro_form_negative'));
    echo "</div></div>";
    $genuine = 3;
}
?> </div> <?php
}
add_shortcode('genpro-check-serial', 'genpro_products_list_checkout');
