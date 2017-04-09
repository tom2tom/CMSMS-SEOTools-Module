function select_all(name) {
 var st = $('#all'+name).attr('checked');
 if(!st) { st = false; }
 $('input[name="'+name+'sel[]"][type="checkbox"]').attr('checked', st);
}
function confirm_click(name) {
 var cb = $('input[name='+name+'sel[]]:checked');
 return (cb.length > 0);
}
function tipkeep() {
 $(this).closest('div.slidediv').css('display','block').stop(false,true)
 .slideDown({duration:10, queue:false});
}
function tipsee() {
 $(this).parent().next('.slidediv').slideDown(300);
}
function tiphide() {
 $(this).parent().next('.slidediv').slideUp({ duration:200, queue:false });
}
function tiphide2() {
 var a = document.activeElement,
   n = a.tagName.toUpperCase();
 if (n == 'BODY' || a != this) {
  $(this).parent().next('.slidediv').slideUp({ duration:200, queue:false });
 }
}
function totab() {
 var indx = $(this).attr('tabindx');
 //this relies on undocumented tabs-structure for admin pages
 var from = $('#page_tabs > .active').attr('id');
 var to = $('#page_tabs div:eq('+indx+')').attr('id'); //lazy, no range-check
 $('#'+from).removeClass('active');
 $('#'+from+'_c').css('display','none');
 $('#'+to).addClass('active');
 $('#'+to+'_c').css('display','block');
 return false;
}

$(document).ready(function(){
 $('.slidediv').css('display','none').find('p').click(tipkeep);
 $('.slidetip').children().filter(':input').hover(tipsee,tiphide2).focus(tipsee).blur(tiphide);
 $('a[tabindx]').click(totab);
});
