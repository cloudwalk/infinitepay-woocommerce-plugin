Given('I request a transaction') do
  @create_transaction = Transaction.new
end

When('I sent the transaction data') do 
   @response_transaction = @create_transaction.transaction
   puts @response_transaction.response.body
end 

And('the transaction has success') do
  expect(@response_transaction.code).to eql 200
end

And('I request to cancel this sale') do
  @nsu = "8358efb5-9501-4df1-a237-a99d89efbfff".hex
  @cancel_sale = HTTParty.post("https://api-staging.infinitepay.io/v2/transactions/#{@response_transaction}/refund",
{ 
        :headers => {'Content-type': 'application/json', 'Authorization': 'USC5mpz105BwRWjSVawzWa6OQbo4Lh', 'Env': 'mock', 'Response-Type': 'ResponseType/json'},
        :body => {"nsu": @nsu}
})
end

Then('the transaction have been cancelled') do
  expect(@cancel_sale.code).to eq 200
  puts @cancel_sale.response.body
end

Given('I request a transaction without card token') do
  @transaction_denied = TransactionDenied.new
  puts @transaction_denied
end 

When('I send the transaction') do
  @response = @transaction_denied.transaction_denied
end

Given('I request a Pix transaction')  do
  @create_pix = Pix.new
  puts @create_pix
end

When('I input all the transaction information') do
  @pix_response = @create_pix.pix_transaction
  puts @pix_response.response.body
end

Then('the pix transaction is approved') do
  expect(@pix_response.code).to eql 200
  puts @pix_response.code
end

Then('the transaction cannot be approved') do
  expect(@response.code).to eq 422
end