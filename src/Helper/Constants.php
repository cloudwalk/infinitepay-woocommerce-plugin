<?php
namespace Woocommerce\InfinitePay\Helper;

if (!function_exists('add_action')) {
    exit(0);
}

class Constants
{
    const TEXT_DOMAIN      = 'infinitepay-woocommerce';
    const SLUG             = 'infinitepay';
    const VERSION          = '2.0.17';
    const MIN_PHP          = 5.6;
    const API_IP_BASE_URL  = 'https://api.infinitepay.io';
    const ACCESS_TOKEN_KEY = 'infinitepay_access_token';
    const CLIENT_ID        = 'infinitepay_client_id';
    const CLIENT_SECRET    = 'infinitepay_client_secret';
    const INFINITEPAY_TAX  = [
        1,
        1.3390,
        1.5041,
        1.5992,
        1.6630,
        1.7057,
        2.3454,
        2.3053,
        2.2755,
        2.2490,
        2.2306,
        2.2111,
        2.3333,
    ];

    const ERROR_CODES = [
        [
            'title'   => 'Pagamento não autorizado',
            'content' => 'Contate o banco emissor ou tente novamente com outro cartão.',
            'code'    => [4, 5, 7, 41, 46, 57, 62, 78],
        ],
        [
            'title'   => 'Pagamento não autorizado',
            'content' => 'Contate o banco emissor ou tente novamente com outro cartão.',
            'code'    => [4, 5, 7, 41, 46, 57, 62, 78],
        ],
        [
            'title'   => 'Ocorreu um erro inesperado',
            'content' => 'Verifique os dados do seu cartão e a sua conexão com a Internet.',
            'code'    => [6],
        ],
        [
            'title'   => 'Transação inválida',
            'content' => 'Verifique os dados do seu cartão e tente novamente.',
            'code'    => [12],
        ],
        [
            'title'   => 'Valor inválido',
            'content' => 'Verifique o valor total do seu pagamento e tente novamente.',
            'code'    => [13],
        ],
        [
            'title'   => 'Número de cartão inválido',
            'content' => 'Verifique os dados do seu cartão e tente novamente.',
            'code'    => [14],
        ],
        [
            'title'   => 'Não conseguimos completar o pagamento',
            'content' => 'Houve um problema para efetuar a compra nesse cartão',
            'code'    => [15],
        ],
        [
            'title'   => 'Número do cartão inválido',
            'content' => 'Verifique os dados do seu cartão e tente novamente.',
            'code'    => [30],
        ],
        [
            'title'   => 'Saldo insuficiente',
            'content' => 'Verifique o limite disponível ou tente novamente com outro cartão.',
            'code'    => [51],
        ],
        [
            'title'   => 'Cartão expirado',
            'content' => 'Verifique a data de validade ou tente novamente com outro cartão.',
            'code'    => [54],
        ],
        [
            'title'   => 'Cartão inválido',
            'content' => 'Verifique os dados do cartão e se ele está disponível na função de crédito.',
            'code'    => [58],
        ],
        [
            'title'   => 'Não conseguimos completar o pagamento',
            'content' => 'Por favor, tente novamente com outro cartão.',
            'code'    => [59],
        ],
        [
            'title'   => 'Código de segurança inválido',
            'content' => 'Verifique o CVV do seu cartão e tente novamente.',
            'code'    => [63],
        ],
        [
            'title'   => 'Limite excedido',
            'content' => 'Verifique o limite de compras ou tente novamente com outro cartão.',
            'code'    => [61],
        ],
        [
            'title'   => 'Transação não autorizada.',
            'content' => 'Refazer a transação confirmando os dados. Se o erro persistir, entre em contato com seu banco emissor.',
            'code'    => [83],
        ],
    ];

}
