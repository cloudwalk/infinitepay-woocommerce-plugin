module BaseAuthTransaction
    include HTTParty
    class << self

        def access_token(scope)
            @token = HTTParty.post('https://api-staging.infinitepay.io/v2/oauth/token' ,:body => {
                "grant_type": "client_credentials", 
                "client_id": "7851d7ef6cbf0b496cf84235492052c1",
                "client_secret": "8d188f11489e80db162cd1f2b3a38b5b3ead315bc226b63e7d10a977b96a3e84",
                "scope": scope
            })
            response = JSON.parse(@token.response.body)
            response['access_token']
        end
    end
end
 
module CreateTransaction 
    include HTTParty
    token = ::BaseAuthTransaction.access_token('transactions')
    base_uri 'https://api-staging.infinitepay.io/v2'
    format :json
    headers 'Content-type': 'application/json', 'Authorization': 'Bearer ' + token, "Env": 'mock', 'Response-Type': 'ResponseType/json'
end

module CreateTransactionPix 
    include HTTParty
    token = ::BaseAuthTransaction.access_token('transactions')
    base_uri 'https://authorizer-staging.infinitepay.io/v2'
    format :json
    headers 'Content-type': 'application/json', 'Authorization': 'Bearer ' + token, 'Response-Type': 'ResponseType/json'
end