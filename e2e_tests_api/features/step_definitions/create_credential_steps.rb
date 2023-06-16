Given('I am an user that wants to create credential') do
  @user_credential = CreateCredential.new
end

When('I send request to create my credential') do
  @user_create_credential = @user_credential.create_credential
end

Then('I have success on this flow') do
  expect(@user_create_credential.code).to eq 201
  puts @user_create_credential.response.body
end

Then('the request failed') do
  expect(@user_create_credential.code).to eq 401
  puts @user_create_credential.response.body
end