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
 * Behat course-related steps definitions.
 *
 * @package    core_course
 * @category   test
 * @copyright  2012 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Course-related steps definitions.
 *
 * @package    core_course
 * @category   test
 * @copyright  2012 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_course extends behat_base {

    /**
     * Turns editing mode on.
     * @Given /^I turn editing mode on$/
     */
    public function i_turn_editing_mode_on() {
        return new Given('I press "Turn editing on"');
    }

    /**
     * Turns editing mode off.
     * @Given /^I turn editing mode off$/
     */
    public function i_turn_editing_mode_off() {
        return new Given('I press "Turn editing off"');
    }

    /**
     * Creates a new course with the provided table data matching course settings names with the desired values.
     *
     * @Given /^I create a course with:$/
     * @param TableNode $table The course data
     */
    public function i_create_a_course_with(TableNode $table) {
        return array(
            new Given('I go to the courses management page'),
            new Given('I press "Add a new course"'),
            new Given('I fill the moodle form with:', $table),
            new Given('I press "Save changes"')
        );
    }

    /**
     * Goes to the system courses/categories management page.
     *
     * @Given /^I go to the courses management page$/
     */
    public function i_go_to_the_courses_management_page() {

        return array(
            new Given('I am on homepage'),
            new Given('I expand "Site administration" node'),
            new Given('I expand "Courses" node'),
            new Given('I follow "Add/edit courses"'),
        );
    }

    /**
     * Adds the selected activity/resource filling the form data with the specified field/value pairs.
     *
     * @When /^I add a "(?P<activity_or_resource_name_string>(?:[^"]|\\")*)" to section "(?P<section_number>\d+)" and I fill the form with:$/
     * @param string $activity The activity name
     * @param string $section The section number
     * @param TableNode $data The activity field/value data
     */
    public function i_add_to_section_and_i_fill_the_form_with($activity, $section, TableNode $data) {

        return array(
            new Given('I add a "'.$activity.'" to section "'.$section.'"'),
            new Given('I fill the moodle form with:', $data),
            new Given('I press "Save and return to course"')
        );
    }

    /**
     * Opens the activity chooser and opens the activity/resource form page.
     *
     * @Given /^I add a "(?P<activity_or_resource_name_string>(?:[^"]|\\")*)" to section "(?P<section_number>\d+)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $activity
     * @param string $section
     */
    public function i_add_to_section($activity, $section) {

        $sectionxpath = "//*[@id='section-" . $section . "']";

        if ($this->running_javascript()) {

            // Clicks add activity or resource section link.
            $sectionxpath = $sectionxpath . "/descendant::div[@class='section-modchooser']/span/a";
            $sectionnode = $this->find('xpath', $sectionxpath);
            $sectionnode->click();

            // Clicks the selected activity if it exists.
            $activity = ucfirst($activity);
            $activityxpath = "//div[@id='chooseform']/descendant::label
/descendant::span[contains(concat(' ', @class, ' '), ' typename ')][contains(.,'" . $activity . "')]
/parent::label/child::input";
            $activitynode = $this->find('xpath', $activityxpath);
            $activitynode->doubleClick();

        } else {
            // Without Javascript.

            // Selecting the option from the select box which contains the option.
            $selectxpath = $sectionxpath . "/descendant::div[contains(concat(' ', @class, ' '), ' section_add_menus ')]
/descendant::select[contains(., '" . $activity . "')]";
            $selectnode = $this->find('xpath', $selectxpath);
            $selectnode->selectOption($activity);

            // Go button.
            $gobuttonxpath = $selectxpath . "/ancestor::form/descendant::input[@type='submit']";
            $gobutton = $this->find('xpath', $gobuttonxpath);
            $gobutton->click();
        }

    }

    /**
     * Turns course section highlighting on.
     *
     * @Given /^I turn section "(?P<section_number>\d+)" highlighting on$/
     * @param int $sectionnumber The section number
     */
    public function i_turn_section_highlighting_on($sectionnumber) {

        // Ensures the section exists.
        $xpath = $this->section_exists($sectionnumber);

        return array(
            new Given('I click on "' . get_string('markthistopic') . '" "link" in the "' . $xpath . '" "xpath_element"'),
            new Given('I wait "2" seconds')
        );
    }

    /**
     * Turns course section highlighting off.
     *
     * @Given /^I turn section "(?P<section_number>\d+)" highlighting off$/
     * @param int $sectionnumber The section number
     */
    public function i_turn_section_highlighting_off($sectionnumber) {

        // Ensures the section exists.
        $xpath = $this->section_exists($sectionnumber);

        return array(
            new Given('I click on "' . get_string('markedthistopic') . '" "link" in the "' . $xpath . '" "xpath_element"'),
            new Given('I wait "2" seconds')
        );
    }

    /**
     * Shows the specified hidden section. You need to be in the course page and on editing mode.
     *
     * @Given /^I show section "(?P<section_number>\d+)"$/
     * @param int $sectionnumber
     */
    public function i_show_section($sectionnumber) {
        $showicon = $this->show_section_icon_exists($sectionnumber);
        $showicon->click();

        // It requires time.
        $this->getSession()->wait(5000, false);
    }

    /**
     * Hides the specified visible section. You need to be in the course page and on editing mode.
     *
     * @Given /^I hide section "(?P<section_number>\d+)"$/
     * @param int $sectionnumber
     */
    public function i_hide_section($sectionnumber) {
        $hideicon = $this->hide_section_icon_exists($sectionnumber);
        $hideicon->click();

        // It requires time.
        $this->getSession()->wait(5000, false);
    }

    /**
     * Checks if the specified course section hightlighting is turned on. You need to be in the course page on editing mode.
     *
     * @Then /^section "(?P<section_number>\d+)" should be highlighted$/
     * @throws ExpectationException
     * @param int $sectionnumber The section number
     */
    public function section_should_be_highlighted($sectionnumber) {

        // Ensures the section exists.
        $xpath = $this->section_exists($sectionnumber);

        // The important checking, we can not check the img.
        $xpath = $xpath . "/descendant::img[@alt='" . get_string('markedthistopic') . "'][contains(@src, 'marked')]";
        $exception = new ExpectationException('The "' . $sectionnumber . '" section is not highlighted', $this->getSession());
        $this->find('xpath', $xpath, $exception);
    }

    /**
     * Checks if the specified course section highlighting is turned off. You need to be in the course page on editing mode.
     *
     * @Then /^section "(?P<section_number>\d+)" should not be highlighted$/
     * @throws ExpectationException
     * @param int $sectionnumber The section number
     */
    public function section_should_not_be_highlighted($sectionnumber) {

        // We only catch ExpectationException, ElementNotFoundException should be thrown if the specified section does not exist.
        try {
            $this->section_should_be_highlighted($sectionnumber);
        } catch (ExpectationException $e) {
            // ExpectedException means that it is not highlighted.
            return;
        }

        throw new ExpectationException('The "' . $sectionnumber . '" section is highlighted', $this->getSession());
    }

    /**
     * Checks that the specified section is visible. You need to be in the course page. It can be used being logged as a student and as a teacher on editing mode.
     *
     * @Then /^section "(?P<section_number>\d+)" should be hidden$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param int $sectionnumber
     */
    public function section_should_be_hidden($sectionnumber) {

        $sectionxpath = $this->section_exists($sectionnumber);

        // Section should be hidden.
        $exception = new ExpectationException('The section is not hidden', $this->getSession());
        $this->find('xpath', $sectionxpath . "[contains(concat(' ', @class, ' '), ' hidden ')]", $exception);

        // The checking are different depending on user permissions.
        if ($this->is_course_editor()) {

            // The section must be hidden.
            $this->show_section_icon_exists($sectionnumber);

            // If there are activities they should be hidden and the visibility icon should not be available.
            if ($activities = $this->get_section_activities($sectionxpath)) {

                $dimmedexception = new ExpectationException('There are activities that are not dimmed', $this->getSession());
                $visibilityexception = new ExpectationException('There are activities which visibility icons are clickable', $this->getSession());
                foreach ($activities as $activity) {

                    // Dimmed.
                    $this->find('xpath', "//div[contains(concat(' ', @class, ' '), ' activityinstance ')]
/a[contains(concat(' ', @class, ' '), ' dimmed ')]", $dimmedexception, $activity);

                    // To check that the visibility is not clickable we check the funcionality rather than the applied style.
                    $visibilityiconnode = $this->find('css', 'a.editing_show img', false, $activity);
                    $visibilityiconnode->click();

                    // We ensure that we still see the show icon.
                    $visibilityiconnode = $this->find('css', 'a.editing_show img', $visibilityexception, $activity);
                }
            }

        } else {
            // There shouldn't be activities.
            if ($this->get_section_activities($sectionxpath)) {
                throw new ExpectationException('There are activities in the section and they should be hidden', $this->getSession());
            }
        }
    }

    /**
     * Checks that the specified section is visible. You need to be in the course page. It can be used being logged as a student and as a teacher on editing mode.
     *
     * @Then /^section "(?P<section_number>\d+)" should be visible$/
     * @throws ExpectationException
     * @param int $sectionnumber
     */
    public function section_should_be_visible($sectionnumber) {

        $sectionxpath = $this->section_exists($sectionnumber);

        // Section should not be hidden.
        if (!$this->getSession()->getPage()->find('xpath', $sectionxpath . "[not(contains(concat(' ', @class, ' '), ' hidden '))]")) {
            throw new ExpectationException('The section is hidden', $this->getSession());
        }

        // Hide section button should be visible.
        if ($this->is_course_editor()) {
            $this->hide_section_icon_exists($sectionnumber);
        }
    }

    /**
     * Checks that the specified activity is visible. You need to be in the course page. It can be used being logged as a student and as a teacher on editing mode.
     *
     * @Then /^"(?P<activity_or_resource_string>(?:[^"]|\\")*)" activity should be visible$/
     * @param string $activityname
     */
    public function activity_should_be_visible($activityname) {

        // The activity must exists and be visible.
        $activitynode = $this->get_activity_node($activityname);

        if ($this->is_course_editor()) {

            // The activity should not be dimmed.
            try {
                $this->find('css', 'a.dimmed', false, $activitynode);
                throw new ExpectationException('"' . $activityname . '" is hidden', $this->getSession());
            } catch (ElementNotFoundException $e) {
                // All ok.
            }

            // The 'Hide' button should be available.
            $nohideexception = new ExpectationException('"' . $activityname . '" don\'t have a "' . get_string('hide') . '" icon', $this->getSession());
            $this->find('named', array('link', get_string('hide')), $nohideexception, $activitynode);
        }
    }

    /**
     * Checks that the specified activity is hidden. You need to be in the course page. It can be used being logged as a student and as a teacher on editing mode.
     *
     * @Then /^"(?P<activity_or_resource_string>(?:[^"]|\\")*)" activity should be hidden$/
     * @param string $activityname
     */
    public function activity_should_be_hidden($activityname) {

        if ($this->is_course_editor()) {

            // The activity should exists.
            $activitynode = $this->get_activity_node($activityname);

            // Should be hidden.
            $exception = new ExpectationException('"' . $activityname . '" is not dimmed', $this->getSession());
            $this->find('css', 'a.dimmed', $exception, $activitynode);

            // Also 'Show' icon.
            $noshowexception = new ExpectationException('"' . $activityname . '" don\'t have a "' . get_string('show') . '" icon', $this->getSession());
            $this->find('named', array('link', get_string('show')), $noshowexception, $activitynode);

        } else {

            // It should not exists at all.
            try {
                $this->find_link($activityname);
                throw new ExpectationException('The "' . $activityname . '" should not appear');
            } catch (ElementNotFoundException $e) {
                // This is good, the activity should not be there.
            }
        }

    }

    /**
     * Checks if the course section exists.
     *
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param int $sectionnumber
     * @return string The xpath of the section.
     */
    protected function section_exists($sectionnumber) {

        // Just to give more info in case it does not exist.
        $xpath = "//li[@id='section-" . $sectionnumber . "']";
        $exception = new ElementNotFoundException($this->getSession(), "Section $sectionnumber ");
        $this->find('xpath', $xpath, $exception);

        return $xpath;
    }

    /**
     * Returns the show section icon or throws an exception.
     *
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param int $sectionnumber
     * @return NodeElement
     */
    protected function show_section_icon_exists($sectionnumber) {

        // Gets the section xpath and ensure it exists.
        $xpath = $this->section_exists($sectionnumber);

        // We need to know the course format as the text strings depends on them.
        $courseformat = $this->get_course_format();

        // Checking the show button alt text and show icon.
        $xpath = $xpath . "/descendant::a/descendant::img[@alt='". get_string('showfromothers', $courseformat) ."'][contains(@src, 'show')]";

        $exception = new ElementNotFoundException($this->getSession(), 'Show section icon ');
        return $this->find('xpath', $xpath, $exception);
    }

    /**
     * Returns the hide section icon link if it exists or throws exception.
     *
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param int $sectionnumber
     * @return NodeElement
     */
    protected function hide_section_icon_exists($sectionnumber) {

        // Gets the section xpath and ensure it exists.
        $xpath = $this->section_exists($sectionnumber);

        // We need to know the course format as the text strings depends on them.
        $courseformat = $this->get_course_format();

        // Checking the hide button alt text and hide icon.
        $xpath = $xpath . "/descendant::a/descendant::img[@alt='". get_string('hidefromothers', $courseformat) ."'][contains(@src, 'hide')]";

        $exception = new ElementNotFoundException($this->getSession(), 'Hide section icon ');
        return $this->find('xpath', $xpath, $exception);
    }

    /**
     * Gets the current course format.
     *
     * @throws ExpectationException If we are not in the course view page.
     * @return string The course format in a frankenstyled name.
     */
    protected function get_course_format() {

        $exception = new ExpectationException('You are not in a course page', $this->getSession());

        // The moodle body's id attribute contains the course format.
        $node = $this->getSession()->getPage()->find('css', 'body');
        if (!$node) {
            throw $exception;
        }

        if (!$bodyid = $node->getAttribute('id')) {
            throw $exception;
        }

        if (strstr($bodyid, 'page-course-view-') === false) {
            throw $exception;
        }

        return 'format_' . str_replace('page-course-view-', '', $bodyid);
    }

    /**
     * Gets the section's activites DOM nodes.
     *
     * @param string $sectionxpath
     * @return array NodeElement instances
     */
    protected function get_section_activities($sectionxpath) {

        $xpath = $sectionxpath . "/descendant::li[contains(concat(' ', @class, ' '), ' activity ')]";

        // We spin here, as activities usually require a lot of time to load.
        try {
            $activities = $this->find_all('xpath', $xpath);
        } catch (ElementNotFoundException $e) {
            return false;
        }

        return $activities;
    }

    /**
     * Returns the DOM node of the activity from <li>.
     *
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $activityname The activity name
     * @return NodeElement
     */
    protected function get_activity_node($activityname) {

        $activityname = str_replace("'", "\'", $activityname);
        $xpath = "//li[contains(concat(' ', @class, ' '), ' activity ')][contains(., '" .$activityname. "')]";

        return $this->find('xpath', $xpath);
    }

    /**
     * Returns whether the user can edit the course contents or not.
     *
     * @return bool
     */
    protected function is_course_editor() {

        // We don't need to behat_base::spin() here as all is already loaded.
        if (!$this->getSession()->getPage()->findButton('Turn editing off') &&
                !$this->getSession()->getPage()->findButton('Turn editing on')) {
            return false;
        }

        return true;
    }

}
