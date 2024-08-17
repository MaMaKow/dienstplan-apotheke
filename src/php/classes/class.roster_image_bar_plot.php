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
    private $total_number_of_lines;
    private $first_start;
    private $last_end;

    /**
     * @var bar_width_factor int describes the width of one hour in pixels.
     */
    private $bar_width_factor = 40;
    private $font_size;
    private $outer_margin_x;
    private $outer_margin_y;
    private $inner_margin_x;
    private $inner_margin_y;
    /*
     * margins and default lengths:
     */
    private $bar_height = 20;
    private $svg_outer_width;
    private $svg_inner_height;
    private $svg_outer_height;

    public function __construct($Roster, $svg_width = 650, $svg_height = 424) {
        foreach ($Roster as $Roster_day_array) {
            $this->total_number_of_lines++;
            foreach ($Roster_day_array as $roster_item) {
                if ($roster_item->employee_key !== NULL) {
                    $this->total_number_of_lines++;
                }
            }
        }
        if (count($Roster) == $this->total_number_of_lines) {
            /*
             * There are no non-empty roster items in the roster.
             */
            return FALSE;
        }
        $this->set_start_end_times($Roster);
        $this->font_size = $this->bar_height * 0.6;
        $this->outer_margin_x = 20;
        $this->outer_margin_y = 20;
        $this->inner_margin_x = $this->bar_height * 0.2;
        $this->inner_margin_y = $this->inner_margin_x;

        $svg_inner_width = $this->inner_margin_x * 2 + ((ceil($this->last_end) - floor($this->first_start)) * $this->bar_width_factor);
        $this->svg_outer_width = $svg_inner_width + ($this->outer_margin_x * 2);
        $this->svg_inner_height = $this->inner_margin_x * ($this->total_number_of_lines + 1) + $this->bar_height * $this->total_number_of_lines;
        $this->svg_outer_height = $this->svg_inner_height + ($this->outer_margin_y * 2);

        $this->svg_string = $this->draw_image_dienstplan($Roster, $svg_width, $svg_height);
    }

    /**
     *
     * @global object $workforce
     * @param array $Roster
     * @return string The svg element
     */
    private function draw_image_dienstplan($Roster, $svg_width, $svg_height) {
        $line = 0;

        $svg_viewBox_x_start = $this->first_start * $this->bar_width_factor;
        $svg_viewBox_y_start = 0;
        $svg_viewBox_width = $this->svg_outer_width;
        $svg_viewBox_heigt = $this->svg_outer_height;
        $svg_viewBox_string = "$svg_viewBox_x_start $svg_viewBox_y_start $svg_viewBox_width $svg_viewBox_heigt ";

        $svg_text = "";
        $svg_text .= "<svg "
                . "width='$svg_width' height='$svg_height' "
                . "class='roster_bar_plot svg-img noselect' "
                . "viewBox='$svg_viewBox_string' "
                . "data-inner_margin_x=$this->inner_margin_x "
                . "data-outer_margin_x=$this->outer_margin_x "
                . "data-inner_margin_y=$this->inner_margin_y "
                . "data-outer_margin_y=$this->outer_margin_y "
                . "data-bar_height=$this->bar_height "
                . "data-bar_width_factor=$this->bar_width_factor"
                . ">\n";

        foreach ($Roster as $date_unix => $Roster_day_array) {
            $workforce = new workforce(date('Y-m-d', $date_unix));

            $svg_text .= "<g id='svg_img_g_$date_unix'>\n";
            if (1 < count($Roster)) {
                /*
                 * Insert the name of the weekday if there is more than one day in the plot:
                 */
                $x_pos_svg_weekday_text = $this->outer_margin_x + $this->inner_margin_x;
                $y_pos_svg_weekday_text = $this->outer_margin_y + ($this->inner_margin_y * ($line + 1)) + ($this->bar_height * $line);
                $configuration = new \PDR\Application\configuration();
                $locale = $configuration->getLanguage();
                $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE');
                $weekday_string = $formatter->format($date_unix);
                $svg_text .= "<g id=svg_weekday_text_$date_unix x='$x_pos_svg_weekday_text' y='$y_pos_svg_weekday_text' width='100%' height='$this->bar_height'>"
                        . "<text xmlns='http://www.w3.org/1999/xhtml' style='margin-top: 0px;'>"
                        . $weekday_string
                        . "</text>"
                        . "</g>";
                $line++;
            }
            /*
             * Draw the bars from start to end for every employee:
             */
            $svg_box_text = "<!-- Boxes -->\n";
            foreach ($Roster_day_array as $roster_item) {
                if (NULL === $roster_item->employee_key) {
                    continue;
                }
                $employee_key = $roster_item->employee_key;
                $dienst_beginn = $roster_item->duty_start_int / 3600;
                $dienst_ende = $roster_item->duty_end_int / 3600;
                $break_start = $roster_item->break_start_int / 3600;
                $break_end = $roster_item->break_end_int / 3600;
                $working_hours = $roster_item->working_hours;
                $width_in_hours = $dienst_ende - $dienst_beginn;
                $break_width_in_hours = $break_end - $break_start;
                if (isset($workforce->List_of_employees[$employee_key]->profession)) {
                    $employee_style_class = $workforce->List_of_employees[$employee_key]->profession;
                } else {
                    $employee_style_class = '';
                }

                $x_pos_box = $this->outer_margin_x + $this->inner_margin_x + ($dienst_beginn) * $this->bar_width_factor;
                $x_pos_break_box = $x_pos_box + (($break_start - $dienst_beginn) * $this->bar_width_factor);
                $x_pos_text = $x_pos_box;
                $y_pos_box = $this->outer_margin_y + ($this->inner_margin_y * ($line + 1)) + ($this->bar_height * $line);
                $y_pos_text = $y_pos_box + $this->bar_height / 2;
                $width = $width_in_hours * $this->bar_width_factor;
                $break_width = $break_width_in_hours * $this->bar_width_factor;
                $work_box_id = "work_box_" . $line . '_' . $roster_item->date_unix;
                $break_box_id = "break_box_" . $line . '_' . $roster_item->date_unix;

                $svg_box_text .= "<g id=$work_box_id class='work-box' "
                        . " onmousedown='roster_change_table_on_drag_of_bar_plot(evt)' "
                        . " data-line='$line' "
                        . " data-date_unix='$date_unix' "
                        . " data-employee_key='$employee_key' "
                        . " data-box_type='work_box' "
                        . " >\n"
                        . "<rect class='$employee_style_class' data-employee_key='$employee_key' x='$x_pos_box' y='$y_pos_box' width='$width' height='$this->bar_height' />";
                //$svg_box_text .= "\n    <text class='$employee_style_class' x='$x_pos_box' y='$y_pos_box'  text-anchor='middle' alignment-baseline='middle'>";
                $svg_box_text .= "\n    <text x='$x_pos_text' y='" . $y_pos_text . "'  alignment-baseline='middle'>";
                if (isset($workforce->List_of_employees[$employee_key]->last_name)) {
                    $svg_box_text .= $workforce->List_of_employees[$employee_key]->last_name;
                } else {
                    $svg_box_text .= gettext("Unknown employee") . ":" . $employee_key;
                }
                $svg_box_text .= "\n        <tspan alignment-baseline='inherit'>&nbsp$working_hours</tspan>";
                $svg_box_text .= "\n    </text>";
                $svg_box_text .= "</g>";

                $svg_box_text .= "<g class='break-box' "
                        . " onmousedown='roster_change_table_on_drag_of_bar_plot(evt)' "
                        . " data-box_type='break_box' "
                        . " data-line='$line' "
                        . " data-employee_key=$employee_key "
                        . " data-date_unix=$date_unix "
                        . "><!--This group exists, beacause the work_box also has to be in a group.--><rect id='$break_box_id' data-employee_key='$employee_key' "
                        . " x='$x_pos_break_box' y='$y_pos_box' width='$break_width' height='$this->bar_height' "
                        . " "
                        . "/><text><!--This text is intentionally left blank.--><text></g>\n";
                $line++;
            }
            $svg_text .= $svg_box_text;
            $svg_text .= "</g>\n";
        }
        $svg_text .= $this->draw_image_dienstplan_add_axis_labeling();
        $svg_text .= "</svg>\n";
        $svg_text .= "<script src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/js/drag-and-drop.js'></script>";
        return $svg_text;
    }

    private function draw_image_dienstplan_add_axis_labeling() {
        $svg_grid_text = "<!--Grid-->\n";

        for ($time = floor($this->first_start); $time <= ceil($this->last_end); $time = $time + 2) {
            $x_pos = $this->outer_margin_x + $this->inner_margin_x + ($time * $this->bar_width_factor);
            $x_pos_secondary = $x_pos + ($this->bar_width_factor / 1);
            $x_pos_text = $x_pos;
            $y_pos_text = $this->font_size;
            $y_pos_grid_start = $this->outer_margin_y;
            $y_pos_grid_end = $this->outer_margin_y + $this->svg_inner_height;
            $svg_grid_text .= "<line class='grid-line' x1='$x_pos' y1='$y_pos_grid_start' x2='$x_pos' y2='$y_pos_grid_end'  />\n";
            $svg_grid_text .= "<line class='grid-line-secondary' x1='$x_pos_secondary' y1='$y_pos_grid_start' x2='$x_pos_secondary' y2='$y_pos_grid_end' />\n";
            $svg_grid_text .= "<text x='$x_pos_text' y='$y_pos_text' font-family='sans-serif' font-size='$this->font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
            $svg_grid_text .= "<text x='$x_pos_text' y='$this->svg_outer_height' font-family='sans-serif' font-size='$this->font_size' alignment-baseline='ideographic' text-anchor='middle'> $time:00 </text>\n";
        }
        return $svg_grid_text;
    }

    private function set_start_end_times($Roster) {
        foreach ($Roster as $Roster_day_array) {
            foreach ($Roster_day_array as $roster_item) {
                if (NULL === $roster_item->duty_start_int) {
                    continue;
                }
                $duty_start_list[] = $roster_item->duty_start_int;
                $duty_end_list[] = $roster_item->duty_end_int;
            }
        }
        $this->first_start = min($duty_start_list) / 3600;
        $this->last_end = max($duty_end_list) / 3600;

        return NULL;
    }
}
