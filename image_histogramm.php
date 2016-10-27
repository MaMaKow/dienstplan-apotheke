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

//    $inner_margin_x = $bar_height * 0.2;
//    $inner_margin_y = $inner_margin_x;
    $outer_margin_x = 30;
    $outer_margin_y = 20;
    $font_size = 24;

    $start_time = min(array_map('time_from_text_to_int', $Erwartung['uhrzeit']));
    $end_time = max(array_map('time_from_text_to_int', $Erwartung['uhrzeit']));
    $max_work_load = max($Erwartung['packungen']);
    $duration = $end_time - $start_time;
    $width_factor = ($canvas_width-($outer_margin_x*2)) / $duration;
    $height_factor = ($canvas_height-($outer_margin_y*2)) / $max_work_load ;
    $x_start = $outer_margin_x/$width_factor;
    $y_start = $outer_margin_y/$height_factor*-1;

    $canvas_text = "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height' style=background-color:#F0F0F0;>\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
    $canvas_text .= "<script>\n";
    $canvas_text .= "var c = document.getElementById('canvas_histogram');\n";
    $canvas_text .= "var ctx = c.getContext('2d');\n";
    $canvas_text .= "ctx.fillStyle = '#FF0000';\n";
    $canvas_text .= "ctx.translate(0,$canvas_height);\n";
    $canvas_text .= "ctx.scale($width_factor, $height_factor);\n";

    $canvas_text .= "ctx.moveTo($x_start, $y_start);\n";
    foreach ($Erwartung['uhrzeit'] as $line => $uhrzeit) {
        $packungen = $Erwartung['packungen'][$line];
        $x_pos = (time_from_text_to_int($uhrzeit) - $start_time)+$outer_margin_x/$width_factor;
        $y_pos = ($packungen*-1)-($outer_margin_y/$height_factor);
        $canvas_text .= "ctx.lineTo($x_pos, $y_pos);\n";
    }
    //$canvas_text .= $canvas_box_text;
    $canvas_text .= "ctx.lineTo($x_pos, $y_start);\n";
    $canvas_text .= "ctx.closePath();";
//    $canvas_text .= "ctx.stroke();\n";
    $canvas_text .= "ctx.fillStyle = 'red';\n";
    $canvas_text .= "ctx.fill();\n";
    
    $canvas_text .= "ctx.scale(" . 1/$width_factor . ", " . 1/$height_factor . ");\n";
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

?>