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
	if ( $i === 1 ) {
		$installments .= '<option value="1">R$ ' . esc_attr( number_format( $installments_value[ $i - 1 ]['value'], 2, ",", "." ) ) . ' à vista</option>';
	} else {
		$new_value    = round( $installments_value[ $i - 1 ]['value'], 2, PHP_ROUND_HALF_UP );
		$has_interest = $installments_value[ $i - 1 ]['interest'] ? 'com' : 'sem';
		$installments .= '<option value="' . esc_attr( $i ) . '">' . esc_attr( $i ) . 'x de R$ ' . esc_attr( number_format( $new_value, 2, ",", "." ) ) . ' ' . esc_attr( $has_interest ) . ' juros</option>';
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
</style>
<fieldset id="wc-<?php echo esc_attr( $id ) ?>-cc-form" class="wc-credit-card-form wc-payment-form"
          style="background:transparent;">
    <div id="infinitepay-form">
        <div class="form-row form-row-wide">
            <label for="ip_ccNo">Número do Cartão <span class="required">*</span></label>
            <input id="ip_ccNo" onkeyup="ipCreditMaskDate(this, ipMcc);" type="text" autocomplete="off"
                   data-checkout="cardNumber" maxlength="19" class="input-text">
            <span id="ip-error-1" class="ip-error" data-main="#ip_ccNo">Número do cartão inválido</span>
        </div>
        <div class="form-row form-row-wide">
            <label for="ip_expdate">Data de validade <span class="required">*</span></label>
            <input id="ip_expdate" onkeyup="ipCreditMaskDate(this, ipDate);" onblur="ipValidateMonthYear()" type="text"
                   autocomplete="off" placeholder="MM/AA" data-checkout="cardExpirationDate" maxlength="5"
                   class="input-text">
            <span id="ip-error-2" class="ip-error" data-main="#ip_expdate">Data inválida</span>
            <input type="hidden" id="cardExpirationMonth" data-checkout="cardExpirationMonth">
            <input type="hidden" id="cardExpirationYear" data-checkout="cardExpirationYear">
        </div>
        <div class="form-row form-row-wide">
            <label for="ip_cvv">CVV<span class="required">*</span></label>
            <input id="ip_cvv" onkeyup="ipCreditMaskDate(this, ipInteger);" type="text" autocomplete="off"
                   placeholder="CVV" data-checkout="cardSecurityCode" maxlength="4" class="input-text">
            <span id="ip-error-3" class="ip-error" data-main="#ip_cvv">CVV inválido</span>
        </div>
        <div class="form-row form-row-wide">
            <label for="ip_installments">Número de parcelas <span class="required">*</span></label>
            <select id="ip_installments" name="infinitepay_custom[installments]"
                    data-checkout="installments"
            ><?php echo wp_kses( $installments, array( 'option' => array( 'value' => array() ) ) ) ?></select>
            <span id="ip-error-4" class="ip-error" data-main="#ip_installments">Selecione o número de parcelas</span>
        </div>
        <div class="form-row form-row-wide">
            <label for="ip_docNumber">CPF do portador do cartão <span class="required">*</span></label>
            <input id="ip_docNumber" onkeyup="ipCreditMaskDate(this, ipDoc);" type="text" autocomplete="off"
                   data-checkout="docNumber" name="infinitepay_custom[doc_number]" maxlength="14" class="input-text">
            <span id="ip-error-5" class="ip-error" data-main="#ip_docNumber">Documento inválido</span>
        </div>
        <input type="hidden" id="ip-token" name="infinitepay_custom[token]"/>
        <input type="hidden" id="ip-uuid" name="infinitepay_custom[uuid]"/>
    </div>
    <div class="clear"></div>
    <div class="clear"></div>
</fieldset>

<script type="text/javascript">
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