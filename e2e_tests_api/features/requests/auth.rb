class Tokenization

    def card_token
        CreateCardToken.post('/cards/tokenize', body: {
            "number": "2310382324271579",
            "expiration_month": "10",
            "expiration_year": "27"
        }.to_json)
    end

   
end
