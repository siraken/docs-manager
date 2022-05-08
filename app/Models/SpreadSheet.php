<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpreadSheet extends Model
{
    use HasFactory;

    public static function instance()
    {
        $credentials_path = resource_path('json/credentials.json');;
        $client = new \Google_Client();
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($credentials_path);
        return new \Google_Service_Sheets($client);
    }

    static function insert_spread_sheet($insert_data)
    {
        $sheets = SpreadSheet::instance();

        $sheet_id = env('GOOGLE_SPREADSHEET_ID');

        $range = 'A1:A';
        $response = $sheets->spreadsheets_values->get($sheet_id, $range);

        // $row = count($response->getValues()) + 1;
        // var_dump($response);
        // var_dump($response->getValues());
        // exit;
        $row = 1;

        $contact = [
            $insert_data['hoge'],
            $insert_data['huga'],
            $insert_data['foo'],
        ];

        $values = new \Google_Service_Sheets_ValueRange();
        $values->setValues([
            'values' => $contact
        ]);
        $sheets->spreadsheets_values->append(
            $sheet_id,
            'A'.$row,
            $values,
            ["valueInputOption" => 'USER_ENTERED']
        );

        return true;
    }
}
