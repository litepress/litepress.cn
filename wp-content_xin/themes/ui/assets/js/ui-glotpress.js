$(".action.edit").click(function(){
  $(this).parent().parent().next().find(".textareas").addClass("active");
})

$("tr.preview").dblclick(function(){
  $(this).next().find(".textareas").addClass("active");
})
const $translations = $("#translations");
if ($translations.length > 0) {
  $translations.tooltip({
    items: ".glossary-word",
    content: function () {
      const i = $("<ul>");
      return $.each($(this).data("translations"), function (t, e) {
        e.locale_entry = undefined;
        const r = $("<li>");
        e.locale_entry && r.append($("<span>", {
          text: e.locale_entry
        }).addClass("locale-entry bubble")),
            r.append($("<span>", {
              text: e.pos
            }).addClass("pos")),
            r.append($("<span>", {
              text: e.translation
            }).addClass("translation")),
            r.append($("<span>", {
              text: e.comment
            }).addClass("comment")),
            i.append(r)
      })
          ,
          i
    },
    hide: !1,
    show: !1
  })
}
$(".btn.replace").click(function(){
  $(this).parent().siblings(".replace").toggle();
  $(this).parent().siblings(".filters-expanded:not(.replace)").hide();
})
$(".btn.filter,.btn.sort").click(function(){
  $(this).parent().siblings(".replace").hide();
})


$(function () {
  $(".btn.filter").click(function () {
    $(this).text("过滤");
    $(this).siblings(".sort").text("排序");
  })
  $(".btn.sort").click(function () {
    $(this).text("排序");
    $(this).siblings(".filter").text("过滤");
  })
})





  $("#lp-approveModal .btn-primary").click(function () {
    var url = location.pathname,
        last_part2 = url.split("/").splice(3, 2).join("/");
    $.ajax({
      url: "https://litepress.cn/translate/wp-json/gp/v1/projects/approve",
      type: "post",
      data: {"path": last_part2},
      headers: {
        'X-WP-Nonce': wpApiSettings.nonce
      },
      success: function (s) {
        console.log(s.message);
        if (s.message !== undefined ) {
          $(" .toast-body").html("<i class=\"fad fa-check-circle text-success\"></i> " + s.message);

        } else {
          $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i> " + s.error);
        }
        $('#liveToast').toast('show')
      },
    })
    return false;
  })
