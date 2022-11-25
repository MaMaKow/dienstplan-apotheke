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

abstract class roster_image_histogramm {

    private static function draw_image_dienstplan_add_Expectation($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $start_time, $canvas_height, $Expectation) {
        $red = self::hex2rgb('#FF0000');
        $x_start = $outer_margin_x / $width_factor;
        $y_start = $outer_margin_y / $height_factor * -1;

        $canvas_text = "";
        $canvas_text .= "ctx.save();\n"; // = dot color
        $canvas_text .= "ctx.translate(0,$canvas_height);\n";
        $canvas_text .= "ctx.scale($width_factor, $height_factor);\n";

        $canvas_text .= "ctx.moveTo($x_start, $y_start);\n";
        foreach ($Expectation as $time => $packages) {
            $x_pos = (time_from_text_to_int($time) - $start_time) + $outer_margin_x / $width_factor;
            $y_pos = ($packages * -1) - ($outer_margin_y / $height_factor);
            $canvas_text .= "ctx.lineTo($x_pos, $y_pos);\n";
        }
        //$canvas_text .= $canvas_box_text;
        $canvas_text .= "ctx.lineTo($x_pos, $y_start);\n";
        $canvas_text .= "ctx.closePath();\n";
//    $canvas_text .= "ctx.stroke();\n";
        $canvas_text .= "ctx.fillStyle = 'rgba($red, 0.5)';\n";
        $canvas_text .= "ctx.fill();\n";
        $canvas_text .= "ctx.restore();\n";
        return $canvas_text;
    }

    private static function draw_image_dienstplan_add_axis_labeling($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $canvas_height, $start_time, $end_time) {
        $font_size = 16;
        $canvas_text = "";
        $canvas_text .= "ctx.save();\n"; // = dot color
        $canvas_text .= "ctx.translate(0,$canvas_height);\n";
        $canvas_text .= "ctx.strokeStyle = 'black';\n"; // = dot color
        $canvas_text .= "ctx.lineWidth=2;\n";
        $canvas_text .= "ctx.fillStyle = 'black';\n"; // = font color
        $canvas_text .= "ctx.font = '" . "$font_size" . "px sans-serif';\n";
        $canvas_text .= "ctx.textAlign = 'center';\n";
        for ($time = floor($start_time); $time <= ceil($end_time); $time = $time + 2) {
            $x_pos = ($time - $start_time) * $width_factor + $outer_margin_x;
            $x_pos_secondary = $x_pos + 1 * $width_factor;
            $y_pos = 0;
            $y_pos_line_start = (($outer_margin_y / $height_factor) + $font_size) * -1;
            $y_pos_line_end = -$canvas_height + ($outer_margin_y / $height_factor);
            $canvas_text .= "ctx.fillText('$time:00', '$x_pos', '$y_pos');\n";
            $canvas_text .= "ctx.beginPath();\n"
                    . "ctx.setLineDash([1, 8]);\n"
                    . "ctx.moveTo($x_pos, $y_pos_line_start);\n"
                    . "ctx.lineTo($x_pos, $y_pos_line_end);\n"
                    . "ctx.stroke();\n"
                    . "ctx.closePath();\n";
            $canvas_text .= "ctx.beginPath();\n"
                    . "ctx.setLineDash([1, 16]);\n"
                    . "ctx.moveTo($x_pos_secondary, $y_pos_line_start);\n"
                    . "ctx.lineTo($x_pos_secondary, $y_pos_line_end);\n"
                    . "ctx.stroke();\n"
                    . "ctx.closePath();\n";
        }
        $canvas_text .= "ctx.restore();\n";
        return $canvas_text;
    }

    private static function draw_image_dienstplan_add_headcount($outer_margin_x, $width_factor, $height_factor, $start_time, $Anwesende, $factor_employee, $canvas_height) {
        $y_pos_line_end = NULL;
        $canvas_text = "";
        $canvas_text .= "ctx.save();\n"; // = dot color
        $canvas_text .= "ctx.translate(0,$canvas_height);\n";
        $canvas_text .= "ctx.beginPath();\n";
        $canvas_text .= "ctx.setLineDash([]);\n";
        $canvas_text .= "ctx.lineWidth=5;\n";
        foreach ($Anwesende as $time_in_seconds => $anwesende) {
            $time_float = $time_in_seconds / 3600;
            if (NULL === $y_pos_line_end) {//the first round.
                $y_pos_line_end = $anwesende * $height_factor * -1 * $factor_employee;
            }
            $y_pos_line_start = $y_pos_line_end;
            $x_pos_line_end = ($time_float - $start_time) * $width_factor + $outer_margin_x;
            $y_pos_line_end = $anwesende * $height_factor * -1 * $factor_employee;

            $canvas_text .= "ctx.lineTo($x_pos_line_end, $y_pos_line_start);\n";
            $canvas_text .= "ctx.lineTo($x_pos_line_end, $y_pos_line_end);\n";
        }
        $green = self::hex2rgb('#73AC22');
        $canvas_text .= "ctx.strokeStyle = 'rgba($green, 0.5)';";
        $canvas_text .= "ctx.stroke();\n";
        $canvas_text .= "ctx.closePath();\n";
        $canvas_text .= "ctx.restore();\n";
        return $canvas_text;
    }

    /**
     *
     * @param array $Roster
     * @param int $branch_id
     * @param array $Anwesende
     * @param string $date_unix
     * @var float $factor_employee The number of drug packages that can be sold per employee within a certain time.
     * @return string The canvas element
     */
    public static function draw_image_histogramm(array $Roster, int $branch_id, array $Anwesende, int $date_unix) {
        if (empty($Anwesende)) {
            return FALSE;
        }
        if (Roster::is_empty($Roster)) {
            return FALSE;
        }
        $Expectation = roster_image_histogramm::get_expectation($date_unix, $branch_id);
        if (FALSE === $Expectation or array() === $Expectation) {
            return FALSE;
        }

        $factor_employee = 6;
        $canvas_width = 650;
        $canvas_height = 300;

//    $inner_margin_x = $bar_height * 0.2;
//    $inner_margin_y = $inner_margin_x;
        $outer_margin_x = 30;
        $outer_margin_y = 20;
        foreach ($Roster as $Roster_day) {
            foreach ($Roster_day as $roster_item_object) {
                $Start_times[] = $roster_item_object->duty_start_int;
                $End_times[] = $roster_item_object->duty_end_int;
            }
        }
        $start_time = min($Start_times) / 3600;
        $end_time = max($End_times) / 3600;
        $duration = $end_time - $start_time;
        $width_factor = ($canvas_width - ($outer_margin_x * 2)) / $duration;
        $max_work_load = max($Expectation);
        $max_workforce = max($Anwesende) * $factor_employee;
        $max_height = max($max_work_load, $max_workforce);
        $height_factor = ($canvas_height - ($outer_margin_y * 2)) / $max_height;


        $canvas_text = "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height' >\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
        $canvas_text .= "<script>\n";
        $canvas_text .= "var c = document.getElementById('canvas_histogram');\n";
        $canvas_text .= "var ctx = c.getContext('2d');\n";

        /*
         * TODO: Maybe add a line that represents the division of packages by ((pharmaceutical) employee-1)?
         * That would give a feeling of to much or to less people in the roster.
         * It could be inside a corridor of wished factor.
         */
        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_Expectation($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $start_time, $canvas_height, $Expectation);
        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_headcount($outer_margin_x, $width_factor, $height_factor, $start_time, $Anwesende, $factor_employee, $canvas_height);
        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_axis_labeling($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $canvas_height, $start_time, $end_time);

        $canvas_text .= "</script>\n";
//header("Content-type: image/canvas+xml");
        return $canvas_text;
    }

    private static function get_expectation($date_unix, $branch_id) {
        $Packungen = array();
        $Expectation = array();
        $factor_tag_im_monat = 1;
        $factor_monat_im_jahr = 1;
        /*
         * echo roster_image_histogramm::check_timeliness_of_pep_data();
         */
        $sql_weekday = date('N', $date_unix) - 1;
        $month_day = date('j', $date_unix);
        $month = date('n', $date_unix);

        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $branch_pep_id = $List_of_branch_objects[$branch_id]->PEP;
        if (empty($branch_pep_id)) {
            return FALSE;
        }
        $result = database_wrapper::instance()->run("SELECT Uhrzeit, Mittelwert FROM `pep_weekday_time`  WHERE Mandant = :branch_pep_id and Wochentag = :weekday", array('branch_pep_id' => $branch_pep_id, 'weekday' => $sql_weekday));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $Packungen[$row->Uhrzeit] = $row->Mittelwert;
        }
        $result = database_wrapper::instance()->run("SELECT factor FROM `pep_month_day`  WHERE `branch` = :branch_pep_id and `day` = :month_day", array('branch_pep_id' => $branch_pep_id, 'month_day' => $month_day));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $factor_tag_im_monat = $row->factor;
        }

        $result = database_wrapper::instance()->run("SELECT factor FROM `pep_year_month`  WHERE `branch` = :branch_pep_id and `month` = :month", array('branch_pep_id' => $branch_pep_id, 'month' => $month));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $factor_monat_im_jahr = $row->factor;
        }
        foreach ($Packungen as $time => $average) {
            $Expectation[$time] = $average * $factor_monat_im_jahr * $factor_tag_im_monat;
        }
        return $Expectation;
    }

    private static function hex2rgb($hexstring) {
        $hex = str_replace("#", "", $hexstring);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb); // returns the rgb values separated by commas
        //return $rgb; // returns an array with the rgb values
    }

}
