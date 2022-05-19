(function ($) {
    "use strict"

    $(function () {
        let infinite_pay_submit = false
        let additionalInfo = {}

        function getCheckoutForm() {
            return document.querySelector('#infinitepay-form')
        }

        function validateInputs() {
            clearErrors()
            const mainInputs = validateMainInputs()
            const additionalInputs = validateAdditionalInputs()

            if (mainInputs || additionalInputs) {
                focusInputWithError()
                return false
            }
            return true
        }

        function clearErrors() {
            for (
                let i = 0;
                i < document.querySelectorAll('[data-checkout]').length;
                i++
            ) {
                const errorElement = document.querySelectorAll('[data-checkout]')[i]
                console.log(errorElement)
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

        function validateMainInputs() {
            let hasEmpty = false
            const form = getCheckoutForm()
            const formInputs = form.querySelectorAll('[data-checkout]')
            const mainInputs = [
                'installments',
                'cardSecurityCode',
                'cardExpirationDate',
                'cardNumber',
                'docNumber',
            ]

            for (
                let k = 0;
                k < formInputs.length;
                k++
            ) {
                const element = formInputs[k]
                if (mainInputs.indexOf(element.getAttribute('data-checkout')) > -1) {
                    let withError = false
                    if (element.value === '-1' || element.value === '') withError = true
                    if (element.id === 'ip_docNumber') withError = !(validateCpf(element.value))

                    if (withError) {
                        const errorSpan = form.querySelectorAll(
                            'span[data-main="#' + element.id + '"]'
                        )
                        if (errorSpan.length > 0) errorSpan[0].style.display = 'inline-block'
                        element.classList.add('ip-form-control-error')
                        hasEmpty = true
                    }
                }
            }

            return hasEmpty
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

        function validateCpf(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '')
            if (cpf == '') return false
            if (cpf.length != 11 ||
                cpf == "00000000000" ||
                cpf == "11111111111" ||
                cpf == "22222222222" ||
                cpf == "33333333333" ||
                cpf == "44444444444" ||
                cpf == "55555555555" ||
                cpf == "66666666666" ||
                cpf == "77777777777" ||
                cpf == "88888888888" ||
                cpf == "99999999999")
                return false
            let add = 0
            for (let i = 0; i < 9; i++)
                add += parseInt(cpf.charAt(i)) * (10 - i)
            let rev = 11 - (add % 11)
            if (rev == 10 || rev == 11)
                rev = 0
            if (rev != parseInt(cpf.charAt(9)))
                return false
            add = 0
            for (let i = 0; i < 10; i++)
                add += parseInt(cpf.charAt(i)) * (11 - i)
            rev = 11 - (add % 11)
            if (rev == 10 || rev == 11)
                rev = 0
            return rev == parseInt(cpf.charAt(10))
        }

        function focusInputWithError() {
            const inputsError = document.querySelectorAll('.ip-form-control-error')
            if (inputsError !== undefined) inputsError[0].focus()
        }

        function responseHandler(response) {
            const token = document.querySelector("#ip-token")
            token.value = response.token
            infinite_pay_submit = false
            const wooCheckoutForm = $('form.woocommerce-checkout')
            wooCheckoutForm.off('checkout_place_order', infinitePayFormHandler)
            wooCheckoutForm.submit()
        }

        function createToken() {
            clearErrors()
            // TODO: show loading

            const form = getCheckoutForm()
            const number = document.querySelector('#ip_ccNo').value.replace(/\D+/g, '')
            const expire = document.querySelector('#ip_expdate').value
            const cvv = document.querySelector('#ip_cvv').value
            const token = `${number}:${expire}:${cvv}`
            responseHandler({token})
            return false
        }

        function infinitePayFormHandler() {
            if (infinite_pay_submit) {
                infinite_pay_submit = false
                return true
            }
            if (
                !document.getElementById('payment_method_infinitepay').checked
            ) return true

            if (validateInputs()) return createToken()
            return false
        }

        function init() {
            const uuid = wc_infinitepay_params.uuid
            const script = document.createElement('script')
            script.setAttribute('src', 'https://authorizer-data.infinitepay.io/fp/tags.js?org_id=k8vif92e&session_id=cloudwalk' + uuid)
            script.setAttribute('type', 'text/javascript')
            document.head.appendChild(script)
            const iFrame = document.createElement('div')
            iFrame.setAttribute('id', 'iframe-cloudwatch-auth')
            iFrame.innerHTML = '<noscript><iframe style="width: 100px; height: 100px; border: 0; position:absolute; top: -5000px;" src="https://authorizer-data.infinitepay.io/fp/tags?org_id=k8vif92e&session_id=cloudwalk' + uuid + '"></iframe></noscript>'
            document.body.appendChild(iFrame)
            const inputUUID = document.querySelector("#ip-uuid")
            inputUUID.value = uuid
        }

        $("form.woocommerce-checkout").on(
            "checkout_place_order",
            infinitePayFormHandler
        )
        init()
    })
})(jQuery)