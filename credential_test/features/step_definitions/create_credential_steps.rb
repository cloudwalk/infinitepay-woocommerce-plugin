Given('I am an user that wants to create credential') do
  @user_credential =  EcommerceUser.new
end

When('I send request to create my credential') do
  @user_create_credential = @user_credential.valid_user

end

Then('I have success on this flow') do
  puts @user_create_credential
end

Given('I am an user without E-commerce product') do
  @user_not_allowed =  EcommerceUser.new
end

When('I send request to create my credential without this permission') do
  @send_request = @user_not_allowed.invalid_user
end

Then('the request failed') do
  puts @send_request
end