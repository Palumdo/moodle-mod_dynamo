@mod @mod_dynamo
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
      | activity   | name         | intro                       | course | idnumber  | section |
      | dynamo     | Dynamo 1     | Test dynamo description     | C1     | dynamo1   | 0       |      
@javascript
  Scenario: Student can see the dynamo activity
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Dynamo 1"
    # The first activity won't have the previous activity link.
    Then "#prev-activity-link" "css_element" should not exist