@extends('layouts/default')
@section('page')

<pre style="background: #eee;">
+--------+----------+---------------------+------+------------------------------------------------------------+------------------------------------------+
| Domain | Method   | URI                 | Name | Action                                                     | Middleware                               |
+--------+----------+---------------------+------+------------------------------------------------------------+------------------------------------------+
|        | GET|HEAD | /                   |      | Closure                                                    | web                                      |
|        | GET|HEAD | api/user            |      | Closure                                                    | api                                      |
|        |          |                     |      |                                                            | App\Http\Middleware\Authenticate:sanctum |
|        | GET|HEAD | estimate            |      | App\Http\Controllers\EstimateController@index              | web                                      |
|        | GET|HEAD | estimate/create     |      | App\Http\Controllers\EstimateController@create             | web                                      |
|        | GET|HEAD | estimate/detail     |      | Closure                                                    | web                                      |
|        | GET|HEAD | estimate/edit       |      | App\Http\Controllers\EstimateController@edit               | web                                      |
|        | GET|HEAD | estimate/trash      |      | Closure                                                    | web                                      |
|        | GET|HEAD | expense             |      | App\Http\Controllers\TravelExpenseController@index         | web                                      |
|        | GET|HEAD | expense/create      |      | App\Http\Controllers\TravelExpenseController@create        | web                                      |
|        | GET|HEAD | expense/pdf/{id}    |      | App\Http\Controllers\TravelExpenseController@pdf           | web                                      |
|        | GET|HEAD | inquiry             |      | App\Http\Controllers\InquiryController@index               | web                                      |
|        | GET|HEAD | inquiry/create      |      | App\Http\Controllers\InquiryController@create              | web                                      |
|        | POST     | inquiry/create      |      | App\Http\Controllers\InquiryController@create              | web                                      |
|        | GET|HEAD | inquiry/truncate    |      | App\Http\Controllers\InquiryController@truncate            | web                                      |
|        | GET|HEAD | inquiry/view/{id}   |      | App\Http\Controllers\InquiryController@view                | web                                      |
|        | GET|HEAD | item                |      | App\Http\Controllers\ItemController@index                  | web                                      |
|        | GET|HEAD | item/create         |      | App\Http\Controllers\ItemController@create                 | web                                      |
|        | GET|HEAD | pdf                 |      | App\Http\Controllers\EstimateController@pdf                | web                                      |
|        | GET|HEAD | pricing             |      | Closure                                                    | web                                      |
|        | GET|HEAD | sanctum/csrf-cookie |      | Laravel\Sanctum\Http\Controllers\CsrfCookieController@show | web                                      |
|        | GET|HEAD | trip                |      | App\Http\Controllers\TravelController@index                | web                                      |
|        | GET|HEAD | trip/create         |      | App\Http\Controllers\TravelController@create               | web                                      |
|        | POST     | trip/create         |      | App\Http\Controllers\TravelController@create               | web                                      |
|        | GET|HEAD | trip/pdf/{id}       |      | App\Http\Controllers\TravelController@pdf                  | web
</pre>
@endsection
