<?php 

/**
* 
*/
class WC_Gateway_mavitm_mail_order extends WC_Payment_Gateway {

	public 	$Lang = "tr";
	public 	$notify_url;
	public 	$form_ust_mesaj,
			$MailorderEncrpyKey,
			$MetaKeyKey,
			$formDescription;

	function __construct(){
		$this->id 					= "mavitm_mail_order";
		$this->method_title 		= __( "Mail Order", 'mavitm_mail_order' );
		$this->method_description 	= __( "WooCommerce mail order formu", 'mavitm_mail_order' );
		$this->notify_url 			= WC()->api_request_url( 'WC_Gateway_mavitm_mail_order' );

		$this->icon = NULL;
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();

		$this->MailorderEncrpyKey 		= $this->get_option('MailorderEncrpyKey');
		$this->form_ust_mesaj 			= $this->get_option('form_ust_mesaj');
		$this->description 				= $this->get_option( 'description' );
		$this->MetaKeyKey 				= $this->get_option( 'MetaKeyKey' );
		$this->title 					= $this->get_option( 'orderTitle' );
		$this->formDescription			= $this->get_option( 'formDescription' );

		if(empty($this->title)){
			$this->title 				= __( "Kredi Kartı Güvenli Tahsilat Sistemi", 'mavitm_mail_order' );
		}

		if(empty($this->formDescription)){
			$this->formDescription 				= '<h2 class="product_title entry-title">Kredi Kartı Güvenli Tahsilat Sistemi Nedir?</h2><p>
Kredi Kartı Güvenli Tahsilat Sistemi, '.get_bloginfo( 'name' ).' üzerinden yapmış olduğunuz rezervasyonların güvenlik kademelerinden geçirilerek tahsilatının yapılmasıdır.</p>
<p><strong>Bu güvenlik kademelerinde sırası ile</strong></p>
<p>*Rezervasyonunuz ön incelemeye alınır.</p>
<p>*Anlaşmalı bankaların tahsilat sistemleri üzerinden rezervasyonunuzun tahsilatı yapılır.</p>
<p>*Hizmet alıcının doğabilecek itirazlarına karşın, tahsilat tutarı banka üzerinde ki güvenlik havuzuna aktarılır.</p>
<p>*10 Günlük süre içerisinde rezervasyon itirazı oluşmaz ise tahsilat yapılır.</p>
<p>*10 Günlük süre içerisinde rezervasyon veya hizmet alımı itirazı yaşanır ise, '.get_bloginfo( 'name' ).' İPTAL İADE SÖZLEŞMESİ kapsamında tur ücretiniz iade edilir.
</p>';
		}

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action(	'woocommerce_receipt_mavitm_mail_order', array( $this, 'receipt_page' ));
		add_action( 'MailOrderSubmitCallback', array( $this, 'gelen_veri' ) );
		add_action( 'woocommerce_api_wc_gateway_mavitm_mail_order', array( $this, 'handle_callback' ));
	}
	

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Mail order formu etkin', 'mavitm_mail_order' ),
				'default' => 'yes'
			),
			'MailorderEncrpyKey' 	=> array(
				'title' 			=> __( 'Şifreleme için key', 'mavitm_mail_order' ),
				'type' 				=> 'text',
				'description' 		=> __( 'Bilgilerin veri tabanında saklanması için anahtar', 'mavitm_mail_order' ),
				'default' 			=> __( 'ayer50@gmail.com', 'mavitm_mail_order' ),
				'desc_tip'      	=> true,
			),
			'form_ust_mesaj' 	=> array(
				'title' 			=> __( 'Form üst mesaj', 'mavitm_mail_order' ),
				'type' 				=> 'textarea',
				'description' 		=> __( 'Mail order formunun üzerinde olacak açıklama.', 'mavitm_mail_order' ),
				'default' 			=> __( 'Siparişiniz için teşekkür ederiz. Kartınızdan ücret daha sonra tarafımızdan çekilecektir.', 'mavitm_mail_order' ),
				'desc_tip'      	=> true,
			),
			'orderTitle' 	=> array(
				'title' 			=> __( 'Ödeme sistemi adı', 'mavitm_mail_order' ),
				'type' 				=> 'text',
				'description' 		=> __( 'Ödeme seçim ekranında ve form ekranında ödeme tipi olarak görüntülenir.', 'mavitm_mail_order' ),
				'default' 			=> __( 'Kredi Kartı Güvenli Tahsilat Sistemi', 'mavitm_mail_order' ),
				'desc_tip'      	=> true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'desc_tip'    => true,
				'description' => __( 'Ödeme seçimi sırasında ziyaretçilerinizin göreceği mesaj', 'mavitm_mail_order' ),
				'default'     => __( 'Kredi kartı bilgilerinizi güvenli bir şekilde bize iletmenizi sağlar .', 'mavitm_mail_order' )
			),

			'formDescription' => array(
				'title'       => __( 'Form bilgisi', 'woocommerce' ),
				'type'        => 'textarea',
				'desc_tip'    => true,
				'description' => __( 'Ziyaretçileriniz için info metni', 'mavitm_mail_order' ),
				'default'     => ''
			),

			'MetaKeyKey' 	=> array(
				'title' 			=> __( 'Veri tabanı kayıt indexi', 'mavitm_mail_order' ),
				'type' 				=> 'text',
				'description' 		=> __( 'Bilgilerin veri tabanında saklanacağı yerin adı, Sipariş sayfasında özel alanlar bölümünde oluşturduğunuz alanın isim değeri.', 'mavitm_mail_order' ),
				'default' 			=> __( 'MaviTmOrderData', 'mavitm_mail_order' ),
				'desc_tip'      	=> true,
			),
		);
	}

	public function receipt_page($order){
		echo '<div class="alert alert-info" role="alert">'.$this->form_ust_mesaj.'</div>';
		echo $this->veri_getir($order);
	}

	public function veri_getir( $order_id ) {
		global $woocommerce;
		$mus_siparis = new WC_Order($order_id);

		$html_form    =
		'<form action="'.$this->notify_url.'" method="post" id="mavitm_mail_order_payment_form">';

		$html_form .= '<div class="row">';

			$html_form .= '<div class="col-md-6">';
			$html_form .= $this->formDescription;
			$html_form .= '</div>';

			$html_form .= '<div class="col-md-6">';

		$html_form .= '<div class="form-group">';
		$html_form .= '<label>'.__('Kart Üzerindeki İsim', 'mavitm_mail_order').'</label>';
		$html_form .= '<input type="text" name="ccName" placeholder="'.__('İsim soyisim', 'mavitm_mail_order').'" class="form-control" required="required" autocomplete="off" />';
		$html_form .= '</div>';

		$html_form .= '<div class="form-group">';
		$html_form .= '<label>'.__('Kart Numarası', 'mavitm_mail_order').'</label>';
		$html_form .= '<input type="text" name="ccNo" class="form-control" maxlength="20" required="required" autocomplete="off" />';
		$html_form .= '</div>';

		$html_form .= '<div class="form-group">';
		$html_form .= '<label>'.__('Son Kullanma Tarihi', 'mavitm_mail_order').'</label>';
		$html_form .= '<div class="row">';
		$html_form .= '<div class="col-md-6">';
		$html_form .= '<select name="gecerAy" class="form-control" required="required">
							<option value="0">'.__('Ay', 'mavitm_mail_order').'</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>';
		$html_form .= '</div>';
		$html_form .= '<div class="col-md-6">';
		$html_form .= '<select name="gecerYil" class="form-control" required="required">
							<option value="0">'.__('Yıl', 'mavitm_mail_order').'</option>';
							$t = date('Y');
							$g = (date('Y') + 12);
							for($i = $t; $i <= $g; $i++){
								$html_form .= '<option value="'.$i.'">'.$i.'</option>';
							}
		$html_form .= '</select>';
		$html_form .= '</div>';
		$html_form .= '</div>';
		$html_form .= '</div>';


		$html_form .= '<div class="form-group">';
		$html_form .= '<label>'.__('Güvenlik Kodu', 'mavitm_mail_order').'</label>';
		$html_form .= '<input type="text" name="cvc" class="form-control" maxlength="4" required="required" autocomplete="off" />';
		$html_form .= '</div>';

		$html_form .= '<div class="form-group">';
		$html_form .= '<label>'.__('Kart Tipi', 'mavitm_mail_order').'</label>';
		$html_form .= '<select name="kartTipi" class="form-control">
							<option value="MASTERCARD">MASTERCARD</option>
							<option value="VISA">VISA</option>
						</select>';
		$html_form .= '</div>';


			$html_form .= '</div>';

		$html_form .= '</div>';

		$html_form .= '<input type="hidden" name="orderID" value="'.$order_id.'" />';
		$html_form .='<a class="btn btn-danger cancel" href="'.$mus_siparis->get_cancel_order_url().'">
		<i class="glyphicon glyphicon-chevron-left"></i>'.__('Vazgeç ve sepete geri dön', 'mavitm_mail_order').'</a>';
		$html_form .='<button type="submit" class="btn btn-succuess succuess pull-right" id="submit_mavitm_mail_order_payment_form">
		<i class="glyphicon glyphicon-floppy-saved"></i>
		'.__('Siparişimi tamamla', 'mavitm_mail_order').'</button>';

		$html_form .='</form>';
		return $html_form;
	}

	public function handle_callback(){
		@ob_clean();
		if($_POST){
			$callback = stripslashes_deep($_POST);
			do_action( "MailOrderSubmitCallback", $callback );
		}else{
			wp_die( "Bilgileriniz kayıt edilemedi. Lütfen site yöneticileriyle iletişime geçin.", "Form submit Callback", array( 'response' => 200 ) );
		}
	}

	public function gelen_veri($post)
	{
		global $woocommerce;

		$post = array_map(function($p){
			$p = strip_tags($p);
			return str_replace(
				array("\"","'","<",">","*","+","(",")","[","]","!","="),
				"",
				$p
			);
		}, $_POST);


		$order_id 	= intval($post['orderID']);
		$a['ccName'] 	= $post['ccName'];
		$a['ccNo'] 		= $post['ccNo'];
		$a['cvc']		= $post['cvc'];
		$a['gecerAy'] 	= $post['gecerAy'];
		$a['gecerYil'] 	= $post['gecerYil'];
		$a['kartTipi'] 	= $post['kartTipi'];

		if($order_id < 1){
			$geri = '<a href="'.$woocommerce->cart->get_checkout_url().'" role="button">« '.__('Sipariş sayfasına geri dön', 'mavitm_mail_order').'</a>';
			wp_die(__('Şiparişiniz ile ilgili bazı bilgiler eksikti.', 'mavitm_mail_order').' 1#<br>'.$geri, 'HATA!', array('response' => 200));
			return false;
		}

		$order = wc_get_order($order_id);

		if($this->_kartBilgisiEkle($order_id, $a)){
			$order->update_status('on-hold', __( 'Mail order kayıt işlemi başarılı', 'mavitm_mail_order' ));
			$woocommerce->cart->empty_cart();
			$order->payment_complete();
			wp_redirect($this->get_return_url($order));
			exit();
		}else{
			$geri = '<a href="'.$woocommerce->cart->get_checkout_url().'" role="button">« '.__('Sipariş sayfasına geri dön', 'mavitm_mail_order').'</a>';
			wp_die(__('Şiparişiniz ile ilgili bazı bilgiler eksikti.', 'mavitm_mail_order').' 2#<br>'.$geri, 'HATA!', array('response' => 200));
			return false;
		}

	}

	private function _kartBilgisiEkle($orderID, Array $infoArr){
		$str = $this->jsonEncode($infoArr);
		$str = $this->encrypt($str, $this->MailorderEncrpyKey);
		return add_post_meta($orderID, $this->MetaKeyKey, $str);
	}

	public function kartBilgisiGoster($orderID)
	{
		if(!is_admin()){
			return false;
		}

		$data 		= get_post_meta( $orderID, $this->MetaKeyKey, true );
		if(empty($data)){
			return false;
		}

		$strData 	= $this->decrypt($data, $this->MailorderEncrpyKey);
		$strData 	= explode('Name":', $strData);
		$strData 	= '{"Name":'.trim($strData[1]);
		$strData	= str_replace("\n","",$strData);
		$arrData	= $this->jsonDecode($strData);

		//print_r($arrData);

		if(!is_array($arrData)){
			return false;
		}

		echo '<br><br><div class="cleafix"></div>';
		echo '<div class="order_data_column form-field-wide">
				<h4>'.__( 'Mail order info', 'mavitm_mail_order' ).'</h4>';
		echo '<p><strong>'.__('İsim Soyisim', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['ccName'].' '.$arrData['Name'].'</span></p>';
		echo '<p><strong>'.__('Kart no', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['ccNo'].'</span></p>';
		echo '<p><strong>'.__('Güvenlik kodu', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['cvc'].'</span></p>';
		echo '<p><strong>'.__('Son kullanım ay', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['gecerAy'].'</span></p>';
		echo '<p><strong>'.__('Son kullanım yıl', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['gecerYil'].'</span></p>';
		echo '<p><strong>'.__('Kart tipi', 'mavitm_mail_order').'</strong>: <span style="float:right;">'.$arrData['kartTipi'].'</span></p>';
		echo '</div>';
	}
	public function arrayMapRecursive($arr, $call){
		if(is_array($arr)){
			foreach ($arr as $key => $value) {
				if(is_array($value)){
					$arr[$key] = $this->arrayMapRecursive($value, $call);
				}else{
					$arr[$key] = call_user_func($call,$value);
				}
			}
		}
		return $arr;
	}

	public function jsonEncode($arr, $base = true){
		if($base){
			$arr = $this->arrayMapRecursive($arr,"base64_encode"); return json_encode($arr);
		}
		return json_encode($arr);
	}

	public function jsonDecode($string, $base = true){
		$string = stripslashes($string);
		if($base){
			$string = json_decode($string, true);
			return $this->arrayMapRecursive($string,"base64_decode");
		}
		return json_decode($string,true);
	}

	public function hex_decode($x, $split = 2) {
		$s='';
		foreach(explode("\n",trim(chunk_split($x,$split))) as $h) $s.=chr(hexdec($h));
		return $s;
	}

	public function hex_encode($x, $split = 2) {
		$s='';
		foreach(str_split($x) as $c) $s.=sprintf("%0".$split."X",ord($c));
		return $s;
	}

	protected function encrypt($string, $key) {
		$string = $this->hex_encode(trim($string));
		$alg  = MCRYPT_BLOWFISH;
		$mode = MCRYPT_MODE_CBC;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($alg,$mode), MCRYPT_DEV_URANDOM);
		$encrypted_data = mcrypt_encrypt($alg, $key, $string, $mode, $iv);
		return base64_encode($encrypted_data);
	}

	protected function decrypt($string, $key) {
		$alg  = MCRYPT_BLOWFISH;
		$mode = MCRYPT_MODE_CBC;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($alg,$mode), MCRYPT_DEV_URANDOM);
		return $this->hex_decode(mcrypt_decrypt($alg, $key, base64_decode($string), $mode, $iv));
	}

	public function hardTrim($str){
		$bul = array("\n","\t");
		$sil = array("",  "");
		return preg_replace(
			array('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '/\r\n|\r|\n|\t|\s\s+/'),
			'',
			str_replace($bul,$sil,$str)
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order($order_id);
		
		return array(
			'result'  => 'success',
			'redirect'  => $order->get_checkout_payment_url( true )
			);
	}

}
