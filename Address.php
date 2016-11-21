<?php
Class Address{
    public function explode_addr($address)
    {
        $Result = array();

        if (strlen($address) < 12) {
            return $Result;
        }
        if (strpos($address, "金門") !== false) {
            $Result['bill_addr'] = $address;
            $Result['bill_city'] = "金門縣";
            $Result['bill_state'] = "";
            $Result['bill_country'] = "TW";
            $Result['bill_postal'] = "890";
            // record_log("bill_address", json_encode($Result) . "\n");
            return $Result;
            exit;
        }

        $fp = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&language=zh-TW&address=" . urlencode($address));

        $Addr = json_decode($fp, true);
        $bill_addr = "";

        $bill_city = "";
        $bill_country = "";
        $bill_postal = "";
        $bill_state = "";
        if (!isset($Addr['results'][0])) {
            return $Result;
        } else {
            foreach ($Addr['results'][0]['address_components'] as $key => $value) {
                switch ($value['types'][0]) {
                    case "premise":
                        $bill_addr = $value['long_name'];
                        break;
                    case "street_number":
                        $bill_addr = $value['long_name'] . "號". $bill_addr;
                        break;
                    case "route":
                        $bill_addr = $value['long_name'] . $bill_addr;
                        break;
                    case "administrative_area_level_4":
                        $bill_addr = $value['long_name'] . $bill_addr;
                        break;
                    case "administrative_area_level_3":
                        $bill_addr = $value['long_name'] . $bill_addr;
                        break;
                    case "administrative_area_level_2":
                        $bill_city = $value['long_name'];
                        $bill_addr = $value['long_name'] . $bill_addr;
                        break;
                    case "administrative_area_level_1":
                        $bill_city = (isset($value['long_name']) && $value['long_name'] != "") ? $value['long_name'] : $bill_city;
                        break;
                    case "locality":
                        $bill_city = $value['long_name'];
                    break;
                    // case "locality":
                    //     $bill_city = ($bill_city == "") ? $value['long_name'] : $bill_city;
                    //     break;
                    case "country":
                        $bill_country = $value['short_name'];
                        if ($value['short_name'] == "US" || $value['short_name'] == "CA") {
                            $bill_state = $value['short_name'];
                        }

                        break;

                    case "postal_code":
                        $bill_postal = $value['long_name'];
                        break;

                }
            }
            if (strpos($address, "澳門") !== false) {
                $bill_city = "澳門";
            }
            $Result['bill_addr'] = $bill_addr;
            $Result['bill_city'] = $bill_city;
            $Result['bill_state'] = $bill_state;
            $Result['bill_country'] = $bill_country;
            $Result['bill_postal'] = ($bill_postal == "") ? 9999 : $bill_postal;
            record_log("bill_address", json_encode($Result) . "\n");
            return $Result;
        }
    }
}
?>