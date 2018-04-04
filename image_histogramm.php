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
        $red = hex2rgb('#FF0000');
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
        $green = hex2rgb('#73AC22');
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
     * @param string $datum
     * @var float $factor_employee The number of drug packages that can be sold per employee within a certain time.
     * @return string The canvas element
     */
    public static function draw_image_histogramm($Roster, $branch_id, $Anwesende, $datum) {
        $factor_employee = 6;
        if (empty($Anwesende)) {
            return FALSE;
        }
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
        $Expectation = roster_image_histogramm::get_expectation($datum, $branch_id);

        $max_work_load = max($Expectation);
        $max_workforce = max($Anwesende) * $factor_employee;
        $max_height = max($max_work_load, $max_workforce);
        $height_factor = ($canvas_height - ($outer_margin_y * 2)) / $max_height;


        $canvas_text = "<canvas id='canvas_histogram' width='$canvas_width' height='$canvas_height' >\n Your browser does not support the HTML5 canvas tag.\n </canvas>\n";
        $canvas_text .= "<script>\n";
        $canvas_text .= "var c = document.getElementById('canvas_histogram');\n";
        $canvas_text .= "var ctx = c.getContext('2d');\n";

        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_Expectation($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $start_time, $canvas_height, $Expectation);
        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_headcount($outer_margin_x, $width_factor, $height_factor, $start_time, $Anwesende, $factor_employee, $canvas_height);
        $canvas_text .= roster_image_histogramm::draw_image_dienstplan_add_axis_labeling($outer_margin_x, $outer_margin_y, $width_factor, $height_factor, $canvas_height, $start_time, $end_time);

        $canvas_text .= "</script>\n";
//header("Content-type: image/canvas+xml");
        return $canvas_text;
    }

    private static function get_expectation($datum, $branch_id) {
        global $List_of_branch_objects;
        /*
          if (basename($_SERVER["SCRIPT_FILENAME"]) === 'tag-in.php') {
          echo roster_image_histogramm::check_timeliness_of_pep_data();
          }
         */
        $unix_datum = strtotime($datum);
        $sql_weekday = date('N', $unix_datum) - 1;
        $month_day = date('j', $unix_datum);
        $month = date('n', $unix_datum);

        $branch_pep_id = $List_of_branch_objects[$branch_id]->PEP;
        if (empty($branch_pep_id)) {
            return FALSE;
        }

        $result = mysqli_query_verbose("SELECT Uhrzeit, Mittelwert FROM `pep_weekday_time`  WHERE Mandant = $branch_pep_id and Wochentag = $sql_weekday");
        while ($row = mysqli_fetch_object($result)) {
            $Packungen[$row->Uhrzeit] = $row->Mittelwert;
        }
        $result = mysqli_query_verbose("SELECT factor FROM `pep_month_day`  WHERE `branch` = $branch_pep_id and `day` = $month_day");
        $row = mysqli_fetch_object($result);
        $factor_tag_im_monat = $row->factor;

        $result = mysqli_query_verbose("SELECT factor FROM `pep_year_month`  WHERE `branch` = $branch_pep_id and `month` = $month");
        $row = mysqli_fetch_object($result);
        $factor_monat_im_jahr = $row->factor;

        foreach ($Packungen as $time => $average) {
            $Expectation[$time] = $average * $factor_monat_im_jahr * $factor_tag_im_monat;
        }
        return $Expectation;
    }

    /*
      private static function check_timeliness_of_pep_data() {
      //Check if the PEP information is still up-to-date:
      $sql_query = "SELECT max(Datum) as Datum FROM `pep`";
      $result = mysqli_query_verbose($sql_query);
      $row = mysqli_fetch_object($result);
      $newest_pep_date = strtotime($row->Datum);
      $today = time();
      $seconds_since_last_update = $today - $newest_pep_date;
      if ($seconds_since_last_update >= 60 * 60 * 24 * 30 * 3) { //3 months
      $timeliness_warning_html = "<br><div class=warningmsg>Die PEP Information ist veraltet. <br>"
      . "Letzter Eintrag " . date('d.m.Y', strtotime($row->Datum)) . ". <br>"
      . "Bitte neue PEP-Datei <a href=upload-in.php>hochladen</a>!</div><br>\n";
      return $timeliness_warning_html;
      }
      }
     */
}
