$(document).ready(function(){
/*      
    $('[data-toggle="tooltip"]').tooltip();
    $(".nav-collapse").removeClass("collapse");
    $("body").css("line-height", "20px");
    $(".container-fluid").css("padding-right","20px");
*/    
});

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

 function mytoggle(id) {
  var dsp = $("#"+id).css('display');
  if(dsp == 'none')  $("#"+id).css('display', '');
  else $("#"+id).css('display', 'none');
 }