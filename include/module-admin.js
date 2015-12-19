function select_all(name) {
 var st = $('#all'+name).attr('checked');
 if(!st) st = false;
 $('input[name="'+name+'sel[]"][type="checkbox"]').attr('checked', st);
}
function confirm_click(name) {
 var cb = $('input[name='+name+'sel[]]:checked');
 return (cb.length > 0);
}
function tipup() {
 var $t = $(this),
  h = $t.height() + 5,
  p = $t.offset(),
  title = $t.attr('title'),
  $n = $('<p class="tooltip"></p>');
 $t.removeAttr('title').data('tipText',title);
 $n.text(title)
   .appendTo('body')
   .css({ top: p.top + h, left: p.left + 5})
   .fadeIn(400);
 $t.data('tipNew',$n);
}

function tipup2() {
 try {
  if (this == document.activeElement) {
 var dbg = 1;
 //TODO manipulate tip
    return;
  }
 } catch(err) {}
}

function tipdown() {
 var $t = $(this);
 $t.attr('title',$t.data('tipText')).data('tipNew').fadeOut(200,function(){
  $t.data('tipNew').remove();
 });
}

function tipdown2() {
 try {
  if (this == document.activeElement) {
 var dbg = 1;
 //TODO manipulate tip
    return;
  }
 } catch(err) {}
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
 $('[title]').hover(tipup,tipdown).children().filter(':input').focus(tipup2).blur(tipdown2);
});
