<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Access;

class LogController extends Controller
{
    /**
     * アクセスログ
     */
    public function access(Request $request)
    {

        $logs = Access::all();
        foreach ($logs as $log) {
            $ip_info = $this->getIpInfo($log->ip_address);
            $ip_string =
                empty($ip_info['hostname']) ?
                'Local access' . "\n" . $ip_info['ip'] : $ip_info['country'] . ' ' . $ip_info['region'] . ' ' . $ip_info['city'] . "\n" . $ip_info['ip'];
            $log->ip_address = nl2br($ip_string);
        }
        return view('logs/access', compact('logs'));
    }

    /**
     * IPアドレス情報取得
     * @param string $ip_address
     * @return array
     * ip, hostname, country, region, city, loc, org, hostname, postal,  timezone
     */
    private function getIpInfo($ip)
    {
        $url = 'http://ipinfo.io/' . $ip . '/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
