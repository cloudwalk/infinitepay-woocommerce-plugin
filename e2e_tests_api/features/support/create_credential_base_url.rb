module Credentials
    include HTTParty
    class << self
    
      def create_credential_user
        base_uri 'https://infinitepay-api-v2-dot-infinitepay-staging.rj.r.appspot.com/users/'
        format :json
        headers 'Content-type': 'application/json', 'Authorization': "9ea3be77c34fbd2f0e5d5be83853e866" #ENV['API_KEY']
      end

    end
end

module ErrorCredential
    include HTTParty
    def create_credential_invalid_user
        base_uri 'https://infinitepay-api-v2-dot-infinitepay-staging.rj.r.appspot.com/users/'
        format :json
        headers 'Content-type': 'application/json', 'Authorization': "881993493fe1141fd7b3045d4c7396bb" #ENV['API_KEY_INVALID']
    end

end