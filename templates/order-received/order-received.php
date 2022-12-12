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
   #qrcodepixcontent {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    background-color: #f8f8f8;
    border-radius: 8px;
    padding: 1rem;
   }

   @media only screen and (max-width: 600px) {
    #qrcodepixcontent {
      display:block;
    }
}
</style>
<div id="qrcodepixcontent">
  <img id="copy-code" style="cursor:pointer; display: initial;margin-right: 1rem;" class="wcpix-img-copy-code" src="https://gerarqrcodepix.com.br/api/v1?brcode=<?php echo urlencode($code); ?>" alt="QR Code"/>
  <div>
    <p style="font-size: 19px;margin-bottom: 0.5rem;">Pix: <strong>R$ <?php echo number_format( $order->get_total(), 2, ',', '.'); ?></strong></p>
    <div style="word-wrap: break-word; max-width: 450px;">
      <small>Código de transação</small><br>
      <code id="pixcodestr" style="font-size: 87.5%; color: #e83e8c; word-wrap: break-word;"><?php echo esc_html($code); ?></code>
      <br />
      <input type="text" id="pixcode" style="display:none;">
      <button onclick="copypix()">Clique aqui para copiar</button>
    </div>
  </div>
</div>
<p style="margin-top: 1rem;">Caso já tenha feito o pagamento, verifique se foi confirmado na página de <a href="<?php echo$order->get_view_order_url(); ?>">detalhes do pedido</a></p>

<script type="text/javascript">

    document.getElementById('pixcode').value = document.getElementById('pixcodestr').innerHTML;
  
    function copypix() {
      var copyText = document.getElementById("pixcode");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      console.log(copyText.value);
      navigator.clipboard.writeText(copyText.value);
    }
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
            pixQrElement.innerHTML = '<div><h2>Pagamento recebido</h2><p>Obrigado por comprar em nossa loja. Você pode consultar o andamento de seu pedido pela página do mesmo.</p><a href="<?php echo $order->get_view_order_url(); ?>">Acessar pedido</a></div>';
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