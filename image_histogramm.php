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


/**
 * 
 * @param array $Dienstplan
 * @global array $Erwartung
 * @return string The canvas element
 */
function draw_image_histogramm($Dienstplan) {
    global $Erwartung;
    $canvas_width = 650;
    $canvas_height = 200;
    $start_time = min(array_map('time_from_text_to_int', $Erwartung['uhrzeit']));
    $end_time = max(array_map('time_from_text_to_int', $Erwartung['uhrzeit']));
    $max_work_load = max($Erwartung['packungen']);
    $duration = $end_time - $start_time;
    var_export(array_walk($Erwartung['uhrzeit'], 'time_from_text_to_int'));
    $width_factor = ($canvas_width-10) / $duration;
    $height_factor = ($canvas_height-10) / $max_work_load ;
    $x_start = time_from_text_to_int($Erwartung['uhrzeit'][0]);
    $y_start = time_from_text_to_int($Erwartung['packungen'][0])*-1;
    $canvas_text = "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height' style=background-color:black;>\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
    $canvas_text .= "<script>\n";
    $canvas_text .= "var c = document.getElementById('canvas_histogram');\n";
    $canvas_text .= "var ctx = c.getContext('2d');\n";
    $canvas_text .= "ctx.fillStyle = '#FF0000';\n";
    $canvas_text .= "ctx.translate(0,$canvas_height);\n";
    $canvas_text .= "ctx.scale($width_factor, $height_factor);\n";
    $canvas_text .= "ctx.moveTo($x_start,$y_start);\n";
    foreach ($Erwartung['uhrzeit'] as $line => $uhrzeit) {
        $packungen = $Erwartung['packungen'][$line];
        $x_pos = (time_from_text_to_int($uhrzeit) - $start_time);
        $y_pos = $packungen*-1;
        $canvas_text .= "ctx.lineTo($x_pos, $y_pos);\n";
    }
    //$canvas_text .= $canvas_box_text;
    $canvas_text .= "ctx.lineTo($x_pos, $y_start);\n";
    $canvas_text .= "ctx.stroke();\n";
    $canvas_text .= "ctx.fillStyle = 'red';\n";
    $canvas_text .= "ctx.fill();\n";
    
    $canvas_text .= "</script>";
//header("Content-type: image/canvas+xml");
    return $canvas_text;
}

?>