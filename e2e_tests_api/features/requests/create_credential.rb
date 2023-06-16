class CreateCredential
    
    def create_credential
        Credentials.post('/credentials' ,:body => {
        {
          "source": "woocommerce",
          "site": "www.infiniteshop.io",
          "ecommerce_platform": "woocommerce",
          "ecommerce_payment": "infinitepay"
        }
    }).to_json
    end

end