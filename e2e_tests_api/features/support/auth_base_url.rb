module BaseAuth
    include HTTParty
    class << self

        def access_token(scope)
            @token = HTTParty.post('https://api-staging.infinitepay.io/v2/oauth/token' ,:body => {
                "grant_type": "client_credentials", 
                "client_id": ENV['CLIENT_ID'],
                "client_secret": ENV['CLIENT_SECRET'],
                "scope": scope
            })
            response = JSON.parse(@token.response.body)
            response['access_token']
        end
    end
   
end
 
module CreateCardToken 
    include HTTParty
    token = ::BaseAuth.access_token('card_tokenization')
    base_uri 'https://authorizer-staging.infinitepay.io/v2'
    format :json
    headers 'Content-type': 'application/json', 'Authorization': 'Bearer ' + token
end

module CreateTransaction 
    include HTTParty
    token = ::BaseAuth.access_token('transactions')
    base_uri 'https://api-staging.infinitepay.io/v2'
    format :json
    headers 'Content-type': 'application/json', 'Authorization': 'Bearer ' + token, "Env": 'mock', 'Response-Type': 'ResponseType/json'
end

module CreateTransactionPix 
    include HTTParty
    token = ::BaseAuth.access_token('transactions')
    base_uri 'https://authorizer-staging.infinitepay.io/v2'
    format :json
    headers 'Content-type': 'application/json', 'Authorization': 'Bearer ' + token, 'Response-Type': 'ResponseType/json'
end
