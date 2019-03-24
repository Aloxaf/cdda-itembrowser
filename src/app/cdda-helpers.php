<?php
function colorToCSS($color)
{
    $assoc = array(
    "" => "black",
    "ltgray" => "LightGray",
    "ltgreen" => "LightGreen",
    "ltblue" => "DodgerBlue",
    "dkgray" => "DarkGray",
    "ltcyan" => "Cyan",
    "cyan" => "DarkCyan",
    "ltred" => "LightCoral",
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
    $activecolor = "";
    // handle seasonal color array by taking only the spring color for now
    if (is_array($color)) {
        $activecolor = $color[0];
    }
    if (count($activecolor) > 1 && $activecolor[1] == "_") {
        $activecolor = substr($activecolor, 2);
    }

    $activecolor = explode("_", "{$activecolor}_");
    $foreground = $activecolor[0];
    $background = $activecolor[1];

    return array(
        colorToCSS($foreground),
        colorToCSS($background),
    );
}
