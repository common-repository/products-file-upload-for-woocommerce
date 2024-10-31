<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Superaddons_Products_Uploads_Frontend {
    function __construct(){
        add_action( "wp_enqueue_scripts",array($this,"add_lib"));
        add_action( "woocommerce_before_add_to_cart_button",array($this,"add_upload"));
        add_action( 'wp_ajax_superaddons_products_uploads', array($this,'superaddons_products_uploads') );
        add_action( 'wp_ajax_nopriv_superaddons_products_uploads', array($this,'superaddons_products_uploads') );
        add_action( 'wp_ajax_woo_superaddons_products_uploads_remove', array($this,'superaddons_products_uploads_remove') );
        add_action( 'wp_ajax_nopriv_superaddons_products_uploads_remove', array($this,'superaddons_products_uploads_remove') );
        add_action( 'woocommerce_add_to_cart', array($this,'add_to_cart_min'),10,2);
        add_action( 'admin_init', array($this,'add_manage_site_meta_box'), 1 );
        add_action( 'save_post', array($this,'save_meta_box'));
        add_filter( 'woocommerce_add_cart_item_data', array($this,'save_files_product_field'), 10, 2 );
        add_filter( 'woocommerce_get_cart_item_from_session', array($this,"wdm_get_cart_items_from_session"), 1, 3 );
        add_action( 'woocommerce_add_order_item_meta',array($this,'wdm_add_values_to_order_item_meta'),1,2);
        add_filter( 'woocommerce_get_item_data',array($this,'filter_woocommerce_get_item_data'),10,2);
        add_filter( 'woocommerce_order_item_display_meta_key',array($this,'woocommerce_order_item_display_meta_key'),10);
        add_filter( 'woocommerce_order_item_display_meta_value',array($this,'woocommerce_order_item_display_meta_value'),10,2);
        add_action( 'woocommerce_order_item_meta_end',array($this,'woocommerce_order_item_meta_end'),10,3);
    }
    function woocommerce_order_item_meta_end($item_id, $item, $order ) {
        $links = wc_get_order_item_meta( $item_id, '_woo_products_upload_files', true );
        if($links != ""){
            $datas = explode("|",$links);
            $datas_text = array();
            $upload_datas = get_option("superaddons_products_uploads",array("required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
            foreach( $datas as $link ){
                $names = explode("/",$link);
                $datas_text[] = '<a href="'.esc_url($link).'" download>'.end($names).'</a>';
            }
            ?>
            <dl class="variation">
                <dt class="variation-FileUpload"><?php echo esc_attr($upload_datas["label"]) ?>:</dt>
                <dd class="variation-FileUpload"><?php echo wp_kses_post(implode(", ",$datas_text)) ?></dd>
            </dl>
            <?php
        } 
    }
    function woocommerce_order_item_display_meta_key($meta_key ) {
        if($meta_key == "_woo_products_upload_files"){
            $upload_datas = get_option("superaddons_products_uploads",array("required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
            $meta_key = $upload_datas["label"];
        }
        return $meta_key;
    }
    function woocommerce_order_item_display_meta_value($meta_value, $meta ) {
        $datas = explode("|",$meta_value);
        $check = false;
        $datas_text = array();
        foreach( $datas as $link ){
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $check = true;
                $names = explode("/",$link);
                $datas_text[] = '<a href="'.esc_url($link).'" download>'.end($names).'</a>';
            }else{
                $check = false;
                break;
            }
        }
        if($check){
           $meta_value = implode(", ",$datas_text);
        }
        return $meta_value;
    }
    function filter_woocommerce_get_item_data($item_data, $cart_item ) {
        if(isset($cart_item["_woo_products_upload_files"]) && $cart_item["_woo_products_upload_files"] != ""){
            $item_datas = explode("|",$cart_item["_woo_products_upload_files"]);
            $upload_datas = get_option("superaddons_products_uploads",array("required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
            $datas_text = array();
            foreach ( $item_datas as $data ) :
                $names = explode("/",$data);
                $datas_text[] = '<a href="'.esc_url($data).'" download>'.end($names).'</a>';
            endforeach; 
            $item_data[] = array("key"=>$upload_datas["label"],"value"=>implode(", ",$datas_text));
        }
        return $item_data;
    }
    function save_files_product_field($cart_item_data, $product_id){
        global $woocommerce;
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'checkout_file_upload_nonce' ] ) ), 'checkout_file_upload' ) ) {
            $new_value = array();
            if( isset($_POST["woo_products_upload_files"])){
                $new_value['_woo_products_upload_files'] = sanitize_text_field($_POST["woo_products_upload_files"]);
                if(empty($cart_item_data)) {
                    return $new_value;
                } else {
                    return array_merge($cart_item_data, $new_value);
                }
            }
        }
        
        return $cart_item_data;
    }
    function wdm_get_cart_items_from_session($item,$values,$key) {
        if (array_key_exists( '_woo_products_upload_files', $values ) ) {
            $item['_woo_products_upload_files'] = $values['_woo_products_upload_files'];
        }
        return $item;
    }
    function wdm_add_values_to_order_item_meta($item_id, $values) {
        global $woocommerce,$wpdb;
        wc_add_order_item_meta($item_id,'_woo_products_upload_files',$values['_woo_products_upload_files']);
    }
    function add_lib(){
            wp_enqueue_style( 'superaddons_products_uploads', SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_URL."assets/css/drap_drop_file_upload.css" );
            wp_enqueue_script( 'superaddons_products_uploads', SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_URL."assets/js/drap_drop_file_upload.js",array("jquery"),time() );
            wp_localize_script('superaddons_products_uploads','superaddons_products_uploads',array('nonce' => wp_create_nonce('checkout_file_upload'),"url_plugin"=>SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_URL,'ajax_url' => admin_url( 'admin-ajax.php' ),"text_maximum"=>__("You can upload maximum:",'products-file-upload-for-woocommerce')));
    }
    function add_manage_site_meta_box(){
        add_meta_box(
            'manage_site_cinemas',
            __('Uploads','products-file-upload-for-woocommerce'),
            array($this,'backend_form_meta'),
            'product',
            'side',
            "core"
        );
    }
    function add_to_cart_min($cart_item_key, $product_id){
        global $woocommerce;
        $upload_datas = get_option("superaddons_products_uploads",array("required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
        $disable = get_post_meta($product_id,"_product_uploads_meta",true);
        if($disable != "yes") {
            if( $upload_datas["required"] == "yes" ){
                $check = true;
                if( !isset($_REQUEST['woo_products_upload_files']) ) {
                    $check = false;
                }else{
                    if( $_REQUEST['woo_products_upload_files'] == "" ){
                        $check = false;
                    }
                }
                if(!$check){
                    wc_add_notice( $upload_datas["label"].' is a required field', 'error' );
                    throw new Exception( );
                }
            }
        }
    }
    function backend_form_meta($post){
        wp_nonce_field( plugin_basename( __FILE__ ), 'single_uploads_meta_nonce' );
        $disable = get_post_meta($post->ID, "_product_uploads_meta", true);
        ?>
        <div class="tagsdiv1" >
            <div class="jaxtag1">
                <div class="ajaxtag1">    
                    <select name="product_uploads_meta_disable">
                        <option value=""><?php esc_attr_e( "Default",'products-file-upload-for-woocommerce') ?></option>
                        <option <?php selected( "yes", $disable ) ?> value="yes"><?php esc_attr_e( "Show upload field",'products-file-upload-for-woocommerce') ?></option>
                        <option value="no" <?php selected( "no", $disable ) ?>><?php esc_attr_e( "Disable Upload field",'products-file-upload-for-woocommerce') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
        }
    function save_meta_box($post_id){
        global $wpdb;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( !wp_verify_nonce( @$_POST['single_uploads_meta_nonce'], plugin_basename( __FILE__ ) ) )
            return;
        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        }
        else{
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
        }
        $sing_uploads_meta = sanitize_text_field( $_POST["product_uploads_meta_disable"]);
        add_post_meta($post_id, '_product_uploads_meta', $sing_uploads_meta,true) or update_post_meta($post_id, '_product_uploads_meta', $sing_uploads_meta);
    }
    function add_upload(){
        global $post;
        $upload_datas = get_option("superaddons_products_uploads",array("enable"=>"yes","required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
        $product_setting = get_post_meta($post->ID, "_product_uploads_meta", true);
        if( isset($upload_datas["enable"]) && $upload_datas["enable"] == "yes" ){
            $show = true;
        }else{
            $show = false;
        }
        if($product_setting =="yes"){
            $show = true;
        }if($product_setting =="no"){
            $show = false;
        }
        if( $show) :
            ?>
            <div class="clear"></div><!-- /.clear -->
            <div class="upload-lb">
                <Label><?php echo esc_html( $upload_datas["label"]) ?></Label>
            </div>
            <div class="products-uploads-dragandrophandler-container">
                <div class="products-uploads-dragandrophandler" data-max="<?php echo esc_attr( $upload_datas["max_files"] ) ?>" >
                    <div class="products-uploads-dragandrophandler-inner">
                        <div class="products-uploads-text-drop"><?php echo esc_html( $upload_datas["translation1"]  ) ?></div>
                        <div class="products-uploads-text-or"><?php echo esc_html( $upload_datas["translation2"]  ) ?></div>
                        <div class="products-uploads-text-browser"><a href="#"><?php echo esc_html( $upload_datas["translation3"]  ) ?></a></div>
                    </div>
                    <input type="file" class="input-uploads hidden" multiple="">
                </div>
            </div><!-- /.cf7-dragandrophandler-container -->
            <input type="hidden" name="woo_products_upload_files" id="woo_products_upload_files" class="wpcf7-form-control products-uploads-drop-upload">
            <div class="clear"></div><!-- /.clear -->
            <?php
            wp_nonce_field( 'checkout_file_upload','checkout_file_upload_nonce');
        endif;
    }
    function superaddons_products_uploads_remove(){
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'nonce' ] ) ), 'checkout_file_upload' ) ) {
			$name = sanitize_text_field( $_POST["name"] );
			$names = explode("/",$name);
			$path = $this->get_upload_dir();
			$path_main = $path . '/'.end($names);
			if ( @is_readable( $path_main ) && @is_file( $path_main ) ) { 
				@unlink($path_main);
				wp_send_json( array("status"=>"ok" ) );
			}else{
				wp_send_json( array("status"=>"error" ) );
			}
		}
		die();
    }
    private function get_blacklist_file_ext() {
		static $blacklist = false;
		if ( ! $blacklist ) {
			$blacklist = [
				'php',
				'php3',
				'php4',
				'php5',
				'php6',
				'phps',
				'php7',
				'phtml',
				'shtml',
				'pht',
				'swf',
				'html',
				'asp',
				'aspx',
				'cmd',
				'csh',
				'bat',
				'htm',
				'hta',
				'jar',
				'exe',
				'com',
				'js',
				'lnk',
				'htaccess',
				'htpasswd',
				'phtml',
				'ps1',
				'ps2',
				'py',
				'rb',
				'tmp',
				'cgi',
				'svg',
				'php2',
				'phtm',
				'phar',
				'hphp',
				'phpt',
				'svgz',
			];
			$blacklist = apply_filters( 'woocommerce/products/uploads/filetypes/blacklist', $blacklist );
		}
		return $blacklist;
	}
    private function get_upload_dir() {
		$wp_upload_dir = wp_upload_dir();
		$path = $wp_upload_dir['basedir'] . '/woocommerce/products/uploads/';
		$path = apply_filters( 'woocommerce/products/uploads/upload_path', $path );
		return $path;
	}
    private function get_file_url( $file_name ) {
		$wp_upload_dir = wp_upload_dir();
		$url = $wp_upload_dir['baseurl'] . '/woocommerce/products/uploads/' . $file_name;
		$url = apply_filters( 'woocommerce/products/uploads/upload_url', $url, $file_name );
		return $url;
	}
    private function get_ensure_upload_dir() {
		$path = $this->get_upload_dir();
		if ( file_exists( $path . '/index.php' ) ) {
			return $path;
		}
		wp_mkdir_p( $path );
		$files = [
			[
				'file' => 'index.php',
				'content' => [
					'<?php',
					'// Silence is golden.',
				],
			],
			[
				'file' => '.htaccess',
				'content' => [
					'Options -Indexes',
					'<ifModule mod_headers.c>',
					'	<Files *.*>',
					'       Header set Content-Disposition attachment',
					'	</Files>',
					'</IfModule>',
				],
			],
		];
		foreach ( $files as $file ) {
			if ( ! file_exists( trailingslashit( $path ) . $file['file'] ) ) {
				$content = implode( PHP_EOL, $file['content'] );
				@ file_put_contents( trailingslashit( $path ) . $file['file'], $content );
			}
		}
		return $path;
	}
    private function is_file_type_valid( $file_types, $file ) {
		// File type validation
		if ( $file_types == "" )  {
			$file_types = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv';
		}
		$file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$file_types_meta = explode( ',', $file_types );
		$file_types_meta = array_map( 'trim', $file_types_meta );
		$file_types_meta = array_map( 'strtolower', $file_types_meta );
		$file_extension = strtolower( $file_extension );
		return ( in_array( $file_extension, $file_types_meta ) && ! in_array( $file_extension, $this->get_blacklist_file_ext() ) );
	}
    private function is_file_size_valid( $file_sizes, $file ) {
		$allowed_size = ( ! empty( $file_sizes ) ) ? $file_sizes : wp_max_upload_size() / pow( 1024, 2 );
		// File size validation
		$file_size_meta = $allowed_size * pow( 1024, 2 );
		$upload_file_size = $file['size'];
		return ( $upload_file_size < $file_size_meta );
	}
    function superaddons_products_uploads(){
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'nonce' ] ) ), 'checkout_file_upload' ) ) {
			$upload_datas = get_option("superaddons_products_uploads",array("required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
            $file = @$_FILES["file"];
            $size = $upload_datas["max_size"];
            $type = $upload_datas["file_type"];
            $file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$filename = uniqid() . '.' . $file_extension;
            $uploads_dir = $this->get_ensure_upload_dir();
			$filename = wp_unique_filename( $uploads_dir, $filename );
			$new_file = trailingslashit( $uploads_dir ) . $filename;
            if(!$this->is_file_type_valid($type,$file)){
				wp_send_json( array("status"=>"not","text"=>esc_html__( 'This file type is not allowed.', 'products-file-upload-for-woocommerce') ) );
				die();
			}
            // allowed file size?
			if ( ! $this->is_file_size_valid( $size, $file ) ) {
				wp_send_json( array("status"=>"not","text"=>esc_html__( 'This file exceeds the maximum allowed size.', 'products-file-upload-for-woocommerce') ) );
				die();
			}
            if ( is_dir( $uploads_dir ) && is_writable( $uploads_dir ) ) {
				$move_new_file = @ move_uploaded_file( $file['tmp_name'], $new_file );
				if ( false !== $move_new_file ) {
					// Set correct file permissions.
					$perms = 0644;
					@ chmod( $new_file, $perms );
					wp_send_json( array("status"=>"ok","text"=>$this->get_file_url( $filename ) ) );
				} else {
					wp_send_json( array("status"=>"not","text"=>esc_html__( 'There was an error while trying to upload your file.', 'products-file-upload-for-woocommerce') ) );
				}
			} else {
				wp_send_json( array("status"=>"not","text"=>esc_html__( 'Upload directory is not writable or does not exist.', 'products-file-upload-for-woocommerce') ) );
			}  
        }
    }
}
new Superaddons_Products_Uploads_Frontend;