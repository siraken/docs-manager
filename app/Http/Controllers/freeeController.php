<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class freeeController extends Controller
{
    public $bearer;
    public $company_id;

    public function __construct()
    {
        $this->bearer = 'Authorization: Bearer ' . env('FREEE_API_TOKEN');
        $this->company_id = env('FREEE_API_COMPANY_ID');
    }

    public function getCurlData()
    {
        return [
            "content_type" => "Content-Type:application/x-www-form-urlencoded",
            "client_id" => "client_id=" . env("FREEE_API_CLIENT_ID"),
            "client_secret" => "client_secret=" . env("FREEE_API_CLIENT_SECRET"),
            "callback" => "redirect_uri=" . urlencode("urn:ietf:wg:oauth:2.0:oob"),
        ];
    }

    public function getAccessTokenByAuthCode($code)
    {
        $curl_data = $this->getCurlData();
        $grant_type = "grant_type=authorization_code";
        $auth_code = "code=" . $code;

        $headers = [
            $curl_data['content_type'],
        ];

        $data = [
            $grant_type,
            $curl_data['client_id'],
            $curl_data['client_secret'],
            $auth_code,
            $curl_data['callback']
        ];

        $data_string = implode("&", $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.secure.freee.co.jp/public_api/token',);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getTokenByRefreshToken($refresh_token)
    {
        $curl_data = $this->getCurlData();

        $grant_type = "grant_type=refresh_token";
        $token = $refresh_token === "null" ? env("FREEE_API_REFRESH_TOKEN") : $refresh_token;
        $ref_token = "refresh_token=" . $token;

        $headers = [
            $curl_data['content_type'],
        ];

        $data = [
            $grant_type,
            $curl_data['client_id'],
            $curl_data['client_secret'],
            $ref_token,
            $curl_data['callback']
        ];

        $data_string = implode("&", $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.secure.freee.co.jp/public_api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getCompanies()
    {
        $url = "https://api.freee.co.jp/api/1/companies";

        $headers = [
            $this->bearer
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getWalletables()
    {
        $url = "https://api.freee.co.jp/api/1/walletables?company_id=" . $this->company_id;

        $headers = [
            $this->bearer
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getPartners()
    {
        $url = "https://api.freee.co.jp/api/1/partners?company_id=" . $this->company_id;

        $headers = [
            $this->bearer
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getQuotations()
    {
        $url = "https://api.freee.co.jp/api/1/quotations?company_id=" . $this->company_id;

        $headers = [
            $this->bearer
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getInvoices()
    {
        $url = "https://api.freee.co.jp/api/1/invoices?company_id=" . $this->company_id;

        $headers = [
            $this->bearer
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }


}
