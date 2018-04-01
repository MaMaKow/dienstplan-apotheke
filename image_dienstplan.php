<?php

/*
 * Copyright (C) 2016 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *
 * @global array $List_of_employees
 * @param array $Dienstplan
 * @return string The svg element
 */
function draw_image_dienstplan($Dienstplan, $svg_width = 650, $svg_height = 424) {
    if (empty(current($Dienstplan[0]["VK"]))) {
        return FALSE;
    }

    global $List_of_employees, $List_of_employee_professions;

    if (basename($_SERVER["SCRIPT_FILENAME"]) === 'tag-in.php') {
        $cursor_style_box = 'move';
        $cursor_style_break_box = 'cell';
    } else {
        $cursor_style_box = 'default';
        $cursor_style_break_box = 'default';
    }


    $bar_height = 20;
    $bar_width_factor = 40;
    $javascript_variables = "var bar_width_factor = $bar_width_factor;";
    $font_size = $bar_height * 0.6;

    $inner_margin_x = $bar_height * 0.2;
    $inner_margin_y = $inner_margin_x;
    $outer_margin_x = 20;
    $outer_margin_y = 20;

    $Worker_style[1] = "#73AC22";
    $Worker_style[2] = "#BDE682";
    $Worker_style[3] = "#B4B4B4";

    $lines = count($Dienstplan[0]['VK']);
    $svg_inner_height = $inner_margin_x * ($lines + 1) + $bar_height * $lines;
    $svg_outer_height = $svg_inner_height + ($outer_margin_y * 2);

    $first_start = min(array_map('time_from_text_to_int', $Dienstplan[0]['Dienstbeginn']));
    $last_end = max(array_map('time_from_text_to_int', $Dienstplan[0]['Dienstende']));
    $svg_inner_width = $inner_margin_x * 2 + ((ceil($last_end) - floor($first_start)) * $bar_width_factor);
    $svg_outer_width = $svg_inner_width + ($outer_margin_x * 2);
    /* $svg_text  = "<!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'>\n";
      /*$svg_text .= "<?xml version='1.0' encoding='utf-8'?>\n"; */
    //$svg_text .= "<svg id='svgimg' width='$svg_width' height='$svg_height' class='noselect' cursor: default;' viewBox='0 0 $svg_outer_width $svg_outer_height'>\n";
    $svg_text = "";
    $svg_text .= "<svg id='svgimg' width='$svg_width' height='$svg_height' class='noselect' cursor: default;' viewBox='0 0 $svg_outer_width $svg_outer_height'>\n";

    $svg_grid_text = "\t<!--Grid-->\n";
    for ($time = floor($first_start); $time <= ceil($last_end); $time = $time + 2) {
        $x_pos = $outer_margin_x + $inner_margin_x + (($time - floor($first_start)) * $bar_width_factor);
        $x_pos_secondary = $x_pos + ($bar_width_factor / 1);
        $x_pos_text = $x_pos;
        $y_pos_text = $font_size;
        $y_pos_grid_start = $outer_margin_y;
        $y_pos_grid_end = $outer_margin_y + $svg_inner_height;
        $svg_grid_text .= "\t<line x1='$x_pos' y1='$y_pos_grid_start' x2='$x_pos' y2='$y_pos_grid_end' stroke-dasharray='1, 8' style='stroke:black;stroke-width:2' />\n";
        $svg_grid_text .= "\t<line x1='$x_pos_secondary' y1='$y_pos_grid_start' x2='$x_pos_secondary' y2='$y_pos_grid_end' stroke-dasharray='1, 16' style='stroke:black;stroke-width:2' />\n";
        $svg_grid_text .= "\t\t<text x='$x_pos_text' y='$y_pos_text' font-family='sans-serif' font-size='$font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
        $svg_grid_text .= "\t\t<text x='$x_pos_text' y='$svg_outer_height' font-family='sans-serif' font-size='$font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
    }
    //draw the bars from start to end for every employee
    $svg_box_text = "\t<!--Boxes-->\n";
    foreach ($Dienstplan[0]['VK'] as $line => $vk) {
        $dienst_beginn = time_from_text_to_int($Dienstplan[0]['Dienstbeginn'][$line]);
        $dienst_ende = time_from_text_to_int($Dienstplan[0]['Dienstende'][$line]);
        $break_start = time_from_text_to_int($Dienstplan[0]['Mittagsbeginn'][$line]);
        $break_end = time_from_text_to_int($Dienstplan[0]['Mittagsende'][$line]);
        $working_hours = $Dienstplan[0]['Stunden'][$line];
        $width_in_hours = $dienst_ende - $dienst_beginn;
        $break_width_in_hours = $break_end - $break_start;

        //The next lines will be used for coloring the image dependent on the education of the workers:
        if ($List_of_employee_professions[$vk] == "Apotheker") {
            $worker_style = 1;
        } elseif ($List_of_employee_professions[$vk] == "PI") {
            $worker_style = 1;
        } elseif ($List_of_employee_professions[$vk] == "PTA") {
            $worker_style = 2;
        } elseif ($List_of_employee_professions[$vk] == "PKA") {
            $worker_style = 3;
        } else {
            //anybody else
            $worker_style = 3;
        }




        // echo "$dienst_beginn $first_start<br>\n";
        $x_pos_box = $outer_margin_x + $inner_margin_x + ($dienst_beginn - floor($first_start)) * $bar_width_factor;
        $x_pos_break_box = $x_pos_box + (($break_start - $dienst_beginn) * $bar_width_factor);
        $x_pos_text = $x_pos_box;
        $y_pos_box = $outer_margin_y + ($inner_margin_y * ($line + 1)) + ($bar_height * $line);
        $y_pos_text = $y_pos_box + $bar_height;
        $width = $width_in_hours * $bar_width_factor;
        $break_width = $break_width_in_hours * $bar_width_factor;
        $x_pos_text_secondary = $x_pos_text + $width;

        $svg_box_text .= "<g id=work_box_$line transform='matrix(1 0 0 1 0 0)' onmousedown='selectElement(evt, \"group\")' style='cursor: $cursor_style_box;'>";
        $svg_box_text .= "\t<rect x='$x_pos_box' y='$y_pos_box' width='$width' height='$bar_height' style='fill: $Worker_style[$worker_style];' />\n";
        $svg_box_text .= "\t\t<text x='$x_pos_text' y='$y_pos_text' class='noselect' font-family='sans-serif' font-size='$font_size' alignment-baseline='ideographic'>" . $List_of_employees[$vk] . "</text>\n";
        $svg_box_text .= "\t\t<text x='$x_pos_text_secondary' y='$y_pos_text' class='noselect' font-family='sans-serif' font-size='$font_size' alignment-baseline='ideographic' text-anchor='end'>" . $working_hours . "</text>\n";
        $svg_box_text .= "</g>";

        $svg_box_text .= "\t<rect id=break_box_$line transform='matrix(1 0 0 1 0 0)' onmousedown='selectElement(evt, \"single\")' x='$x_pos_break_box' y='$y_pos_box' width='$break_width' height='$bar_height' stroke='black' stroke-width='0.3' style='fill:#FEFEFF; cursor: $cursor_style_break_box;' />\n";
    }
    $svg_text .= $svg_box_text;
    $svg_text .= $svg_grid_text;
//    $svg_text .= $svg_break_box_text;
    $svg_text .= "</svg>\n";
    $svg_text .= "<script>$javascript_variables</script>";
    $svg_text .= "<script src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "drag-and-drop.js' ></script>";

    //header("Content-type: image/svg+xml");
    return $svg_text;
}
