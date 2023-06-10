require 'net/http'
require 'json'

proxy_host = 'pg-staging.infinitepay.io'
proxy_port = 19170


begin
  while true do
    http = Net::HTTP.new(proxy_host, proxy_port)
    
    headers = { 'Content-Type' => 'application/json' }
    body = { 'advertisement_id' => '5d47c701-decb-4674-92bc-4a75c1df1b38', 'user': { 'phone_number'=> '+5511977433900', 'role' => 'cardholder'}, 'channel' => 'whatsapp' }.to_json

    response = http.post('https://infinitepay-api-v2-dot-infinitepay-staging.rj.r.appspot.com/users/login_notifier', { body: body, headers: headers })
    puts response.code
  
  if response.code == "503"
       puts "Erro 503 recebido, tentando novamente em 5 segundos..."
      sleep(5)
       puts response.code
     else
       puts "Conexão bem-sucedida com o servidor proxy"
       break
     end
   end
rescue
    puts "Não foi possível conectar ao servidor proxy"
end


      











# require 'net/telnet'


# begin
#     connection = Net::Telnet::new('Host' => 'pg-staging.infinitepay.io', 'Port' => 19170)
#     puts "Conexão bem-sucedida com o servidor proxy"
#     puts connection
# rescue
#     while connection 
#     puts "Não foi possível conectar ao servidor proxy"
#     puts connection
# end

# # require 'net/http'
# # require 'uri'

# # proxy_host = 'pg-staging.infinitepay.io'
# # proxy_port = 19170

# # uri = URI.parse('https://infinitepay-api-v2-dot-infinitepay-staging.rj.r.appspot.com/users/login_notifier')
# # https = Net::HTTP.new(uri.host, uri.port, proxy_host, proxy_port)
# # https.use_ssl = true

# # response = https.get(uri.request_uri)

# # if response.code == '200'
# #   puts 'Proxy conectado'
#else
# #   puts 'Proxy não conectado'
# # end
