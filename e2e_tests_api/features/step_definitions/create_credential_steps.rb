# Given('I am an user that wants to create credential') do
#   @user_credential = CreateCredential.new
# end

# When('I send request to create my credential') do
#   @user_create_credential = @user_credential.create_credential
# end

# Then('I have success on this flow') do
#   expect(@user_create_credential.code).to eq 201
# end


# Given('I dont have e-commerce product allowed') do
#   @request_error = CreateCredential.new
# end
  
# When('I send request to create my credential without this permission') do
#   @send_request = @request_error.credential_invalid_user
# end

# Then('the request failed') do
#   expect(@send_request.code).to eq 401
# end