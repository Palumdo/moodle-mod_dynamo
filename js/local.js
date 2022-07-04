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
 
// linked to student

// Evaluation system by star 
$(".mystar").mouseover(function() {
    var value = $(this).data("value");
    var id = $(this).data("id");
    $('[data-id='+id+']').css('color','#dddddd');     
    var element = $(this);
    for(i = value;i >= 1; i--) {
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
    var id = $(this).data("id");
    $('#' + id).val(value);
    displayStar(id);
});

function displayStar(id) {
    var value = $('#' + id).val();
    $('[data-id=' + id + ']').css('color','#dddddd');       
    if(value != '') {
        for(i = 1; i<= value; i++) {
            element = $('[data-id='+id+'][data-value='+i+']');
            element.css('color','#d5d54c');
            element = element.prev();
        }
    }
}

// display the stars based on the value (real evaluation)
$('.hiddenval').each(function() {
    var id  = this.id;
    displayStar(id);
});  

 // crit6 '' or none grp 0 or 1... 
function validation(crit6, grp) {
    var error = 0;  
    var idList = '';
    // peers
    jQuery('.saveme').each(function() {
        var currentElement = $(this);
        var value = currentElement.val(); 
        var id = this.id;

        if(id.indexOf('_6') != -1) { // 6th criteria for peers
            if(crit6 == '' && value == 0) {
                error++; 
            }
        } else {
            if(value == 0) {
                error++;
                idList += id + ","; 
            } 
        }
    });

    // group
    if(grp == 1) {
        jQuery('.savemegrp').each(function() {
            var currentElement = $(this);
            var value = currentElement.val(); 
            var id = this.id;
   
            if(id.indexOf('_6') != -1) { // 6th criteria for group
                if(crit6 == '' && value == 0) {
                    error++; 
                }
            } else {
                if(value == 0) {
                    error++;
                    idList += id + ",";
                }
            }
     
        });
    }

    // comments
    jQuery('.savemecom').each(function() {
        var currentElement = $(this);
        var value = currentElement.val();
        var id = this.id;

        if(value == 0) {
            error++;
        }
    });  
 
    if(error > 0) {
        window.scrollTo(0, 0); 
        $("#errormsg").css('display','block');
        return false;
    }
 
    return true;
}
// END OF JS specific to student

// JS specific to teacher

// Add row number to the global view result
function numTable() {
    var nbLine = 0;
    $(".tablelvlx > tbody  > tr").each(function() {
        if($(this).css("display") != "none") {
            nbLine++;
            $(this).find("td").eq(8).html(nbLine);
        }
    });
}

// Hide row with no problem in the global view result
function hidenoprob() {
    var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
        if ( ($.trim($(this).find("td").eq(7).html())).search("fa-sun") > -1 ) {
            return true;
        } else {
            return false;
        }
    }).toggle();
    numTable();
  
    var $climat = $("#main-overview .overview-group").filter(function () {
        if (($.trim($(this).html())).search("fa-sun") > -1 ) {
            return true;
        } else {
            return false;
        }
    }).toggle();
}

// Hide rows with missing answers in the global view result
function hidenotcomplete() {
    var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
        if (($.trim($(this).find("td").eq(1).html())).search("color:#ccc") > -1) {
            return true;
        } else {
            return false;
        }
    }).toggle();
    numTable();

    $("#main-overview .abstent").toggle();
}

// Swith display in the global view result (table to di, div to table...)
function switchoverview() {
    $('#table-overview').toggle();
    $('#main-overview').toggle();
}

// Zoom effect on the global view result
$(".tablelvlx tr").hover(function() {
    $(this).find("td").eq(8).css("background-color", "#006DCC");
    $(this).find("td").eq(8).css("padding", "15px;");
    $(this).find("td").eq(8).css("transition", "all 200ms ease-in");
    $(this).find("td").eq(8).css("transform", "scale(1.7)");
});

$(".tablelvlx tr").mouseleave(function() {
    $(this).find("td").eq(8).css("background-color", "transparent");
    $(this).find("td").eq(8).css("padding", "0");
    $(this).find("td").eq(8).css("transition", "all 200ms ease-in");
    $(this).find("td").eq(8).css("transform", "scale(1.0)");
});

// Animation effect on yearbook report
$(".report-yearbook-descr").hover(function() {
    $(this).css("transition", "all 200ms ease-in");
    $(this).css("borderRadius", "15px");
    $(this).css("fontSize", "14px");
    $(this).css("transform", "scale(1.7)");
    $(this).css("zIndex", "1000");
});

$(".report-yearbook-descr").mouseleave(function() {
    $(this).css("transition", "all 200ms ease-in");
    $(this).css("borderRadius", "0");
    $(this).css("fontSize", "20px");
    $(this).css("transform", "scale(1.0)");
    $(this).css("zIndex", "1");
});

$(".report-yearbook img").hover(function() {
    $(this).css("transition", "all 200ms ease-in");
    $(this).css("transform", "scale(2.2)");
    $(this).css("zIndex", "1000");
});

$(".report-yearbook img").mouseleave(function() {
    $(this).css("transition", "all 200ms ease-in");
    $(this).css("transform", "scale(1.0)");
    $(this).css("zIndex", "1");
});
// End of animation

// Reports functions 

// Reload the page with the selected report
function reloadme(val) {
    var usrid = document.getElementById("usrid").value;
    var groupid = document.getElementById("groupid").value;
    var activityid = document.getElementById("activityid").value;
    location.href = 'view.php?id=' + activityid + '&groupid=' + groupid + '&usrid=' + usrid + '&report=' + val + '&tab=3';
}

// Change the zoom level in the report relative insurances
function reloadZoom(zoom) {
    val = zoom;
    if(val < 0) val = 0;
    if(val > 3) val = 4;
    var usrid = document.getElementById("usrid").value;
    var groupid = document.getElementById("groupid").value;
    var activityid = document.getElementById("activityid").value;
    location.href = 'view.php?id=' + activityid + '&groupid=' + groupid + '&usrid=' + usrid + '&report=4&tab=3&zoom=' + val;
}

// Go to the selected group
function gototag(obj) {
    val = $(obj).children(":selected").attr("id");
    var verticalPositionOfElement = $("."+val).offset().top;
    $(window).scrollTop(verticalPositionOfElement - 50);
}

// Remove color on values (orange, red green,...)
// To not warn the student if the data are printed
function removeColors() {
    $(".change-color").css("color", "#000");
    $(".change-color").css("background-color", "white");

    $(".region-main").css("width", "100%");
    $(".region-main").css("max-width", "100%");
    $("nav").css("display", "none");
    $("footer").css("display", "none");
    $(".columnright").css("display", "none");
    $(".columnleft").css("display", "none");

    $("#region-main-box").removeAttr("class");
    $("#page").removeAttr("id");
    $("#page-wrapper").removeAttr("id");
    $("#page-content").removeAttr("id");
    $(".row").removeClass("row");
    $(".headerbkg").css("display", "none");
    if($("#nav-drawer").attr('aria-hidden') == 'false') {
        $('button').first().click();
    }
    $(".drawer-left").hide();
}

function hideBeforePrint() {
    $('.button_list_subreport').find('div:not(:last)').hide();
    //$("div").filter(function() { return $(this).css("display") == "none" }).addClass('dontprint');
    $("div").filter(function() { return $(this).css("display") == "none" }).remove();
    $(".drawer-left").hide();
}

// Draw the graph of self insurance/confidence
function drawGraphSelfConfidence(data, txtauto, txtpeer) {
    var canvas = document.getElementById("confidence_gfx");
    var canvas2 = document.getElementById("layer_gfx");

    var ctx = canvas.getContext("2d");
    var ctx2 = canvas2.getContext("2d");

    var border = 30;
    var width = canvas.width - border;
    var height = canvas.height - border;
    var minNote = 5.5;
    var maxNote = 1.0;
    var difNote = maxNote - minNote;
    var zoom = 0;

    for(i = 0;i < data.length;i++) {
      evals = parseFloat(data[i].evals);
      autoeval = parseFloat(data[i].autoeval);

      if(evals < minNote && evals != 0) minNote = Math.round(evals) - 0.5;
      if(autoeval < minNote && autoeval != 0) minNote = Math.round(autoeval) - 0.5;

      if(evals > maxNote) maxNote = Math.round(evals) + 0.5;
      if(autoeval > maxNote) maxNote = Math.round(autoeval) + 0.5;
    }  
    difNote = maxNote - minNote;

    function drawAxes() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.beginPath();
        ctx.strokeStyle = "#000";
        ctx.moveTo(border, 0);
        ctx.lineTo(border, height);
        ctx.stroke();

        ctx.moveTo(border, height);
        ctx.lineTo(width + border, height);
        ctx.stroke();
        for (i = minNote;i <= maxNote;i += 0.25) {  
            ctx.moveTo(border - 3, (i - minNote) * (height / difNote));
            ctx.lineTo(border + 3, (i - minNote) * (height / difNote));
            ctx.stroke();
            ctx.moveTo(border - 3, (i - minNote) * (height / difNote) + (height / difNote / 2));
            ctx.lineTo(border + 3, (i - minNote) * (height / difNote) + (height / difNote / 2));
            ctx.stroke();

            if (maxNote - (i - minNote) <= 5) {
                ctx.fillStyle = "#000";
                ctx.font = "10px Verdana";
                if (i > minNote) {
                    ctx.fillText(maxNote - ( i - minNote), 0, (i - minNote) * (height / difNote) + 6);
                } else {
                    ctx.fillText(maxNote - (i - minNote), 0, (i - minNote) * (height / difNote) + 12);
                }
            }
        }

        for (i = minNote;i <= maxNote; i += 0.25) {  
            ctx.moveTo(border + ( i -minNote) * (width / difNote), height + 3);
            ctx.lineTo(border + (i - minNote) * (width / difNote), height - 3);
            ctx.stroke();
            ctx.moveTo(border+ (i - minNote) * (width / difNote) + (width / difNote / 2), height + 3);
            ctx.lineTo(border+ (i - minNote) * (width / difNote) + (width / difNote / 2), height - 3);
            ctx.stroke();

            if (i == maxNote) {
                decalX = 20;
            } else {
                decalX = (i.toString().length) * 3; 
            }

            if (i <= 5) { 
                ctx.font = "10px Verdana";
                ctx.fillText(i, border + (i - minNote) * (width / difNote) - decalX, height + 16);
            }
        }    
        ctx.font = "12px Verdana";
        ctx.fillText(txtauto, border + 3, 12);
        ctx.fillText(txtpeer, width - 50, height - 14);
        ctx.moveTo(border, height);
        ctx.lineTo(width+border, 0);
        ctx.stroke();
    }

    function drawBalls() {
        for(i = 0; i < data.length; i++) {  
            x = parseInt((data[i].evals - minNote) * (width / difNote) + border);
            y = parseInt(height - ((data[i].autoeval - minNote) * (height / difNote)) - 8);
            yy = parseInt((data[i].autoeval - minNote) * (height / difNote)); 
            $("#graph-balls").append("<div class=\"myballs toolpit\" id=\"" + data[i].id + "\" style=\"top:" + y + "px;left:"
                        + x + "px;\"><b>&nbsp;</b><span class=\"toolpittext\">"
                        + data[i].name + "(" + data[i].autoeval + " - "+ data[i].evals + ")</span></div>");
            ctx.beginPath();
            ctx.arc(x + 4, y + 4, 4, 0, 2 * Math.PI);
            ctx.strokeStyle = "rgba(0,144,0,0.5)";
            ctx.fillStyle = "rgba(0,144,0,0.5)";
            if(Math.abs(data[i].evals-data[i].autoeval) > 0.5 ) {
                ctx.fillStyle = "rgba(255,140,0,0.5)";
                ctx.strokeStyle = "rgba(255,140,0,0.5)";
            }
            if(Math.abs(data[i].evals-data[i].autoeval) > 1 ) {
                ctx.fillStyle = "rgba(0,0,0,0.5)";
                ctx.strokeStyle = "rgba(0,0,0,0.5)";
            }
            ctx.fill();
            ctx.stroke(); 
        }
    }  

    drawAxes();
    drawBalls();

    $("div.myballs").mouseover(function() {    
        $( "div.myballs" ).css("display","none");
        $(this).css("display","");
        pos = $(this).position();
        ctx2.clearRect(0, 0, canvas2.width, canvas2.height);
        ctx2.beginPath();
        ctx2.strokeStyle = "#999";
        ctx2.moveTo(pos.left, pos.top + 6);
        ctx2.lineTo(pos.left, height);
        ctx2.stroke();
        ctx2.moveTo(pos.left + 6, pos.top + 8);
        ctx2.lineTo(border, pos.top + 8);
        ctx2.stroke();
    }).mouseout(function() {    
        $("div.myballs").css("display","");
        ctx2.clearRect(0, 0, canvas2.width, canvas2.height);
    });

    $("th").click(function(){
        var table = $(this).parents("table").eq(0);
        var rows = table.find("tr:gt(0)").toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc){
            rows = rows.reverse();
        }
        for (var i = 0;i < rows.length; i++) {
            table.append(rows[i]);
        }
    });
    
    function comparer(index) {
        return function(a, b) {
            var valA = getCellValue(a, index);
            var valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
        }
    }
    function getCellValue(row, index){ return $(row).children("td").eq(index).text(); }
     $(".myballs").click(function(){
       var activityid = document.getElementById("activityid").value;
       location.href="view.php?id=" + activityid + "&groupid=0&usrid=" + this.id + "&tab=2&results=3";
    });
}  // End of function

// Reload the page result with group details
function reloadGroupme(obj) {
    val = $(obj).children(":selected").attr("id");
    usrid = document.getElementById("usrid").value;
    id = document.getElementById("activityid").value;
    location.href = 'view.php?id=' + id + '&usrid=' + usrid + '&groupid=' + val + '&tab=2&results=2';
}
// From https://www.sitepoint.com/get-url-parameters-with-javascript/
function getAllUrlParams(url) {
    // Get query string from url (optional) or window
    var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

    // We'll store the parameters here
    var obj = {};

    // if query string exists
    if (queryString) {
        // Stuff after # is not part of query string, so get rid of it
        queryString = queryString.split('#')[0];

        // Split our query string into its component parts
        var arr = queryString.split('&');

        for (var i = 0; i < arr.length; i++) {
            // Separate the keys and the values
            var a = arr[i].split('=');

            // Set parameter name and value (use 'true' if empty)
            var paramName = a[0];
            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

            // Keep case consistent (optional)
            paramName = paramName.toLowerCase();
            if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

            // If the paramName ends with square brackets, e.g. colors[] or colors[2]
            if (paramName.match(/\[(\d+)?\]$/)) {

                // Create key if it doesn't exist
                var key = paramName.replace(/\[(\d+)?\]/, '');
                if (!obj[key]) obj[key] = [];
                // If it's an indexed array e.g. colors[2]
                if (paramName.match(/\[\d+\]$/)) {
                    // Get the index value and add the entry at the appropriate position
                    var index = /\[(\d+)\]/.exec(paramName)[1];
                    obj[key][index] = paramValue;
                } else {
                    // Otherwise add the value to the end of the array
                    obj[key].push(paramValue);
                }
            } else {
                // We're dealing with a string
                if (!obj[paramName]) {
                    // If it doesn't exist, create property
                    obj[paramName] = paramValue;
                } else if (obj[paramName] && typeof obj[paramName] === 'string'){
                    // If property does exist and it's a string, convert it to an array
                    obj[paramName] = [obj[paramName]];
                    obj[paramName].push(paramValue);
                } else {
                    // Otherwise add the property
                    obj[paramName].push(paramValue);
                }
            }
        }
    }

    return obj;
}

// Clear the sort parameter for climat when go out of visualisation
if(getAllUrlParams().tab != 2) {
    localStorage.setItem("sort_climat", '');
}

$(".report-yearbook img").css("transform","scale(1.0)");
$(".report-yearbook-descr").css("transform","scale(1.0)");

