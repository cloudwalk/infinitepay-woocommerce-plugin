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
.pix-label {
  width: 100%;
  margin-top: 1rem;
}
.pix-label img {
  width: 100%;
  object-fit: cover;
}
</style>

<fieldset id="wc-<?php echo esc_attr( $id ) ?>-pix-form" class="wc-pix-form wc-payment-form" style="background:transparent;">
  <div class="pix-label">
    <img src="https://confere-pix.web.app/pix.png" alt="InfinitePay Label" />
  </div>
</fieldset>

<script type="text/javascript">
  console.log('[pix loaded]')
</script>