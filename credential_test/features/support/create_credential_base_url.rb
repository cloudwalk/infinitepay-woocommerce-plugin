require 'httparty'

class EcommerceUser
  
    def valid_user
    response = HTTParty.post('https://infinitepay-api-v2.services.staging.capybaras.dev/users/credentials', headers: {
      'Content-Type' => 'application/json',
      'Authorization' => ENV['API_KEY']
    }, body: {
      "source": "woocommerce",
      "site": "www.infiniteshop.io",
      "ecommerce_platform": "woocommerce",
      "ecommerce_payment": "infinitepay"
    }.to_json)
    
    response.parsed_response
    end

    def invalid_user
      response = HTTParty.post('https://infinitepay-api-v2.services.staging.capybaras.dev/users/credentials', headers: {
      'Content-Type' => 'application/json',
      'Authorization' => ENV['API_KEY_INVALID']
     }, body: {
      "source": "woocommerce",
      "site": "www.infiniteshop.io",
      "ecommerce_platform": "woocommerce",
      "ecommerce_payment": "infinitepay"
     }.to_json)
    
    response.parsed_response
    end
end
