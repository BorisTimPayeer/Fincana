<?php

namespace Payeer\ApiTradePayeer;

use Exception;

/**
 * Class ApiTradePayeer
 *
 * @package Payeer\ApiTradePayeer
 */
class ApiTradePayeer
{

    const API_URL = 'https://payeer.com/api/trade/';

    private array $arParams;

    private array $arError;


    public function __construct($params = [])
    {
        $this->arParams = $params;
    }

    /**
     * @throws \Exception
     */
    private function Request(array $request = [])
    {
        $request['post']['ts'] = microtime(true) * 1000;
        $post = json_encode($request['post']);
        $sign = hash_hmac('sha256', $request['method'] . $post, $this->arParams['key']);
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => self::API_URL . $request['method'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json", "API-ID: " . $this->arParams['id'], "API-SIGN: " . $sign,],
            ]
        );
        $response = curl_exec($ch);
        curl_close($ch);
        $arResponse = json_decode($response, true);
        if ($arResponse['success'] !== true) {
            $this->arError = $arResponse['error'];
            throw new Exception($arResponse['error']['code']);
        }
        return $arResponse;
    }


    public function GetError(): array
    {
        return $this->arError;
    }


    /**
     * @throws \Exception
     */
    public function Info()
    {
        return $this->Request(['method' => 'info',]);
    }


    /**
     * @throws \Exception
     */
    public function Orders(string $pair = 'BTC_USDT')
    {
        $res = $this->Request(['method' => 'orders', 'post' => ['pair' => $pair,],]);

        return $res['pairs'];
    }


    /**
     * @throws \Exception
     */
    public function Account()
    {
        $res = $this->Request(['method' => 'account',]);

        return $res['balances'];
    }


    /**
     * @throws \Exception
     */
    public function OrderCreate($req = [])
    {
        return $this->Request(['method' => 'order_create', 'post' => $req,]);
    }


    /**
     * @throws \Exception
     */
    public function OrderStatus($req = [])
    {
        $res = $this->Request(['method' => 'order_status', 'post' => $req,]);

        return $res['order'];
    }


    /**
     * @throws \Exception
     */
    public function MyOrders($req = [])
    {
        $res = $this->Request(['method' => 'my_orders', 'post' => $req,]);

        return $res['items'];
    }

}
