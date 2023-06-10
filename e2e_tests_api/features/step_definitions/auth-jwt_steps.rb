Given('the API address to create a token JWT') do
  @card_token = Tokenization.new
end
  
When('I send a request to create it') do
  @response_card_token = @card_token.card_token
end
  
Then('the I have success to tokenize the card') do
  expect(@response_card_token.code).to eql 201
  puts @response_card_token.response.body
end
    