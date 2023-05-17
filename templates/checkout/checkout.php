<?php
/*
 * Part of Woo InfinitePay Module
 * Author - InfinitePay
 * Developer
 * Copyright - Copyright(c) CloudWalk [https://www.cloudwalk.io]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 *  @package InfinitePay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$installments = '';
for (
	$i = 1;
	$i <= (int) $max_installments;
	$i ++
) {
	if ( $i == 1 ) {
		$installments .= '<option value="1">R$ ' . esc_attr( number_format( $installments_value[ $i - 1 ]['value'], 2, ",", "." ) ) . ' à vista</option>';
	} else {
		$new_value = round( $installments_value[ $i - 1 ]['value'], 2, PHP_ROUND_HALF_UP );
		if ( $new_value < 1 ) {
			break;
		}

		$has_interest = $installments_value[ $i - 1 ]['interest'] ? 'com' : 'sem'; 
        $total_value = $i * $new_value;
        $has_interest_total = $installments_value[ $i - 1 ]['interest'] ? '(R$ '. esc_attr( number_format($total_value, 2, ",", "." ) ) .')' : '(R$ '. esc_attr( number_format($installments_value[ 0 ]['value'], 2, ",", "." ) ) .')';
		$installments .= '<option value="' . esc_attr( $i ) . '">' . esc_attr( $i ) . 'x de R$ ' . esc_attr( number_format( $new_value, 2, ",", "." ) ) . ' ' . esc_attr( $has_interest ) . ' juros ' . $has_interest_total . '</option>';
	}
}


?>
<style>
    .ip-error {
        display: none;
        color: red;
    }

    .ip-form-control-error {
        border: 1px solid red !important;
    }

    .ip-installments {
        width: 100%;
        height: 40px !important;
        border-radius: 5px !important;
        font-size: 13px !important;
        padding: 0 10px !important;
        background-color: #fff !important;
        border: 1px solid #d1d1d1 !important;
        margin-bottom: 0 !important;
        color: #000 !important;
    }

    .payment_method_infinitepay img {
        max-height: 1.3em !important;
    }

    .button.active {
        border:1px solid #000;
    }
    
    a.noactive {   
        background: #5f5f5f1a !important;     
    }

    .pix-label {
        width: 100%;
        margin-top: 1rem;
    }
    .pix-label img {
        width: 40%;
        float: none !important;
        border: 0; 
        padding: 0; 
        margin-bottom: 2rem;
        max-height: 40% !important;
    }

    .wc-pix-form ul {
        margin:10px 0px !important;
        padding: 0px !important;
    }

    .wc-pix-form ul li {
        margin:15px 0px !important;
        padding: 0px !important;
    }

    .wc-pix-form ul li span{
        background: #5f5f5f59;
        border-radius: 50%;
        width: 30px;
        padding: 5px 0px 0px 12px;
        height: 30px;
        display: inline-block;
    }

    .ipwarning {
        padding: 10px;
        background-color: #ff9800;
        color: #fff;
        margin-bottom: 15px;
    }

</style>
<span class="<?php echo $css_custom; ?>"></span>
<?php if($sandbox_warning) : ?>
<div class="ipwarning">
<?php echo $sandbox_warning; ?>
</div>
<?php endif; ?>

<?php if($enabled_creditcard == 'yes') : ?>
<a href="javascript:;" onClick="<?php echo ($enabled_pix == 'yes') ? "ifchangePaymentMethod('cc-form')" : "void(0)" ?>" id="cc-form" class="button active">
    <?php echo __('Credit Card', 'infinitepay-woocommerce'); ?>
</a>
<?php endif; ?>

<?php if($enabled_pix == 'yes') : ?>
<a href="javascript:;" onClick="<?php echo ($enabled_creditcard == 'yes') ? "ifchangePaymentMethod('pix-form')" : "void(0)" ?>" id="pix-form" class="button noactive">
    PIX
</a>
<?php endif; ?>

<?php if($enabled_creditcard == 'yes' || $enabled_pix == 'yes') : ?>
    <div  id="infinitepay-form">
        <input id="ip_method" name="ip_method" value="<?php echo ($enabled_pix == 'yes' && $enabled_creditcard == 'yes') ? "cc-form" : ($enabled_pix == 'yes' ? 'form-pix' : 'cc-form') ?>" type="hidden">
        <?php if($enabled_creditcard == 'yes') : ?>
            <fieldset id="wc-<?php echo esc_attr( $id ) ?>-cc-form" class="wc-credit-card-form wc-payment-form wc-if-form" style="background:transparent;">                
                    
                <p><?php echo $instructions; ?></p>

                    <div class="form-row form-row-wide">
                        <label for="ip_ccNo">Número do Cartão <span class="required">*</span></label>
                        <input id="ip_ccNo" onkeyup="ipCreditMaskDate(this, ipMcc);" type="tel" data-ip="card-number" autocomplete="off" maxlength="19" class="input-text">
                        <span id="ip-error-1" class="ip-error" data-main="#ip_ccNo">Número do cartão inválido</span>
                    </div>

                    <div class="form-row form-row-first">
                        <label for="cardExpirationMonth">Mês de validade <span class="required">*</span></label>
                        <input id="cardExpirationMonth" data-ip="card-expiration-month" onkeyup="ipCreditMaskDate(this, ipInteger);" type="tel" autocomplete="off" placeholder="MM" maxlength="2" class="input-text">
                        <span id="ip-error-2" class="ip-error" data-main="#ip_expdate">Data inválida</span>
                    </div>

                    <div class="form-row form-row-last">
                        <label for="cardExpirationYear">Ano de validade <span class="required">*</span></label>
                        <input id="cardExpirationYear" data-ip="card-expiration-year" onkeyup="ipCreditMaskDate(this, ipInteger);" type="tel" autocomplete="off" placeholder="AA" maxlength="2" class="input-text">
                    </div>

                    <div class="form-row form-row-wide">
                        <label for="ip_cvv">CVV<span class="required">*</span></label>
                        <input id="ip_cvv" onkeyup="ipCreditMaskDate(this, ipInteger);" type="tel" data-ip="card-cvv" autocomplete="off" placeholder="CVV" name="infinitepay_custom[cvv]" maxlength="4" class="input-text">
                        <span id="ip-error-3" class="ip-error" data-main="#ip_cvv">CVV inválido</span>
                    </div>

                    <div class="form-row form-row-wide">
                        <label for="ip_installments">Número de parcelas <span class="required">*</span></label>
                        <select id="ip_installments" name="infinitepay_custom[installments]"><?php print_r(wp_kses( $installments, array( 'option' => array( 'value' => array() ) ) )) ?></select>
                        <span id="ip-error-4" class="ip-error" data-main="#ip_installments">Selecione o número de parcelas</span>
                    </div>

                    <div class="form-row form-row-wide">
                        <label for="ip_docNumber">CPF do portador do cartão <span class="required">*</span></label>
                        <input id="ip_docNumber" onkeyup="ipCreditMaskDate(this, ipDoc);"  data-ip="card-holder-document" type="tel" autocomplete="off" name="infinitepay_custom[doc_number]" maxlength="14" class="input-text">
                        <span id="ip-error-5" class="ip-error" data-main="#ip_docNumber">Documento inválido</span>
                    </div>
                    <input type="hidden" id="ip-token" name="infinitepay_custom[token]"/>
                    <input type="hidden" id="ip-uuid" name="infinitepay_custom[uuid]"/>
                    <input type="hidden" data-ip="method" value="credit_card">
                    <div class="clear"></div>
                
            </fieldset>
        <?php endif; ?>
        
        <?php if($enabled_pix == 'yes') : ?>
            <fieldset id="wc-<?php echo esc_attr( $id ) ?>-pix-form" class="wc-pix-form wc-payment-form wc-if-form" style="background:transparent;<?php echo ($enabled_creditcard == 'yes') ? "display:none" : "" ?>">
                <p><?php echo $instructions_pix; ?></p>
                <div class="pix-label">
                    <img src="<?php echo $pix_logo; ?>" alt="InfinitePay Label" />
                </div>
                <?php if($pix_value != 0) : ?>
                <p>
                   <strong><?php echo __('Discounted value', 'infinitepay-woocommerce'); ?> (<?php echo $discount_pix; ?>%): R$ <?php echo $pix_value; ?></strong>
                </p>
                <?php endif; ?>
                <ul>
                    <li><span>1</span> <?php echo __('Checkout to display the QRCode', 'infinitepay-woocommerce'); ?></li>
                    <li><span>2</span> <?php echo __('Open your bank app and select the option to pay with PIX/Scan QRCode', 'infinitepay-woocommerce'); ?></li>
                    <li><span>3</span> <?php echo __("Check the data and confirm your payment through your bank's app", 'infinitepay-woocommerce'); ?></li>
                </ul>
            </fieldset>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script type="text/javascript">

    <?php if($enabled_creditcard == 'yes' && $enabled_pix == 'yes') : ?>
    function ifchangePaymentMethod(frame) {
        if(frame == 'cc-form') {
            document.getElementById('wc-<?php echo esc_attr( $id ) ?>-cc-form').style.display = 'block';
            document.getElementById('wc-<?php echo esc_attr( $id ) ?>-pix-form').style.display = 'none';
            document.getElementById('cc-form').classList.add("active");
            document.getElementById('pix-form').classList.add("noactive"); 
            
            document.getElementById('pix-form').classList.remove("active");
            document.getElementById('cc-form').classList.remove("noactive");

        } else {
            document.getElementById('wc-<?php echo esc_attr( $id ) ?>-cc-form').style.display = 'none';
            document.getElementById('wc-<?php echo esc_attr( $id ) ?>-pix-form').style.display = 'block';
            document.getElementById('cc-form').classList.remove("active");
            document.getElementById('pix-form').classList.add("active");
            document.getElementById('cc-form').classList.add("noactive");
            
            document.getElementById('cc-form').classList.remove("active");
            document.getElementById('pix-form').classList.remove("noactive")
            document.getElementById('pix-form').classList.add("active");
        }
        document.getElementById('ip_method').value = frame;
    }
    <?php endif; ?>

    function ipCreditExecmascara() {
        v_obj.value = v_fun(v_obj.value)
    }

    function ipCreditMaskDate(o, f) {
        v_obj = o
        v_fun = f
        setTimeout("ipCreditExecmascara()", 1)
    }

    function ipMcc(value) {
        if (ipIsMobile()) {
            return value
        }
        value = value.replace(/\D/g, "")
        value = value.replace(/^(\d{4})(\d)/g, "$1 $2")
        value = value.replace(/^(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3")
        value = value.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4")
        return value
    }

    function ipDoc(value) {
        if (ipIsMobile()) {
            return value
        }
        value = value.replace(/\D/g, "")
        value = value.replace(/^(\d{3})(\d)/g, "$1.$2")
        value = value.replace(/^(\d{3})\.(\d{3})(\d)/g, "$1.$2.$3")
        value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/g, "$1.$2.$3-$4")
        return value
    }

    function ipDate(v) {
        v = v.replace(/\D/g, "")
        v = v.replace(/(\d{2})(\d)/, "$1/$2")
        v = v.replace(/(\d{2})(\d{2})$/, "$1$2")
        return v
    }

    function ipValidateMonthYear() {
        var date = document.getElementById('ip_expdate').value.split('/')
        document.getElementById('cardExpirationMonth').value = date[0]
        document.getElementById('cardExpirationYear').value = date[1]
    }

    function ipInteger(v) {
        return v.replace(/\D/g, "")
    }

    function ipIsMobile() {
        try {
            document.createEvent("TouchEvent")
            return true
        } catch (e) {
            return false
        }
    }
</script>