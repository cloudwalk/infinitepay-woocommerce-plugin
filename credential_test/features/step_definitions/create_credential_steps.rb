Given('I am an user that wants to create credential') do
  @user_credential =  EcommerceUser.new
end

When('I send request to create my credential') do
  @user_create_credential = @user_credential.valid_user

end

Then('I have success on this flow') do
  expect(@user_create_credential.code).to eql 201
  puts @user_create_credential.response.body
end

Given('I am an user without E-commerce product') do
  @user_not_allowed =  EcommerceUser.new
end

When('I send request to create my credential without this permission') do
  @send_request = @user_not_allowed.invalid_user
end

Then('the request failed') do
  expect(@send_request.code).to eql 401
  puts @send_request.response.body
end