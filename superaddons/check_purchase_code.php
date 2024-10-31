<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
if(!class_exists('Superaddons_Check_Purchase_Code')){
	class Superaddons_Check_Purchase_Code {
		protected $data;
		public function __construct( $data ) { 
			$defaults = array(
				"plugin"=>false,
				"id"=>false,
				"bundle"=>false,
				"pro" => "",
				"document"=>"#"
			);
			$args = wp_parse_args( $data, $defaults );
			$this->data = $args;	
			add_filter( 'plugin_action_links_' . $this->data["plugin"] , array( $this, 'add_action' ) );
			add_action( 'wp_ajax_rednumber_check_purchase_code', array($this,'check_purchase_code_ajax') );
			add_action( 'wp_ajax_rednumber_check_purchase_code_remove', array($this,'check_purchase_remove_code_ajax') );
			add_action('admin_enqueue_scripts', array($this,'add_js'));
			add_action( 'admin_notices', array($this,"add_pro") );
			add_action( 'admin_head', array($this,"add_head") );
		}
		function add_head() {
			?>
			<style type="text/css">
				.pro_disable::after {
					content: "Pro";
					position: absolute;
					bottom: 0;
					right: 0;
					background: red;
					padding: 3px;
					font-size: 11px;
					color: #fff;
					border-radius: 5px 0 0 0;
				}
				.pro_disable {
					position: relative;
				}
				.pro_disable_padding{
					padding: 10px !important;
				}
				.pro_text_style{
					color:#9f9e9e;
				}
				body .pro_disable_fff {
					background: transparent;
				}
			</style>
			<?php
		}
		function add_pro(){
	        global $pagenow;
	        $admin_pages = array('index.php', 'plugins.php');
	        $check = get_option( '_redmuber_item_'.$this->data["id"] );
	        if($check != "ok" ) {
		        if ( in_array( $pagenow, $admin_pages )) {
		        ?>
		         <div class="notice notice-warning is-dismissible">
		            <p><strong><?php echo esc_attr($this->data["plugin_name"]) ?>: </strong><?php esc_html_e( 'Enter Purchase Code below the plugin  or Upgrade to pro version: ', 'rednumber' ); ?> <a href="<?php echo esc_url( $this->data["pro"] ) ?>" target="_blank" ><?php echo esc_url( $this->data["pro"] ) ?></a></p>
		        </div>
		        <?php
		    	}
	    	}
	    }
		function add_js(){
			wp_enqueue_script('rednumber_check_purchase_code', plugins_url('rednumber_check_purchase_code.js', __FILE__),array("jquery"));
		}
		function add_action($links){
			$check = get_option( '_redmuber_item_'.$this->data["id"] );
			$class_1 = "";
			$class_2 = "";
			if( $check =="ok" ){
				$class_1 = "hidden";
			}else{
				$class_2 = "hidden";
			}
			$mylinks = array(
			        '<div class="rednumber-purchase-container rednumber-purchase-container_form '.$class_1.'">'.esc_html__("Purchase Code:","rednumber").' <input data-id="'.$this->data["id"].'" type="text"><a href="#" class="button button-primary rednumber-active">'.esc_html__("Active","rednumber").'</a></div>
			         <div class="rednumber-purchase-container rednumber-purchase-container_show '.$class_2.'">Purchased: <span>'.get_option( '_redmuber_item_'.$this->data["id"]."_code" ).'</span> <a data-code="'.get_option( '_redmuber_item_'.$this->data["id"]."_code" ).'" data-id="'.$this->data["id"].'" href="#" class="rednumber-remove">'.esc_html__("Remove","rednumber").'</a></div><a target="_blank" class="'.$class_1.'"  href="'.$this->data["pro"].'" >'.esc_html__("Get pro version","rednumber").'</a>',
			    );
			$mylinks[] ='<a href="'.$this->data["document"] .'" target="_blank" />Document</a>';
		    return array_merge( $links, $mylinks );
		}
		function check_purchase_code_ajax(){
			$code = sanitize_text_field($_POST["code"]);
			$id = sanitize_text_field($_POST["id"]);
			$status = $this->check_purchase_code($code,$id);
			if( $status == "ok"){
				update_option( '_redmuber_item_'.$id, "ok" );
				update_option( '_redmuber_item_'.$id."_code", $code );
			}
			echo esc_attr($status);
			die();
		}
		function check_purchase_remove_code_ajax(){
			$id = sanitize_text_field($_POST["id"]);
			$code = sanitize_text_field($_POST["code"]);
			delete_option('_redmuber_item_'.$id);
			delete_option('_redmuber_item_'.$id."_code");
			$personalToken = "uzAMx8rZ3FRV0ecu8t1pXNWG0d0NA6qL";
			$userAgent = "Purchase code verification";
			$ch = curl_init();
			$domain_name = preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
			curl_setopt_array($ch, array(
			    CURLOPT_URL => "https://add-ons.org/wp-json/removepurchase_code/apiv2/token/{$code}/".htmlentities($domain_name),
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_TIMEOUT => 20,
			    CURLOPT_HTTPHEADER => array(
			        "Authorization: Bearer {$personalToken}",
			        "User-Agent: {$userAgent}"
			    )
			));
			$response = curl_exec($ch);
			if (curl_errno($ch) > 0) { 
			    return "Error connecting to API: " . curl_error($ch);
			}
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($responseCode === 404) {
			    return "The purchase code was invalid";
			}
			if ($responseCode !== 200) {
			    return "Failed to validate code due to an error: HTTP {$responseCode}";
			}
			$body = json_decode($response,true);
			if ($body === false && json_last_error() !== JSON_ERROR_NONE) {
			    return "Error parsing response";
			}
			die();
		}
		function resolve_root_domain($url, $max=6){
		$domain = parse_url($url, PHP_URL_HOST);
		if (!strstr(substr($domain, 0, $max), '.'))
			return ($domain);
		else
			return (preg_replace("/^(.*?)\.(.*)$/", "$2", $domain));
		}
		function check_purchase_code($code,$id_item){
			$personalToken = "uzAMx8rZ3FRV0ecu8t1pXNWG0d0NA6qL";
			$userAgent = "Purchase code verification";
			$ch = curl_init();
			$domain_name = preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
			curl_setopt_array($ch, array(
			    CURLOPT_URL => "https://add-ons.org/wp-json/checkpurchase_code/apiv2/token/{$code}/".htmlentities($domain_name)."/".$id_item,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_TIMEOUT => 20,
			    CURLOPT_HTTPHEADER => array(
			        "Authorization: Bearer {$personalToken}",
			        "User-Agent: {$userAgent}"
			    )
			));
			$response = curl_exec($ch);
			if (curl_errno($ch) > 0) { 
			    return "Error connecting to API: " . curl_error($ch);
			}
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($responseCode === 404) {
			    return "The purchase code was invalid";
			}
			if ($responseCode !== 200) {
			    return "Failed to validate code due to an error: HTTP {$responseCode}";
			}
			$body = json_decode($response,true);
			if ($body === false && json_last_error() !== JSON_ERROR_NONE) {
			    return "Error parsing response";
			}
			if( isset($body["check"]) && $body["check"] == "ok" ){
				return "ok";
			}else{
				if( isset($body["check"]) ){
					return $body["check"];
				}else{
					return "Please choose other purchase.";
				}
			}
		}
	}
}