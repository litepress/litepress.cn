var $ = jQuery.noConflict();
$(function (){
$('section.lprsa .copy').click(function () {
    $(this).text('已复制').attr('disabled', true);
    const textarea = $(this).parents().find('textarea');
    $(textarea).select();
    document.execCommand('Copy');
});
} );