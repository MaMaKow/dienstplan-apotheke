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

class roster_image_bar_plot {

    public $svg_string;
    private $Employee_style_array;

    public function __construct($Roster, $svg_width = 650, $svg_height = 424) {
        /*
         * color of the employee bars:
         */
        $this->Employee_style_array[1] = "#73AC22"; /* Apotheker, PI (qualified pharmacists) */
        $this->Employee_style_array[2] = "#BDE682"; /* PTA */
        $this->Employee_style_array[3] = "#B4B4B4"; /* non pharmaceutical employees */
        /*
         * margins and default lengths:
         */
        $this->bar_height = 20;
        $this->bar_width_factor = 40;
        $this->font_size = $this->bar_height * 0.6;
        $this->outer_margin_x = 20;
        $this->outer_margin_y = 20;
        $this->inner_margin_x = $this->bar_height * 0.2;
        $this->inner_margin_y = $this->inner_margin_x;
        /*
         * cursor styles:
         * TODO: These could be turned back to default in read_only views:
         */
        $this->cursor_style_box = 'move';
        $this->cursor_style_break_box = 'cell';

        $this->set_start_end_times($Roster);
        $this->svg_string = $this->draw_image_dienstplan($Roster, $svg_width, $svg_height);
    }

    /**
     *
     * @global object $workforce
     * @param array $Roster
     * @return string The svg element
     */
    private function draw_image_dienstplan($Roster, $svg_width, $svg_height) {
        global $workforce;
        $this->line = 0;
        $javascript_variables = "var bar_width_factor = $this->bar_width_factor;";

        $svg_text = "";
        $svg_text .= "<svg id='svgimg' width='$svg_width' height='$svg_height' class='noselect' >\n";


        foreach ($Roster as $date_unix => $Roster_day_array) {

            $svg_text .= "<g id='svgimg_g_$date_unix'>\n";

            /*
             * draw the bars from start to end for every employee
             */
            $svg_box_text = "<!--Boxes-->\n";
            foreach ($Roster_day_array as $roster_item) {
                $employee_id = $roster_item->employee_id;
                $dienst_beginn = $roster_item->duty_start_int / 3600;
                $dienst_ende = $roster_item->duty_end_int / 3600;
                $break_start = $roster_item->break_start_int / 3600;
                $break_end = $roster_item->break_end_int / 3600;
                $working_hours = $roster_item->working_hours;
                $width_in_hours = $dienst_ende - $dienst_beginn;
                $break_width_in_hours = $break_end - $break_start;

                $employee_style = $this->get_employee_style($employee_id);

                $x_pos_box = $this->outer_margin_x + $this->inner_margin_x + ($dienst_beginn - floor($this->first_start)) * $this->bar_width_factor;
                $x_pos_break_box = $x_pos_box + (($break_start - $dienst_beginn) * $this->bar_width_factor);
                $this->x_pos_text = $x_pos_box;
                $y_pos_box = $this->outer_margin_y + ($this->inner_margin_y * ($this->line + 1)) + ($this->bar_height * $this->line);
                $width = $width_in_hours * $this->bar_width_factor;
                $break_width = $break_width_in_hours * $this->bar_width_factor;
                $svg_box_text .= "<foreignObject id=work_box_$this->line transform='matrix(1 0 0 1 0 0)' onmousedown='selectElement(evt, \"group\")' x='$x_pos_box' y='$y_pos_box' width='$width' height='$this->bar_height' style='cursor: $this->cursor_style_box;'>"
                        . "<p xmlns='http://www.w3.org/1999/xhtml' style='background-color: $employee_style; margin-top: 0px;'>"
                        . $workforce->List_of_employees[$employee_id]->last_name
                        . "<span style='float: right'>$working_hours</span>"
                        . "</p>"
                        . "</foreignObject>";

                $svg_box_text .= "<rect id=break_box_$this->line transform='matrix(1 0 0 1 0 0)' onmousedown='selectElement(evt, \"single\")' x='$x_pos_break_box' y='$y_pos_box' width='$break_width' height='$this->bar_height' stroke='black' stroke-width='0.3' style='fill:#FEFEFF; cursor: $this->cursor_style_break_box;' />\n";
                $this->line++;
            }
            $svg_text .= $svg_box_text;
            $svg_text .= "</g>\n";
            $this->line++;
        }
        $svg_text .= $this->draw_image_dienstplan_add_axis_labeling();
        $svg_text .= "</svg>\n";
        $svg_text .= "<script>$javascript_variables</script>";
        $svg_text .= "<script src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "drag-and-drop.js' ></script>";
//print_debug_variable($svg_text);
        return $svg_text;
    }

    private function draw_image_dienstplan_add_axis_labeling() {

        $svg_inner_height = $this->inner_margin_x * ($this->line + 1) + $this->bar_height * $this->line;
        $svg_outer_height = $svg_inner_height + ($this->outer_margin_y * 2);
        //$svg_inner_width = $this->inner_margin_x * 2 + ((ceil($this->last_end) - floor($this->first_start)) * $this->bar_width_factor);
        //$svg_outer_width = $svg_inner_width + ($this->outer_margin_x * 2);

        $svg_grid_text = "<!--Grid-->\n";

        for ($time = floor($this->first_start); $time <= ceil($this->last_end); $time = $time + 2) {
            $x_pos = $this->outer_margin_x + $this->inner_margin_x + (($time - floor($this->first_start)) * $this->bar_width_factor);
            $x_pos_secondary = $x_pos + ($this->bar_width_factor / 1);
            $this->x_pos_text = $x_pos;
            $y_pos_text = $this->font_size;
            $y_pos_grid_start = $this->outer_margin_y;
            $y_pos_grid_end = $this->outer_margin_y + $svg_inner_height;
            $svg_grid_text .= "<line x1='$x_pos' y1='$y_pos_grid_start' x2='$x_pos' y2='$y_pos_grid_end' stroke-dasharray='1, 8' style='stroke:black;stroke-width:2' />\n";
            $svg_grid_text .= "<line x1='$x_pos_secondary' y1='$y_pos_grid_start' x2='$x_pos_secondary' y2='$y_pos_grid_end' stroke-dasharray='1, 16' style='stroke:black;stroke-width:2' />\n";
            $svg_grid_text .= "<text x='$this->x_pos_text' y='$y_pos_text' font-family='sans-serif' font-size='$this->font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
            $svg_grid_text .= "<text x='$this->x_pos_text' y='$svg_outer_height' font-family='sans-serif' font-size='$this->font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
        }
        return $svg_grid_text;
    }

    private function set_start_end_times($Roster) {
        foreach ($Roster as $Roster_day_array) {
            foreach ($Roster_day_array as $roster_item) {
                $duty_start_list[] = $roster_item->duty_start_int;
                $duty_end_list[] = $roster_item->duty_end_int;
            }
        }
        $this->first_start = min($duty_start_list) / 3600;
        $this->last_end = max($duty_end_list) / 3600;

        return NULL;
    }

    /*
     * This function will be used for coloring the image dependent on the education of the workers.
     *
     * @param int $employee_id
     * @global object $workforce
     * @return string $employee_style_string Currently this is only a color string (e.g. #73AC22).
     *     The colors are defined during __construct()
     */

    private function get_employee_style($employee_id) {
        global $workforce;
        switch ($workforce->List_of_employees[$employee_id]->profession) {
            case "Apotheker":
            case "PI":
                $employee_style = 1;
                break;
            case "PTA":
                $employee_style = 2;
                break;
            case "PKA":
                $employee_style = 3;
                break;
            default:
                $employee_style = 3;
        }
        $employee_style_string = $this->Employee_style_array[$employee_style];
        return $employee_style_string;
    }

}
