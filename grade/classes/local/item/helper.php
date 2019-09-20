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
 * Grade item, itemnumber mapping.
 *
 * @package   core_grades
 * @copyright Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types = 1);

namespace core_grades\local\item;

use code_grades\local\item\itemnumber_mapping;
use code_grades\local\item\advanced_grading_items;

/**
 * Grade item helpers.
 *
 * @package   core_grades
 * @copyright Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Get the grade itemnumber mapping for a component.
     *
     * @param string $component The component that the grade item belongs to
     * @return array
     */
    public static function get_mappings_for_component(string $component): array {
        $classname = "{$component}\\grades\gradeitems";

        if (!class_exists($classname)) {
            return [
                0 => '',
            ];
        }

        if (!is_subclass_of($classname, 'core_grades\local\item\itemnumber_mapping')) {
            throw new \coding_exception("The {$classname} class does not implement " . itemnumber_mapping::class);
        }

        return $classname::get_mappings();
    }

    /**
     * Get the suffixed field name for an activity field mapped from its itemnumber.
     *
     * For legacy reasons, the first itemnumber has no suffix on field names.
     *
     * @param string $component The component that the grade item belongs to
     * @param int $itemnumber The grade itemnumber
     * @param string $fieldname The name of the field to be rewritten
     * @return string The translated field name
     */
    public static function get_field_name_for_itemnumber(string $component, int $itemnumber, string $fieldname): string {
        $itemname = static::get_itemname_from_itemnumber($component, $itemnumber);

        if ($itemname) {
            return "{$fieldname}_{$itemname}";
        }

        return $fieldname;
    }

    /**
     * Get the suffixed field name for an activity field mapped from its itemnumber.
     *
     * For legacy reasons, the first itemnumber has no suffix on field names.
     *
     * @param string $component The component that the grade item belongs to
     * @param int $itemname The grade itemname
     * @param string $fieldname The name of the field to be rewritten
     * @return string The translated field name
     */
    public static function get_field_name_for_itemname(string $component, string $itemname, string $fieldname): string {
        if (empty($itemname)) {
            return $fieldname;
        }

        $itemnumber = static::get_itemnumber_from_itemname($component, $itemname);

        if ($itemnumber > 0) {
            return "{$fieldname}_{$itemname}";
        }

        return $fieldname;
    }

    /**
     * Get the itemname for an itemnumber.
     *
     * For legacy compatability when the itemnumber is 0, the itemname will always be empty.
     *
     * @param string $component The component that the grade item belongs to
     * @param int $itemnumber The grade itemnumber
     * @return int The grade itemnumber of the itemname
     */
    public static function get_itemname_from_itemnumber(string $component, int $itemnumber): string {
        if ($itemnumber === 0) {
            return '';
        }

        $mappings = self::get_mappings_for_component($component);

        if (isset($mappings[$itemnumber])) {
            return $mappings[$itemnumber];
        }

        if ($itemnumber >= 1000) {
            // An itemnumber >= 1000 belongs to an outcome.
            return '';
        }

        throw new \coding_exception("Unknown itemnumber mapping for {$itemnumber} in {$component}");
    }

    /**
     * Get the itemnumber for a item name.
     *
     * For legacy compatability when the itemname is empty, the itemnumber will always be 0.
     *
     * @param string $component The component that the grade item belongs to
     * @param string $itemname The grade itemname
     * @return int The grade itemname of the itemnumber
     */
    public static function get_itemnumber_from_itemname(string $component, string $itemname): int {
        if (empty($itemname)) {
            return 0;
        }

        $mappings = self::get_mappings_for_component($component);

        $flipped = array_flip($mappings);
        if (isset($flipped[$itemname])) {
            return $flipped[$itemname];
        }

        throw new \coding_exception("Unknown itemnumber mapping for {$itemname} in {$component}");
    }

    /**
     * Get the list of advanced grdaing areas for the named component.
     *
     * @param string $component
     * @return array
     */
    public static function get_advanced_grading_areas_for_component(string $component): array {
        $classname = "{$component}\\grades\gradeitems";

        if (!class_exists($classname)) {
            return [
                0 => '',
            ];
        }

        if (!is_subclass_of($classname, 'core_grades\local\item\advanced_grading_items')) {
            throw new \coding_exception("The {$classname} class does not implement " . advanced_grading_items::class);
        }

        return $classname::get_advanced_grading_areas();
    }
}
