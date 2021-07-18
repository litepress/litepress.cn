var $ = jQuery.noConflict();
$(function () {
$("#client").each(function () {
    option_val = $(this).val();
    $("#client").on("change", function () {
        option_val = $(this).val();
        /*console.log(option_val)*/
    });
    $("#lpapilog_filter_btn").on("click", function () {
        input_val = $("#lpapilog_filter_input").val();
        Url.updateSearchParam({'filter_by': option_val, "filter_value": input_val});
        window.location.href = Url.getLocation();

    });
});
url = $(location).attr('href');
url_no_filter = url.split("&").splice(0, 1).join("");
$("#clear_filter").attr("href", url_no_filter);
    $().ready(function(){
    filter_value = Url.queryString("filter_value");
    filter_by = Url.queryString("filter_by");
    if (filter_value !== undefined) {
        $("#client").val(filter_by);
    }
    $("#lpapilog_filter_input").val(filter_value);
    })
});