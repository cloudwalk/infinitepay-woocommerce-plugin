class Transaction

    def transaction 
        CreateTransaction.post('/transactions', body: {
            "payment": {
                "amount": 2500,
                "capture_method": "ecommerce",
                "payment_method": "credit",
            },
            "card": {
                "cvv": "769",
                "token": "6c186124-9634-4429-ad7e-a0082ba51791",
                "card_holder_name": "qe teste"
            },
            "customer": {
                "document_number": "012.345.678-90",
                "first_name": "qe",
                "last_name": "teste",
                "email": "qetest@test.com",
                "phone_number": "+551199999999",
                "address": "Rua da Pracinha",
                "complement": "123",
                "city": "Sao Paulo",
                "state": "SP",
                "country": "BR",
                "zip": "12345000"
            },
            "order": {
                "id": "23323",
                "amount": 2500,
                "items": [
                    {
                        "id": "192",
                        "description": "1.00",
                        "quantity": 1,
                        "amount": 0
                    }
                ],
                "delivery_details": {
                    "email": "qetest@test.com",
                    "name": "qe test",
                    "phone_number": "+5511955551111",
                    "address": {
                        "line1": "Rua da Pracinha",
                        "line2": "123",
                        "city": "Sao Paulo",
                        "state": "SP",
                        "country": "BR",
                        "zip": "12345000"
                    }
                }
            },
            "billing_details": {
                "document_number": "012.345.678-90",
                "email": "qetest@test.com",
                "name": "qe test",
                "phone_number": "+5511999999999",
                "address": {
                    "line1": "Rua da Pracinha",
                    "line2": "123",
                    "city": "Sao Paulo",
                    "state": "SP",
                    "country": "BR",
                    "zip": "12345000"
                }
            },
            "metadata": {
                "origin": "woocommerce",
                "store_url": "ste.woo.com.br",
                "payment_method": "credit",
                "plugin_version": "2.0.4",
                "wordpress_version": "6.0.3", 
                "woocommerce_version": "7.0.0",
                "risk": {
                    "session_id": "ff25441a-b8c4-4d9e-8aae-f72a5014f98e",
                    "payer_ip": "200.155.128.253"
                }
            }
        }.to_json)
    end
end

class Pix
    def pix_transaction
        
        CreateTransactionPix.post('/transactions', body: {
                "amount": 140,
                "capture_method": "pix",
                "metadata": {
                    "origin": "woocommerce",
                    "plugin_version": "2.0.14",
                    "wordpress_version": "6.2.2",
                    "woocommerce_version": "7.7.2",
                    "store_url": "infiniteshop.io",
                    "payment_method": "pix",
                    "callback": {
                        "validate": "",
                        "confirm": "https://infiniteshop.io:443/wp-json/wc/v3/infinitepay_pix_callback?order_id=830",
                        "secret": "073936ce894959c38a4fb9bef5091e1d177a6d71"
                    }
                }
            }.to_json)
    end

    def pix_payment
        CreateTransactionPix.post('/transactions', body: {
                "amount": 140,
                "capture_method": "pix",
                "metadata": {
                  "origin": "woocommerce",
                 "payment_method": "pix",
                "order_id": "830",
                "callback": {
                    "validate": "https://infiniteshop.io/validation_callback?order_id=830",
                    "confirm": "https://infiniteshop.io/confirmation_callback?order_id=830",
                    "secret": "073936ce894959c38a4fb9bef5091e1d177a6d71"
              }
            }
            }.to_json)
    end
end

class TransactionDenied

    def transaction_denied
        CreateTransaction.post('/transactions', body: {
            "payment": {
                "amount": 3300,
                "capture_method": "ecommerce",
                "payment_method": "credit",
            },
            "card": {
                "cvv": "666",
                "token": "",
                "card_holder_name": "qe teste"
            },
            "customer": {
                "document_number": "012.345.678-90",
                "first_name": "qe",
                "last_name": "teste",
                "email": "qetest@test.com",
                "phone_number": "+551199999999",
                "address": "Rua da Pracinha",
                "complement": "123",
                "city": "Sao Paulo",
                "state": "SP",
                "country": "BR",
                "zip": "12345000"
            },
            "order": {
                "id": "1922",
                "amount": 3300,
                "items": [
                    {
                        "id": "1923",
                        "description": "33.00",
                        "quantity": 1,
                        "amount": 0
                    }
                ],
                "delivery_details": {
                    "email": "qetest@test.com",
                    "name": "qe test",
                    "phone_number": "+5511955551111",
                    "address": {
                        "line1": "Rua da Pracinha",
                        "line2": "123",
                        "city": "Sao Paulo",
                        "state": "SP",
                        "country": "BR",
                        "zip": "12345000"
                    }
                }
            },
            "billing_details": {
                "document_number": "012.345.678-90",
                "email": "qetest@test.com",
                "name": "qe test",
                "phone_number": "+5511999999999",
                "address": {
                    "line1": "Rua da Pracinha",
                    "line2": "123",
                    "city": "Sao Paulo",
                    "state": "SP",
                    "country": "BR",
                    "zip": "12345000"
                }
            },
            "metadata": {
                "origin": "woocommerce",
                "store_url": "ste.woo.com.br",
                "payment_method": "credit",
                "plugin_version": "2.0.4",
                "wordpress_version": "6.0.3", 
                "woocommerce_version": "7.0.0",
                "risk": {
                    "session_id": "ff25441a-b8c4-4d9e-8aae-f72a5014f98e",
                    "payer_ip": "200.155.128.253"
                }
            }
        }.to_json)
    end
end


    

