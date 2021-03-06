<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of collaborate
 *
 * @package    mod_collaborate
 * @copyright  202 Richard Jones richardnz@outlook.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see https://github.com/moodlehq/moodle-mod_collaborate
 * @see https://github.com/justinhunt/moodle-mod_collaborate
 */

namespace mod_collaborate\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;
use context_module;

/**
 * collaborate: Create a new view page renderable object
 *
 * @param string title - intro page title.
 * @param int height - course module id.
 * @copyright  2020 Richard Jones <richardnz@outlook.com>
 */

class showpage implements renderable, templatable {

    protected $collaborate;
    protected $id;
    protected $context;

    public function __construct($collaborate, $cm, $page) {
        $this->collaborate = $collaborate;
        $this->cm = $cm;
        $this->page = $page;
        $this->context = \context_module::instance($this->cm->id);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {

        $data = new stdClass();

        $data->heading = $this->collaborate->title;

        // $data->user = 'User: ' . strtoupper($this->page);
        $data->user = get_string('student_name', 'mod_collaborate', strtoupper($this->page));

        // Get the content from the database.
        $content = ($this->page == 'a') ? $this->collaborate->instructionsa : $this->collaborate->instructionsb;

        $filearea = 'instructions' . $this->page;
        $content = file_rewrite_pluginfile_urls(
            $content,
            'pluginfile.php',
            $this->context->id,
            'mod_collaborate',
            $filearea,
            $this->collaborate->id
        );

        // Run the content through format_text to enable streaming video etc.
        $formatoptions = new stdClass;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $this->context;
        $format = ($this->page == 'a') ? $this->collaborate->instructionsaformat : $this->collaborate->instructionsbformat;
        $data->body = format_text($content, $format, $formatoptions);

        // Get a return url back to view page.
        $urlv = new \moodle_url('/mod/collaborate/view.php', ['id' => $this->cm->id]);
        $data->url_view = $urlv->out(false);

        return $data;
    }
}