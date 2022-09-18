var $ = jQuery.noConflict();
$(function () {
    $("#client").each(function () {
        let option_val = $(this).val();
        $("#client").on("change", function () {
            option_val = $(this).val();
            /*console.log(option_val)*/
        });
        $("#lpapilog_filter_btn").on("click", function () {
            let input_val = $("#lpapilog_filter_input").val();
            Url.updateSearchParam({'filter_by': option_val, "filter_value": input_val});
            window.location.href = Url.getLocation();
        });
    });

    let url = $(location).attr('href');
    let url_no_filter = url.split("&").splice(0, 1).join("");
    $("#clear_filter").attr("href", url_no_filter);
    $().ready(function(){
        let filter_value = Url.queryString("filter_value");
        let filter_by = Url.queryString("filter_by");
        if (filter_value !== undefined) {
            $("#client").val(filter_by);
        }
        $("#lpapilog_filter_input").val(filter_value);
    })

    $(".modal_button").click(function () {

        const text = $(this).parents().parents("tr").find("#order_id").attr("data");


            $(' .order_id_show').html(text);
            $('input[name="order_id"]').val(text);


    });
    $("#fade").modal({
        fadeDuration: 100
    });
});