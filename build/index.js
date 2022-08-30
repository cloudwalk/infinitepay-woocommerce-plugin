(function ($) {
    "use strict"

    $(function () {
        let infinite_pay_submit = false;
        let additionalInfo = {};

        function validateInputs() {
            clearErrors()
            const additionalInputs = validateAdditionalInputs()

            if (additionalInputs) {
                return false
            }
            return true
        }

        function validateAdditionalInputs() {
            let hasEmpty = false
            if (additionalInfo.cardholderIdentificationType) {
                const inputDocType = document.getElementById('docType')
                if (inputDocType.value === '-1' || inputDocType === '') {
                    inputDocType.classList.add('ip-form-control-error')
                    hasEmpty = true
                }
            }
            if (additionalInfo.cardholderIdentificationNumber) {
                const inputDocNumber = document.getElementById('docNumber')
                if (inputDocNumber.value === '-1' || inputDocNumber === '') {
                    inputDocNumber.classList.add('ip-form-control-error')
                    document.getElementById('ip-error-1').style.display = 'inline-block'
                    hasEmpty = true
                }
            }
            return hasEmpty
        }

        function clearErrors() {
            for (
                let i = 0;
                i < document.querySelectorAll('[data-checkout]').length;
                i++
            ) {
                const errorElement = document.querySelectorAll('[data-checkout]')[i]
                errorElement.classList.remove('ip-form-control-error')
            }
            for (
                let j = 0;
                j < document.querySelectorAll('.ip-error').length;
                j++
            ) {
                const errorMessage = document.querySelectorAll('.ip-error')[j]
                errorMessage.style.display = 'none'
            }
        }

        function responseHandler() {
            infinite_pay_submit = false;
            let wooCheckoutForm = $('form.woocommerce-checkout');

            
            if(wooCheckoutForm.length == 0) {
                wooCheckoutForm = document.querySelector('form#order_review');
            } else {
                wooCheckoutForm.off('checkout_place_order', infinitePayFormHandler)
            }

            setTimeout(function() {
                wooCheckoutForm.submit();
            }, 600)
        }

        function createToken() {
            clearErrors();
            if(document.getElementById('ip_method').value == 'cc-form') {
                
                var form = document.querySelector('form.woocommerce-checkout');
                if(!form) {
                    form = document.querySelector('form#order_review');
                }
                var ipay = new IPay({ access_token: wc_infinitepay_params.access_token });

                ipay.listeners = {
                    "result:success": function() {
                        let token = document.querySelector("input[name='ip[token]']").value;
                        let session = document.querySelector("input[name='ip[session_id]']").value;
                        document.querySelector("#ip-token").value = token;
                        document.querySelector("#ip-uuid").value = session;
                        if(token != '') {
                            responseHandler();
                        }
                    },
                    "result:error": function(errors) {
                        return false;
                    }
                };
                ipay.generate(form);

            } else {
                responseHandler();
            }
            return false;
        }

        function infinitePayFormHandler() {

            if (infinite_pay_submit) {
                infinite_pay_submit = false;
                return true;
            }
            if ( !document.getElementById('payment_method_infinitepay').checked ) {
                return true;
            }
            

            if (validateInputs()) return createToken()
            return false;
        }

        $("form.woocommerce-checkout").on( "checkout_place_order", infinitePayFormHandler );
        //$("form#order_review").on( "checkout_place_order", infinitePayFormHandler );
        $("form#order_review").on( "submit", infinitePayFormHandler );

        function init() {
            if(!!window["IPay"]) return;
            var head = document.getElementsByTagName("head")[0];
            var script = document.createElement("script");
                script.async = 1;
                script.src = wc_infinitepay_params.script_url;
            head.parentNode.appendChild(script);
        }
        init();
    })
})(jQuery)