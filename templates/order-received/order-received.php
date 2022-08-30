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
   
</style>
<div id="qrcodepixcontent" style="display: flex;flex-direction: row;justify-content: flex-start;align-items: center;background-color: #f8f8f8;border-radius: 8px; padding: 1rem;">
  <img id="copy-code" style="cursor:pointer; display: initial;margin-right: 1rem;" class="wcpix-img-copy-code" src="https://gerarqrcodepix.com.br/api/v1?brcode=<?php echo urlencode($code); ?>" alt="QR Code"/>
  <div>
    <p style="font-size: 19px;margin-bottom: 0.5rem;">Pix: <strong>R$ <?php echo $order->get_total(); ?></strong></p>
    <div style="word-wrap: break-word; max-width: 450px;">
      <small>Código de transação</small><br>
      <code style="font-size: 87.5%; color: #e83e8c; word-wrap: break-word;"><?php echo $code; ?></code>
    </div>
  </div>
</div>
<p style="margin-top: 1rem;">Caso já tenha feito o pagamento, verifique se foi confirmado na página de <a href="<?php echo$order->get_view_order_url(); ?>">detalhes do pedido</a></p>


<script type="text/javascript">
    var req = new XMLHttpRequest();
    var lastStatus = "";
    req.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const data = JSON.parse(req.responseText);
            console.log("status update", data.order_status);
            lastStatus = data.order_status;

            if (data.order_status == "processing") {
            const pixQrElement = document.getElementById("qrcodepixcontent");
            pixQrElement.innerHTML = "";
            pixQrElement.innerHTML = "<div><h2>Pagamento recebido</h2><p>Obrigado por comprar em nossa loja. Você pode consultar o andamento de seu pedido pela página do mesmo.</p><a href="<?php echo $order->get_view_order_url(); ?>">Acessar pedido</a></div>";
            }
        }
    };
    setTimeout(() => {
    let pixInterval = setInterval(() => {
        if (lastStatus == "processing") clearInterval(pixInterval); 
        req.open("GET", "<?php echo $storeUrl; ?>/wp-json/wc/v3/infinitepay_order_status?order_id=<?php echo $order->get_id(); ?>", true);
        req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        req.setRequestHeader("Access-Control-Allow-Origin", "*");
        req.send(null); }, 10000);
    }, 1000);
</script>