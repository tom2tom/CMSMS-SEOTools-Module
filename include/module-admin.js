function select_all(name) {
 var st = $('#all'+name).attr('checked');
 if(!st) st = false;
 $('input[name="'+name+'sel[]"][type="checkbox"]').attr('checked', st);
}

function confirm_click(name) {
 var cb = $('input[name='+name+'sel[]]:checked');
 return (cb.length > 0);
}

$(document).ready(function(){
 $('#alerts_c a').click(function(){
  var indx = $(this).attr('class').substring(1);
  //this relies on undocumented tabs-structure for admin pages
  var from = $('#page_tabs > .active').attr('id');
  var to = $('#page_tabs div:eq('+indx+')').attr('id'); //lazy, no range-check
  $('#'+from).removeClass('active');
  $('#'+from+'_c').css('display','none');
  $('#'+to).addClass('active');
  $('#'+to+'_c').css('display','block');
  return false;
 });
 $('.masterTooltip').hover(function(){
  var title = $(this).attr('title');
  $(this).data('tipText', title).removeAttr('title');
  $('<p class="tooltip"></p>')
   .text(title)
   .appendTo('body')
   .fadeIn('slow');
 }, function() {
   $(this).attr('title', $(this).data('tipText'));
   $('.tooltip').remove();
 }).mousemove(function(e) {
   var mousex = e.pageX + 10; //X coordinate
   var mousey = e.pageY + 16; //Y coordinate
   $('.tooltip').css({ top: mousey, left: mousex })
 });
});
