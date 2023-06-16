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
  @pix_response = @create_pix.pix_payment
  expect(@pix_response.code).to eql 200
  puts @pix_response.code
end

Then('the transaction cannot be approved') do
  expect(@response.code).to eq 422
end