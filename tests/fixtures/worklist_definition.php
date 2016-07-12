<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2016
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2016, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

return array(
    'def1' => array(
        'id' => 1,
        'name' => 'test',
        'rrule' => 'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR',
        'start_time' => '09:00',
        'end_time' => '17:00',
        'scheduled' => true,
    ),
    'def2' => array(
        'id' => 2,
        'name' => 'test 2',
        'rrule' => 'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR',
        'start_time' => '09:00',
        'end_time' => '12:00',
        'scheduled' => true,
    ),
);