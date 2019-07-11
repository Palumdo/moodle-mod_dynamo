@mod @mod_dynamo
Feature: Test that students can view the survey.
  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | course | user     | role           |
      | C1     | teacher1 | editingteacher |
      | C1     | student1 | student        |
@javascript
  Scenario: Student can see the dynamo activity
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I add a "Dynamo" to section "0" and I fill the form with:
      | Name | Test name |
      | Description | Test dynamo description |
    And I turn editing mode off
    And I log out
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test name"
    And I press "Save"
    # Then "#prev-activity-link" "css_element" should not exist
    