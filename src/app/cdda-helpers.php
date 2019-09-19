<?php
function colorToCSS($color)
{
    $assoc = array(
        "" => "black",
        "lightblue" => "DodgerBlue",
        "lightcyan" => "Cyan",
        "cyan" => "DarkCyan",
        "lightred" => "LightCoral",
        "magenta" => "DarkMagenta",
        "pink" => "HotPink",
    );
    if (isset($assoc[$color])) {
        return $assoc[$color];
    }

    return $color;
}

function colorPairToCSS($color)
{
    // handle seasonal color array by taking only the spring color for now
    if (is_array($color)) {
        $activecolor = $color[0];
    } else {
        $activecolor = str_replace(array("light_", "dark_", "i_"), array("light", "dark", ""), $color);
    }

    if (strpos($activecolor, '_') === false) {
        return array(colorToCSS($activecolor), "black");
    }

    $colors = explode("_", "${activecolor}_");
    $foreground = $colors[0];
    $background = $colors[1];

    return array(
        colorToCSS($foreground),
        colorToCSS($background),
    );
}
