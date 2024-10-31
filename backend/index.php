<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Superaddon_Settings_Tab_Products_Upload {
    function __construct(){
        add_filter( 'woocommerce_settings_tabs_array',array($this,"add_settings_tab"), 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab_products_upload', array($this,"get_settings") );
        add_action( 'woocommerce_update_options_settings_tab_products_upload', array($this,"update_settings") );
    }
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_products_upload'] = __( 'Products uploads', 'products-file-upload-for-woocommerce');
        return $settings_tabs;
    }
    public function get_upload_file_size_options() {
		$max_file_size = wp_max_upload_size() / pow( 1024, 2 ); //MB
		$sizes = [];
        $sizes[] = "";
		for ( $file_size = 1; $file_size <= $max_file_size; $file_size++ ) {
			$sizes[ $file_size ] = $file_size . 'MB';
		}
		return $sizes;
	}
    public function get_settings() {
        $upload_datas = get_option("superaddons_products_uploads",array("enable"=>"yes","required"=>"no","label"=>"File Upload","max_size"=>"","max_files"=>"","file_type"=>"","translation1"=>"Drag & Drop Files Here","translation2"=>"or","translation3"=>"Browse Files"));
        if( isset($upload_datas["required"]) && $upload_datas["required"] == "yes" ){
            $required = "yes";
        }else{
            $required = false;
        }
        if( isset($upload_datas["enable"]) && $upload_datas["enable"] == "yes" ){
            $enable = "yes";
        }else{
            $enable = false;
        }
        ?>
        <h3><?php esc_html_e("Products File Upload Settings",'products-file-upload-for-woocommerce') ?></h3>
        <table class="form-table">
			<tbody>
            <tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Enable",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
						<input <?php checked($enable,"yes") ?> type="checkbox" name="superaddons_products_uploads[enable]" value="yes">
					</td>
				</tr>
				<tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Required",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
							<input <?php checked($required,"yes") ?> type="checkbox" name="superaddons_products_uploads[required]" value="yes">
					</td>
				</tr>
				<tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Label",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
						<input type="text" class="regular-text" name="superaddons_products_uploads[label]" value="<?php echo esc_attr( $upload_datas["label"] ) ?>">
					</td>
				</tr>
                <tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Max size uploads",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
                        <select class="regular-text" name="superaddons_products_uploads[max_size]">
                            <?php
                            $sizes = $this->get_upload_file_size_options();
                            foreach( $sizes as $key => $size ){
                                ?>
                                <option <?php selected( $upload_datas["max_size"],$key) ?> value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $size  ) ?></option>
                                <?php
                            }
                            ?>
                        </select>
					</td>
				</tr>
                <tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Max files",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
						<input type="text" class="regular-text" name="superaddons_products_uploads[max_files]" value="<?php echo esc_attr( $upload_datas["max_files"] ) ?>">
					</td>
				</tr>
                <tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("File type",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
						<input type="text" class="regular-text" name="superaddons_products_uploads[file_type]" value="<?php echo esc_attr( $upload_datas["file_type"] ) ?>">
                        <p class="description"><?php esc_attr_e( 'Default', 'products-file-upload-for-woocommerce') ?>: jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv</p>
					</td>
				</tr>
                <tr valign="top" class="">
					<th scope="row" class="titledesc"><?php esc_html_e("Translation",'products-file-upload-for-woocommerce') ?></th>
					<td class="">
						<input type="text" class="regular-text" name="superaddons_products_uploads[translation1]" value="<?php echo esc_attr( $upload_datas["translation1"] ) ?>">
                        <input type="text" class="regular-text" name="superaddons_products_uploads[translation2]" value="<?php echo esc_attr( $upload_datas["translation2"] ) ?>">
                        <input type="text" class="regular-text" name="superaddons_products_uploads[translation3]" value="<?php echo esc_attr( $upload_datas["translation3"] ) ?>">
					</td>
				</tr>
		</tbody>
	</table>
    <?php
    wp_nonce_field( 'products_file_upload','products_file_upload_nonce');
    }
    function update_settings() {
        $templates = array();
        $datas = array();
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'products_file_upload_nonce' ] ) ), 'products_file_upload' ) ) {
            if( isset($_POST["superaddons_products_uploads"]) ) {
                foreach( $_POST['superaddons_products_uploads'] as $key => $value ){
                    $datas[sanitize_text_field($key)] = sanitize_text_field($value);
                }
            }
            update_option("superaddons_products_uploads",$datas);
        }
}
}
new Superaddon_Settings_Tab_Products_Upload;