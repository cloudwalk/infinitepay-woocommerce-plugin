class CreateCredential
    
    def create_credential
        Credentials.post('/credentials' ,:body => {
          "source": "woocommerce",
          "site": "www.infiniteshop.io",
          "ecommerce_platform": "woocommerce",
          "ecommerce_payment": "infinitepay"
        }).to_json
    end

    def credential_invalid_user
        ErrorCredential.post('/credentials' ,:body => {
          "source": "woocommerce",
          "site": "www.infiniteshop.io",
          "ecommerce_platform": "woocommerce",
          "ecommerce_payment": "infinitepay"
        }).to_json
    end
end