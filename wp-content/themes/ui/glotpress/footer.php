	</div><!-- .gp-content -->

	<script>
    var $ = jQuery.noConflict();

var projectsearch =  $(".search-form input[type=search]");
var url = $(location).attr('href'); //获取url地址
var jqurl = url.split("/").splice(0, 6).join("/");
 if(url.indexOf("plugins") >= 0){ 
$(projectsearch).attr("placeholder","搜索插件……");
 }else if(url.indexOf("docs") >= 0){
$(projectsearch).attr("placeholder","搜索文档……");
 }else if(url.indexOf("themes") >= 0){
$(projectsearch).attr("placeholder","搜索主题……");
 }else if(url.indexOf("wordpress") >= 0){
$(projectsearch).attr("placeholder","搜索WordPress核心……");
 }
 else if(url =="price-desc"){
 }
         $("#projects-filter").on("input", function () {

            var projectval =$(projectsearch).val() ;

             $(projectsearch).keydown(function(event){
if(event.keyCode==13){
 $(location).prop('href',jqurl+"/?s="+projectval);
}
})

        });
 
$(function(){ 
var　headerval =　$(".header-search input").val();
$(projectsearch).val(headerval);
$(".header-search input").val("");
});
 	jQuery('#hide-help-notice').click(function() {
				jQuery.ajax({url: '/getting-started/hide-notice/'});
				jQuery('#help-notice').fadeOut(1000);
				return false;
			});
</script>
<?php

get_footer();
?>
