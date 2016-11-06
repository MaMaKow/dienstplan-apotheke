<?php
/*
 * Copyright (C) 2016 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
//    echo '<pre>';    var_export($Erwartung);   echo '</pre>';
require_once 'headcount-duty-roster.php';

function get_Erwartung ($datum, $mandant) {
    global $verbindungi;
    global $Pep_mandant;

    $sql_weekday = date('N', $datum)-1;
    $month_day = date('j', $datum);
    $month = date('n', $datum);

    $pep_mandant = $Pep_mandant[$mandant];
    
    $abfrage = "SELECT Uhrzeit, Mittelwert FROM `pep_zeit_im_wochentag`  WHERE Mandant = $pep_mandant and Wochentag = $sql_weekday";
    $ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
    while($row = mysqli_fetch_object($ergebnis)) {
        $Packungen[$row->Uhrzeit]=$row->Mittelwert;
    }

    $abfrage = "SELECT factor FROM `pep_tag_im_monat`  WHERE `branch` = $pep_mandant and `day` = $month_day";
    $ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
    $row = mysqli_fetch_object($ergebnis);
    $factor_tag_im_monat = $row->factor;

    $abfrage = "SELECT factor FROM `pep_monat_im_jahr`  WHERE `branch` = $pep_mandant and `month` = $month";
    $ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
    $row = mysqli_fetch_object($ergebnis);
    $factor_monat_im_jahr = $row->factor;

    foreach ($Packungen as $time => $average) {
        $Erwartung[$time] = $average * $factor_monat_im_jahr * $factor_tag_im_monat;
    }
//    echo '<pre>';    var_export($Packungen);   echo '</pre>';
    
    return $Erwartung;
}

    /**
     * @var float $factor_employee The number of drug packages that can be sold per employee within a certail time.
     */
    $factor_employee = 6;
//TODO: Erwartung should be moved into the databasecompletely!
// We need the reader for Erwartung here or inside a seperate function file!
    
/**
 * 
 * @param array $Dienstplan
 * @global int $mandant
 * @global string $datum
 * @global array $Anwesende
 * @return string The canvas element
 */
function draw_image_histogramm($Dienstplan) {
    global $mandant, $Anwesende, $datum;
    global $factor_employee;

    $Erwartung = get_Erwartung ($datum, $mandant);
//    echo '<pre>';    var_export($Erwartung);   echo '</pre>';
    
    $canvas_width = 650;
    $canvas_height = 200;

//    $inner_margin_x = $bar_height * 0.2;
//    $inner_margin_y = $inner_margin_x;
    $outer_margin_x = 30;
    $outer_margin_y = 20;
    $font_size = 16;

    $start_time = min(array_map('time_from_text_to_int', $Dienstplan[0]['Dienstbeginn']));
    $end_time = max(array_map('time_from_text_to_int', $Dienstplan[0]['Dienstende']));
    $duration = $end_time - $start_time;
    $width_factor = ($canvas_width-($outer_margin_x*2)) / $duration;

    $max_work_load = max($Erwartung);
    $max_workforce = max($Anwesende)*$factor_employee;
    $max_height = max($max_work_load, $max_workforce);
    $height_factor = ($canvas_height-($outer_margin_y*2)) / $max_height ;

    $x_start = $outer_margin_x/$width_factor;
    $y_start = $outer_margin_y/$height_factor*-1;

    $canvas_text = "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height' >\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
    $canvas_text .= "<script>\n";
    $canvas_text .= "var c = document.getElementById('canvas_histogram');\n";
    $canvas_text .= "var ctx = c.getContext('2d');\n";
    $red = hex2rgb('#FF0000');
    $canvas_text .= "ctx.translate(0,$canvas_height);\n";
    $canvas_text .= "ctx.scale($width_factor, $height_factor);\n";

    $canvas_text .= "ctx.moveTo($x_start, $y_start);\n";
    foreach ($Erwartung as $time => $packages) {
        $x_pos = (time_from_text_to_int($time) - $start_time)+$outer_margin_x/$width_factor;
        $y_pos = ($packages*-1)-($outer_margin_y/$height_factor);
        $canvas_text .= "ctx.lineTo($x_pos, $y_pos);\n";
    }
    //$canvas_text .= $canvas_box_text;
    $canvas_text .= "ctx.lineTo($x_pos, $y_start);\n";
    $canvas_text .= "ctx.closePath();";
//    $canvas_text .= "ctx.stroke();\n";
    $canvas_text .= "ctx.fillStyle = 'rgba($red, 0.5)';\n";
    $canvas_text .= "ctx.fill();\n";

    
    $canvas_text .= "ctx.scale(" . 1/$width_factor . ", " . 1/$height_factor . ");\n";
    $canvas_text .= draw_image_dienstplan_add_headcount($outer_margin_x, $width_factor, $height_factor, $start_time);

    $canvas_text .= "ctx.strokeStyle = '#B4B4B4';";
    $canvas_text .= "ctx.lineWidth=1;\n";
    $canvas_text .= "ctx.fillStyle = 'black';\n";
    $canvas_text .= "ctx.font = '"."$font_size"."px sans-serif';\n";
    $canvas_text .= "ctx.textAlign = 'center';\n";
    for ($time = floor($start_time); $time <= ceil($end_time); $time = $time + 2) {
        $x_pos = ($time-$start_time)*$width_factor+$outer_margin_x;
        $x_pos_secondary = $x_pos + 1*$width_factor;
        $y_pos = 0;
        $y_pos_line_start = (($outer_margin_y/$height_factor)+$font_size)*-1;
        $y_pos_line_end = -$canvas_height+($outer_margin_y/$height_factor);
        $canvas_text .= "ctx.fillText('$time:00', '$x_pos', '$y_pos');\n";
        $canvas_text .= "ctx.beginPath();\n"
                . "ctx.setLineDash([5, 5]);\n"
                . "ctx.moveTo($x_pos, $y_pos_line_start);\n"
                . "ctx.lineTo($x_pos, $y_pos_line_end);\n"
                . "ctx.stroke();\n"
                . "ctx.closePath();\n";         
        $canvas_text .= "ctx.beginPath();\n"
                . "ctx.setLineDash([1, 5]);\n"
                . "ctx.moveTo($x_pos_secondary, $y_pos_line_start);\n"
                . "ctx.lineTo($x_pos_secondary, $y_pos_line_end);\n"
                . "ctx.stroke();\n"
                . "ctx.closePath();\n";         
    }
    $canvas_text .= "</script>";
//header("Content-type: image/canvas+xml");
    return $canvas_text;
}

function draw_image_dienstplan_add_headcount ($outer_margin_x, $width_factor, $height_factor, $start_time) {
    global $Anwesende, $Changing_times;
    global $factor_employee;
    $canvas_text  = "ctx.beginPath();\n";
    $canvas_text .= "ctx.setLineDash([]);\n";
    $canvas_text .= "ctx.lineWidth=5;\n";
    foreach ($Changing_times as $time) {
        $unix_time = strtotime($time);
        $time_float = time_from_text_to_int($time);
        $x_pos_line_start = $x_pos_line_end;
        $y_pos_line_start = $y_pos_line_end;
        $x_pos_line_end = ($time_float-$start_time)*$width_factor+$outer_margin_x;
        $y_pos_line_end = $Anwesende[$unix_time]*$height_factor*-1*$factor_employee;
        if (empty($x_pos_line_start)) {continue;} //Skipping the first round.

        $canvas_text .= ""
                     .  "ctx.lineTo($x_pos_line_end, $y_pos_line_start);\n"
                     .  "ctx.lineTo($x_pos_line_end, $y_pos_line_end);\n";
    }
    $green = hex2rgb('#73AC22');
    $canvas_text .= "ctx.strokeStyle = 'rgba($green, 0.5)';";
    $canvas_text .= "ctx.stroke();\n";
    $canvas_text .= "ctx.closePath();\n";

    return $canvas_text;
}

    //echo '<pre>';    var_export(draw_image_dienstplan_add_headcount());   echo '</pre>';


?>