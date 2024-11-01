<?php
/*
 * Plugin Name: Woocommerce Product Review Export/Import
 * Plugin URI: https://webmantechnologies.com
 * Description: Toolkit for import and export the woocommerce Reviews, Rating and Meta.
 * Author: Webman Technologies
 * Text Domain: swrei-review-exim
 * Version: 1.0
 * Requires at least: 4.4
 * Tested up to: 4.9
 */
defined( 'ABSPATH' ) or exit;

//WC check
$active_plugins = get_option( 'active_plugins', array() );
if( !in_array( 'woocommerce/woocommerce.php',$active_plugins ) ){
	
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
	deactivate_plugins( plugin_basename( __FILE__ ) );
	if( isset( $_GET['activate'] ))
      unset( $_GET['activate'] );
}

class SWREI_wc_export_review {	
  
	protected static $instance;
	protected $adminpage;
	protected $template;
	
	public function __construct() {
		
		add_action( 'admin_init', array( $this, 'SWREI_woo_version_check' ) );		
		
		//add admin page
		add_action('admin_menu', array($this, 'SWREI_add_menulink'));
		
		//add script and style
		add_action( 'admin_enqueue_scripts', array( $this, 'SWREI_wc_export_review_enqueue' ) );

	}
	
	public function SWREI_woo_version_check() {
			
		global $woocommerce; 
					
		if ( version_compare( $woocommerce->version, '2.4.9', '<=' ) ) {
			add_action( 'admin_notices', array($this,'SWREI_admin_notice_msg') );
			
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
			deactivate_plugins( plugin_basename( __FILE__ ) );
			
			if( isset( $_GET['activate'] ))
			unset( $_GET['activate'] );
		
			return false;
		}
	}
	
    public function SWREI_add_menulink() {

		 $this->adminpage = add_submenu_page(
					'woocommerce',
					__('Review Export/Import','swrei-review-exim'),
					__('Review Export/Import','swrei-review-exim'), 
					'manage_woocommerce',
					'woo_review_export',
					array($this, 'SWREI_render_submenu_pages' ),
					''
				);	
	}	
	
	public function SWREI_render_submenu_pages() {		
			
			$user_permission = current_user_can( 'edit_posts' );
			
			if ( $user_permission == true ) {		
			
			$review_export_nonce = sanitize_text_field($_POST['review_export_nonce']);
			$review_import_nonce = sanitize_text_field($_POST['review_import_nonce']);
			
			
			$fieldcheck_reviewexport = sanitize_text_field($_POST['review_export_call']);
			$review_import_call = sanitize_text_field($_POST['review_import_call']);
	
			$data['exportType'] = sanitize_text_field($_POST['option']);		

			if(( wp_verify_nonce( $review_export_nonce, 'review_export_nonce' ) || isset( $review_export_nonce ) ) && (isset($fieldcheck_reviewexport) && $fieldcheck_reviewexport != NULL)){
				
					// Include the main export file.
					include_once dirname( __FILE__ ) . '/inc/review_export.php';
						
					$res = SWREI_reviewexport::SWREI_simple_reviewexporter();
					
					//show  notice
					if($res == false){
						$notice_msg = 'No reviews to export.';
						$notice_class = 'error';
					}
				
			}else if(( wp_verify_nonce( $review_import_nonce, 'review_import_nonce' ) || isset( $review_import_nonce ) ) && (isset($review_import_call) && $review_import_call != NULL)){
					
					$allowed =  array('csv');
					$filename = sanitize_file_name($_FILES['review_import_file']['name']);
					$format = pathinfo($filename, PATHINFO_EXTENSION);
					
					if(!in_array($format,$allowed) ) { 
						
						//show  notice
						$notice_msg = '"'.$format .'" File type not allowed!!';
						$notice_class = 'error';
						$active_tab = 'review';
						
					}else{					
		
						// Include the main import file.
						include_once dirname( __FILE__ ) . '/inc/review_import.php';
						
						$temp_name = ($_FILES["review_import_file"]["tmp_name"]);	
						
						$review_status = SWREI_reviewimport::SWREI_review_importer($temp_name);														
						$review_status = json_decode($review_status);
						
						//show notice
						if($review_status->status== 'success'){
							$notice_msg = 'Reviews and meta successfully Imported !!';							 
						}else{
							$notice_msg = 'Processing...  Error with above listed records. !';
							
							$review_error_data =  $review_status->data;							
							$this->SWREI_reviewImport_errorHtml($review_error_data);							
						}
						
						$active_tab = 'review';			
			}
		}
		}
		?>
		<?php  
			if(isset($notice_msg) && $notice_msg != '') { 
			
			 (isset($notice_class) && '' != $notice_class) ? '' : $notice_class ='success'; ?>
			
			<div class="SWREI_notice notice ">
				<center>
					<label>
						<strong></strong>
					</label>
				</center>
			</div>
			<div class="notice notice-<?php echo sanitize_html_class($notice_class) ?> is-dismissible"> 
				<p><strong><?php _e( $notice_msg, 'swrei-review-exim' );?></strong></p>				
			</div>
			
		<?php } ?>
				
		<div class="SWREI_export_wrapper" >
		<?php echo $this->SWREI_get_msgbox();  ?>		
			
			<div class="tab">				  
			  <button class="tablinks <?php echo (  (!isset($active_tab)) || (isset($active_tab) && $active_tab =='review')) ? sanitize_html_class('active') : ''; ?>" onclick="SWREI_openTab(event, 'review_export')"><?php echo esc_html("Review") ?></button>		  
			</div>
			<div id="review_export" class="tabcontent " <?php echo  (!isset($active_tab)) || ((isset($active_tab) && $active_tab =='review')) ? ' style="display: block;"' : ''; ?>>
				<?php $this->template = $this->SWREI_get_template('review_export'); 	?> 
			</div>	
		</div>
		<?php
	}
		
	public function SWREI_wc_export_review_enqueue() {	
	
		wp_enqueue_style('export_review-style', plugins_url('/assets/css/woo_reviewexpor.css', __FILE__ ) );			
		wp_enqueue_script('export_review-script',  plugins_url('/assets/js/woo_reviewexpor.js', __FILE__ ) , array('jquery'), '', true);
		
		wp_localize_script( 'export_review-script', 'plajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		));
	}
	
	public function SWREI_get_plugin_dir(){
		
		 return dirname( __FILE__ );	
		 
	}
	
	public function SWREI_get_template($template){ 
	
		$template_name = 'template_'.$template.'.php';			
		include  $this->SWREI_get_plugin_dir().'/template/'.$template_name;
		
	}
	
	public function SWREI_get_loader() {
		
		$img = plugin_dir_url( __FILE__ ) .'assets/img/loader.gif';
		$html = "<div class='SWREI_loader' style='display:none;' ><center><img src=".$img." /><label>Refreshing ...</label></center></div>";
		return $html;
		
	}
	
	public function SWREI_get_msgbox() {
		
		$html = "<div class='msg_box'></div>";
		return $html;
		
	}
	
	public function SWREI_instance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
		
	}
	
	
	public function SWREI_recursive_sanitize_text_field($array) {
		
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = SWREI_recursive_sanitize_text_field($value);
			}
			else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
		
	}
	
	public function SWREI_admin_notice_msg() {
		
		global $woocommerce;
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php   _e("<b>Simple Review Export/Import is inactive</b>. Simple Review Export/Import requires a minimum of WooCommerce v2.5.0","swrei-review-exim"); ?></p>
		</div>
		<?php
	}
	
	public function SWREI_reviewImport_errorHtml($review_error_data){
		  
		$html = ' <div class="SWREI_reviewimport_wrapper SWREI_export_wrapper"> '
				.' <table cellspacing="0" cellpaddong="0" width="100%" class="order_mapping"><tr><th>S.No</th><th>ID</th><th>Type</th><th>Status</th><th>Message</th></tr>';
		$review_error_data = (array)$review_error_data;
		
		if(is_array($review_error_data) && !empty($review_error_data)){
			
			$count = 1;
			foreach($review_error_data as $type=>$review_error){
				
				foreach($review_error as $msg){
					$html .= "<tr><td>$count</td><td>$msg[0]</td><td>$type</td><td>Skipped</td><td>Product Id not found in database.</td></tr>";
					$count++;
				}				
			} 
		}
		
		echo  $html .= '</table></div>'; 	
	}
	
	

}

function SWREI_wc_export_review() {
	return SWREI_wc_export_review::SWREI_instance();
}

SWREI_wc_export_review();
?>