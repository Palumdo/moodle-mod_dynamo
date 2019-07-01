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
 * This manage the UI of the student to make a look a like five stars 
 * rating system
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Click on user ico to go to their specific report 
$(".fa-user").click(function() {
    var usrid = $(this).data("id");
    var groupid = $(this).data("group");
    var activityid = document.getElementById("activityid").value;
    location.href='view.php?id=' + activityid + '&groupid=' + groupid + '&usrid=' + usrid + '&tab=2&results=3';
    event.stopPropagation();
});
//**************************************************************************
window.onload = function () {
  // reset the check box value on refresh !  
  var checkboxes = document.getElementsByTagName("input");
  for (var i = 0; i < checkboxes.length; i++)  {
    if (checkboxes[i].type == "checkbox")   {
      checkboxes[i].checked = false;
    }
  }
  
  $("#button-list-teacher").css("display","flex");
  $("#pleasewait").css("display","none");
  numTable();
  // Manage the climat sorting
  if(getAllUrlParams().tab == 2) {
    var sort = localStorage.getItem("sort_climat");
    console.log(sort);
    if(sort != '') {
        $("th:nth-child(8)").click();
    }
    
    if(sort == "false") {
        $("th:nth-child(8)").click();
    }
  } 
};
//**************************************************************************
// Allow to sort on the climat between student in the group
$("th:nth-child(8)").click(function(){
    var table = $(this).parents("table").eq(0);
    var rows = table.find("tr:gt(0)").toArray().sort(comparer($(this).index()));
    this.asc = !this.asc;
    localStorage.setItem("sort_climat", this.asc);
    if (!this.asc){rows = rows.reverse();}
    for (var i = 0; i < rows.length; i++){table.append(rows[i]);}
    numTable();
});
//**************************************************************************
function comparer(index) {
    return function(a, b) {
        var valA = getCellValue(a, index);
        var valB = getCellValue(b, index);
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
    }
}      
//**************************************************************************
function getCellValue(row, index){ return $(row).children("td").eq(index).text(); }
//**************************************************************************