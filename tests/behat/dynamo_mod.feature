@mod @mod_dynamo @javascript
Feature: Test that teachers can add the dynamo activity and students can view the survey.
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
      | activity   | name                        | intro                              | course               | idnumber    |
      | dynamo     | dynamoTest1                 | dynamo description                 | C1                   | dynamo1     |

  Scenario: Teachers can add the dynamo activity
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "dynamo" to section "1"
    Then I should see "dynamoTest1"
