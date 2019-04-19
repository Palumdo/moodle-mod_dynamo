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
 
// linked to student.php
$(".mystar").mouseover(function() {
  var value = $(this).data("value");
  var id    = $(this).data("id");
  $('[data-id='+id+']').css('color','#dddddd');     
  var element = $(this);
  for(i=value;i>=1;i--) {
    element.css('color','#d5d54c');
    element = element.prev();
  }
});

$(".mystar" ).mouseout(function() {
  var id = $(this).data("id");
  displayStar(id);
});

$(".mystar" ).click(function() {
  var value = $(this).data("value");
  var id    = $(this).data("id");
  $('#'+id).val(value);
  displayStar(id);
});

function displayStar(id) {
  var value = $('#'+id).val();
  $('[data-id='+id+']').css('color','#dddddd');       
  if(value != '') {
    for(i=1;i<=value;i++) {
      element = $('[data-id='+id+'][data-value='+i+']');
      element.css('color','#d5d54c');
      element = element.prev();
    }
  }
}

$('.hiddenval').each(function() {
  var id  = this.id;
  displayStar(id);
});  

 // crit6 '' or none grp 0 or 1... 
function validation(crit6, grp) {
 var error = 0;  
 var idList = '';
 // pairs
 jQuery('.saveme').each(function() {
   var currentElement  = $(this);
   var value           = currentElement.val(); 
   var id              = this.id;
   //console.log(id + ' - ' + value );
   if(id.indexOf('_6') != -1) { // 6th criteria for pairs
     if(crit6 == '' && value == 0) error++; 
   } else {
     if(value == 0) {error++;idList += id+"," } 
   }
 });

 // group
 if(grp == 1) {
   jQuery('.savemegrp').each(function() {
     var currentElement  = $(this);
     var value           = currentElement.val(); 
     var id              = this.id;
   
     if(id.indexOf('_6') != -1) { // 6th criteria for group
       if(crit6 == '' && value == 0) error++; 
     } else {
       if(value == 0) {error++;idList += id+"," }
     }
     
   });
 }

 // comments
 jQuery('.savemecom').each(function() {
   var currentElement  = $(this);
   var value           = currentElement.val(); 
   var id              = this.id;
 
   if(value == 0) error++; 
 });  
 
 
 if(error > 0) {  
   window.scrollTo(0, 0); 
   $("#errormsg").css('display','block');
   return false;
 }
 
 return true;
}   
// END OF JS specific to student.php

// JS specific to teacher.php
function numTable() {
  var nbLine = 0;
  $(".tablelvlx > tbody  > tr").each(function() {
    if($(this).css("display") != "none") {
      nbLine++;
      $(this).find("td").eq(7).html(nbLine);
    }
  });
}  

function hidenoprob() {
  var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
    if ( ($.trim($(this).find("td").eq(6).html())).search("fa-sun") > -1 ) return true;
    else return false;        
  }).toggle();
  numTable();
  
  var $climat = $("#main-overview .overview-group").filter(function () {
    if ( ($.trim($(this).html())).search("fa-sun") > -1 ) return true;
    else return false;        
  }).toggle();
  
}

function hidenotcomplete() {
  var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
   if (($.trim($(this).find("td").eq(1).html())).search("color:#ccc") > -1) return true;
   else return false;
  }).toggle();
  numTable();
  
  $("#main-overview .abstent").toggle();
}

function switchoverview() {
  $('#table-overview').toggle();
  $('#main-overview').toggle();
}


$(".tablelvlx tr").hover(function() {
  $(this).find("td").eq(7).css("background-color","#006DCC");
  $(this).find("td").eq(7).css("padding","15px;");
  $(this).find("td").eq(7).css("transition","all 200ms ease-in");
  $(this).find("td").eq(7).css("transform","scale(1.7)");
});

$(".tablelvlx tr").mouseleave(function() {
  $(this).find("td").eq(7).css("background-color","transparent");
  $(this).find("td").eq(7).css("padding","0;");
  $(this).find("td").eq(7).css("transition","all 200ms ease-in");
  $(this).find("td").eq(7).css("transform","scale(1.0)");
});


$(".report-yearbook-descr").hover(function() {
  $(this).css("transition","all 200ms ease-in");
  $(this).css("borderRadius","15px");
  $(this).css("fontSize","14px");
  $(this).css("transform","scale(1.7)");
  $(this).css("zIndex","1000");

});

$(".report-yearbook-descr").mouseleave(function() {
  $(this).css("transition","all 200ms ease-in");
  $(this).css("borderRadius","0");
  $(this).css("fontSize","20px");
  $(this).css("transform","scale(1.0)");
  $(this).css("zIndex","1");
});


$(".report-yearbook img").hover(function() {
  $(this).css("transition","all 200ms ease-in");
  $(this).css("transform","scale(1.7)");
  $(this).css("zIndex","1000");

});

$(".report-yearbook img").mouseleave(function() {
  $(this).css("transition","all 200ms ease-in");
  $(this).css("transform","scale(1.0)");
  $(this).css("zIndex","1");
});



