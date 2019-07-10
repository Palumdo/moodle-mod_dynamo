@mod @mod_dynamo @javascript
Feature: Test that teachers can add the dynamo activity and students can view the survey.
  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | course | user | role |
      | C1     | teacher1 | editingteacher |
      | C1     | student1 | student |

  Scenario: Teachers can add the dynamo activity
    When I add a "Dynamo" to section "1" and I fill the form with:
      | Name | Test name |
      | Description | Test dynamo description |
      | ID number | TESTNAME |
    And I turn editing mode off
    Then I should not see "Adding a new"
    And I turn editing mode on
    And I open "Test name" actions menu
    And I click on "Edit settings" "link" in the "Test name" activity
    And I expand all fieldsets
    And the field "Name" matches value "Test name"
    And the field "ID number" matches value "TESTNAME"