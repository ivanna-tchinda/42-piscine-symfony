<?php

$html_file = "Mendeleiev.html";
$elements_file = "ex06.txt";
$content_file = file_get_contents($elements_file);

$mend_table = build_table();
$html_content = '<html>
    <head>
        <title>Mendeleiev Table</title>
        <style>
            li{
                font-size: 10px;
            }
        </style>
    </head>
    <body>' . $mend_table . '</body>
</html>';

file_put_contents($html_file, $html_content);

function empty_cell($num){
    if(($num > 0 && $num < 17) || ($num > 19 && $num < 30) || ($num > 37 && $num < 48) || ($num == 92) || ($num == 110))
        return true;
    return false;
}

function getcolor($num){
    if($num == 0 || ($num > 30 && $num < 34) || ($num > 49 && $num < 52) || ($num == 69))
        return("#3258a8");
    if($num == 18 || $num == 36 || $num == 54 || $num == 72 || $num == 90 || $num == 108)
        return("#349eeb");
    if($num == 19 || $num == 37 || $num == 55 || $num == 73 || $num == 91 || $num == 109)
        return("#a83e32");
    if($num == 17 || $num == 35 || $num == 53 || $num == 71 || $num == 89 || $num == 107)
        return("#c0f3fc");
    if(($num > 55 && $num < 65) || ($num > 73 && $num < 83) || ($num > 92 && $num < 101) || ($num > 110 && $num < 116) || $num == 119)
        return("#fcc0f3");
    if(($num == 48) || ($num > 64 && $num < 67) || ($num > 82 && $num < 86) || ($num > 100 && $num < 106))
        return("#cfcace");
    if($num == 34 || $num == 52 || $num == 70 || $num == 88)
        return("#fcffa1");
    if($num == 30 || $num == 49 || ($num > 66 && $num < 69) || ($num > 85 && $num < 88) || $num == 106)
        return("#9ba396");
    return("white");
}

function tab_content(){
    $content = $GLOBALS["content_file"];
    $lines = explode("\n", $content);
    $tab = "";
    $element_index = 0;
    $index = 0;
    for($row = 0; $row < 7; $row++){
        $tab .= "<tr style='border: solid; height: 100px'>";
        for($col = 0; $col < 18 && $element_index < 88; $col++){
            if(empty_cell($index)){
                $tab .= "<td style='border: solid;'></td>";
            }
            else{
                $infos = explode(",", $lines[$element_index]);
                $first_arg = explode(" ", $infos[0]);
                $name = $first_arg[0];
                $position = $first_arg[2];
                $number = $infos[1];
                $small = $infos[2];
                $molar = $infos[3];
                $electron = $infos[4];
                $getcolor_cell = getcolor($index);
                $tab .= "<td style='border: solid; background-color:$getcolor_cell'>" . 
                    "<h4 style='text-align:center;'>" . $name . "</h4>" .
                    "<ul style='list-style-type:none; margin: 0;'>" .
                        "<li>" . $position . "</li>" .
                        "<li>" . $number . "</li>" .
                        "<li>" . $small . "</li>" .
                        "<li>" . $molar . "</li>" .
                        "<li>" . $electron . "</li>" .
                    "</ul>" 
                ."</td>";
                $element_index++;
            }
            $index++;
        }
        $tab .= "</tr>";
    }
    return $tab;
}

function build_table(){
    $content = "<h4 style='text-align:center; margin-top: 50px;'>Tableau periodique des elements chimiques</h4>";
    $table_content = tab_content();
    $content .= "<table style='width: 70%'>" . $table_content . "</table>";
    return $content;
}

?>
