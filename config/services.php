<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
     * freee 会計 API。
     * 移行前は freeeController が env() を直接読んでいたため、config:cache 済みの
     * 環境では値が取れなかった (env() は .env を読み直さない)。config 経由に寄せている。
     */
    'freee' => [
        'client_id' => env('FREEE_API_CLIENT_ID'),
        'client_secret' => env('FREEE_API_CLIENT_SECRET'),
        'token' => env('FREEE_API_TOKEN'),
        'refresh_token' => env('FREEE_API_REFRESH_TOKEN'),
        'company_id' => env('FREEE_API_COMPANY_ID'),
    ],

    /*
     * Google スプレッドシート連携。認証情報は resources/json/credentials.json。
     */
    'google_sheets' => [
        'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jira
    |--------------------------------------------------------------------------
    |
    | 案件に紐づく Jira のキー (projects.jira_key) からリンクを組むための URL。
    | API を叩くわけではなく、画面のリンク先を作るためだけに使う。
    | 移植元 (in-house-timecard-app) はこの URL をビューに直書きしていた。
    |
    */

    'jira' => [
        'browse_url' => env('JIRA_BROWSE_URL', 'https://novalumo.atlassian.net/browse/'),
    ],

];
