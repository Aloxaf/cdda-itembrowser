<?php

namespace CustomUtility;

class ToHitEnumConversion
{
    public static function GripStringToInt($valueString)
    {
        $result = 0;
        switch(strtolower($valueString)){
            case "bad":
                $result = -1;
            break;
            case "none":
                $result = 0;
            break;
            case "solid":
                $result = 1;
            break;
            case "weapon":
                $result = 2;
            break;
        }
        
        return $result;
    }

    public static function LengthStringToInt($valueString)
    {
        $result = 0;
        switch(strtolower($valueString)){
            case "hand":
                $result = 0;
            break;
            case "short":
                $result = 1;
            break;
            case "long":
                $result = 2;
            break;
        }
        
        return $result;
    }

    public static function SurfaceStringToInt($valueString)
    {
        $result = 0;
        switch(strtolower($valueString)){
            case "point":
                $result = -2;
            break;
            case "line":
                $result = -1;
            break;
            case "any":
                $result = 0;
            break;
            case "every":
                $result = 1;
            break;
        }
        
        return $result;
    }

    public static function BalanceStringToInt($valueString)
    {
        $result = 0;
        switch(strtolower($valueString)){
            case "clumsy":
                $result = -2;
            break;
            case "uneven":
                $result = -1;
            break;
            case "neutral":
                $result = 0;
            break;
            case "good":
                $result = 1;
            break;
        }
        
        return $result;
    }
}
