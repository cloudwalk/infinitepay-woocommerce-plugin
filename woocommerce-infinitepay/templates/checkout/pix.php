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

?>
<style>
.ip-error {
  display: none;
  color: red;
}

.ip-form-control-error {
  border: 1px solid red !important;
}
</style>

<fieldset id="wc-<?php echo esc_attr( $id ) ?>-pix-form" class="wc-pix-form wc-payment-form" style="background:transparent;">
  <div>
    <h1>PIX</h1>
  </div>
</fieldset>

<script type="text/javascript">
  console.log('[pix loaded]')
</script>