<?php
namespace App\Lib;

class Common {

    public function getMonths()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++)
        {
            $val = $i;
            $months[$val] = str_pad($val, 2, "0", STR_PAD_LEFT);
        }
        return $months;
    }

    public function getTaxes()
    {
        $taxes = [
            [
                "id" => 1,
                "name" => "10%",
                "per" => 0.1
            ],
            [
                "id" => 2,
                "name" => "軽減8%",
                "per" => 0.08
            ],
            [
                "id" => 3,
                "name" => "8%",
                "per" => 0.08
            ],
            [
                "id" => 4,
                "name" => "5%",
                "per" => 0.05
            ],
            [
                "id" => 5,
                "name" => "対象外",
                "per" => 0
            ],
        ];
        return $taxes;
    }
}
