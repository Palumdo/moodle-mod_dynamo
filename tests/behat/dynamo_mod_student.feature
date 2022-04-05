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
    And the following "activities" exist:
      | activity   | name         | intro                       | course | idnumber  | section |
      | assign     | Assignment 1 | Test assignment description | C1     | assign1   | 0       |
@javascript
  Scenario: Student can see the dynamo activity
    Given I log in as "teacher1"
    # And I am on "Course 1" course homepage with editing mode on
    # When I add a "Dynamo" to section "1" and I fill the form with:
    #   | Name | Test name |
    #   | Description | Test dynamo description |
    # And I turn editing mode off
    # And I log out
    # Given I log in as "student1"
    # And I am on "Course 1" course homepage
    # And I follow "Test name"
    # Then "#prev-activity-link" "css_element" should not exist
    # And I should see "Assignment 1" in the "#prev-activity-link" "css_element"
    # Then I click on "Save" "button"
    # Then I should see "Save" in the "#dynamosave" "css_element"
