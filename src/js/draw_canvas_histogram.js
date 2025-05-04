/*
 * Copyright (C) 2022 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

var array_of_keys;
class Plot {
    static outer_margin_x = 30;
    static outer_margin_y = 20;

    constructor(width, height) {
        this.canvas_width = width;
        this.canvas_height = height;
    }
}
window.addEventListener('load', function () {
    draw_canvas_histogram();
});

function draw_canvas_histogram() {
    var expectation_div = document.getElementById('expectation');
    if (!expectation_div) {
        /*
         * <p lang=de>Wir sind nicht auf der Seite upload-pep.php</p>
         */
        return;
    }
    var expectation_string = expectation_div.dataset.expectation;
    var expectation = JSON.parse(expectation_string);
    array_of_keys = Object.keys(expectation).map(parse_time_string_to_seconds);
    time_start = Math.min.apply(null, array_of_keys);
    time_end = Math.max.apply(null, array_of_keys);
    duration = time_end - time_start;
    var canvas = document.getElementById('canvasHistogram');
    var canvas_context = canvas.getContext('2d');
    var plot_object = new Plot(canvas.width, canvas.height);
    plot_object.max_height = Math.max.apply(null, Object.values(expectation));
    plot_object.time_start = time_start;
    plot_object.time_end = time_end;
    plot_object.width_factor = (plot_object.canvas_width - (Plot.outer_margin_x * 2)) / duration;
    plot_object.height_factor = (plot_object.canvas_height - (Plot.outer_margin_y * 2)) / plot_object.max_height;

    canvas_context.save();
    canvas_context.translate(0, canvas.height);
    canvas_context.scale(plot_object.width_factor, plot_object.height_factor);

    Object.entries(expectation).forEach(draw_line_to, plot_object);
    //expectation.foreach(draw_line_to, plot_object);
    canvas_context.strokeStyle = '#E6828B';
    canvas_context.lineWidth = 1;
    canvas_context.stroke();
    canvas_context.restore();
}

function draw_line_to(item, index) {
    let plot_object = this;
    let current_time_in_seconds = array_of_keys[index];
    let factor = item[1];
    let x_pos = (current_time_in_seconds - plot_object.time_start) + Plot.outer_margin_x / plot_object.width_factor;
    let y_pos = (factor * -1) - (Plot.outer_margin_y / plot_object.height_factor);
    let canvas = document.getElementById('canvasHistogram');
    let canvas_context = canvas.getContext('2d');
    canvas_context.lineTo(x_pos, y_pos);
}

function parse_time_string_to_seconds(time_string) {
    let time_object = new Date('1970-01-01T' + time_string + 'Z');
    let seconds = time_object.getUTCSeconds() + (60 * time_object.getUTCMinutes()) + (60 * 60 * time_object.getUTCHours());
    return seconds;
}
