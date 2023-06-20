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
    expected_status = 200
      if response.code.to_i == expected_status
        puts "Status code is as expected: #{expected_status}"
      else
        puts "Status code is not as expected. Expected: #{expected_status}, Actual: #{response.code}"
      end
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
    expected_body = "error=>{message=>Not Authorize}"
      if response.body.include?(expected_body)
        puts "Response body contains the expected text: #{expected_body}"
      else
        puts "Response body does not contain the expected text: #{expected_body}"
      end
    end
end
