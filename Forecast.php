<?php

namespace Android;

class Forecast extends WeatherData
{
    public function __construct($point_id)
    {
        parent::__construct($point_id);
    }

    public function getPrecipitationHint($forecast)
    {
        $p1 = strpos($forecast->PrecipitationStr,"(");
        $p2 = strpos($forecast->PrecipitationStr,"/");

        if ($p1 === false || $p2 === false)
        {
            $precipitation_1 = 0;
        }
        else
        {
            $precipitation_1 = (float) trim(substr($forecast->PrecipitationStr,$p1+1,$p2-$p1-1));
        }

        $precipitation_1 = $this->units->convert(5, $precipitation_1, 1);

        $precipitation_1 = preg_replace(
            '/\(.+\//',
            "(" . $precipitation_1 . " " . trim($this->units->setInches($precipitation_1)) . " /",
            $forecast->PrecipitationStr
        );

        $prType = substr( $forecast->precipitation_img , 1 , -2 );
        $precipitation_str_mm = \main::$objConfigFunctions->prDesc( $prType , $forecast->PrecipitationStr );
        $precipitation_str_in = \main::$objConfigFunctions->prDesc( $prType , $precipitation_1 );

        $result = new Result();
        $result->mm = $precipitation_str_mm;
        $result->inches = $precipitation_str_in;

        return $result;
    }

    public function getFraction($forecast) {
        $fraction = -1;
        if ($forecast->fraction > 0) {
            if ($forecast->temperature >= 0 || $forecast->temperature < -11)
                $fraction = 1;
            else if ($forecast->temperature < 0 && $forecast->temperature >= -11)
                $fraction = 2;
        }
        return $fraction;
    }

    // до 05.10.2020
    public function getFractionOld($forecast)
    {
        $fraction = -1;

        if($forecast->fraction > 0)
        {
            if ($forecast->temperature >= 0)
            {
                if ($forecast->humidity > 69 && $forecast->humidity <= 90)
                {
                    $fraction = 0;
                }
                else if ($forecast->humidity > 90)
                {
                    $fraction = 1;
                }
            }
            else if ($forecast->temperature < 0)
            {
                if($forecast->humidity > 69 && $forecast->humidity <= 90)
                {
                    $fraction = 0;
                }
                elseif($forecast->humidity > 90)
                {
                    $fraction = 2;
                }
            }
        }

        return $fraction;
    }

    public function getFeelTemp($forecast)
    {
        $feel_temp = round($this->feelTemp->getFeelTemp(
            $forecast->temperature,
            $forecast->wind_velocity,
            $forecast->humidity,
            $this->units)
        );

        return $feel_temp;
    }

    public function getWindVelocityHint($forecast)
    {
        if ($forecast->wind_velocity * 10 < 5)
        {
            return mb_ucfirst(\main::$objConfigFunctions->translate(1708));
        }

        return $this->units->setWinds($forecast->wind_velocity);
    }

    function compareData()
    {
        // TODO: Implement compareData() method.
    }
}