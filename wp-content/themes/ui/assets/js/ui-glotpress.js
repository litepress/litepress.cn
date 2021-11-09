$(".action.edit").click(function(){
  $(this).parent().parent().next().find(".textareas").addClass("active");
})

$("tr.preview").dblclick(function(){
  $(this).next().find(".textareas").addClass("active");
})

$("#translations").tooltip({
  items: ".glossary-word",
  content: function() {
    var i = $("<ul>");
    return $.each($(this).data("translations"), function (t, e) {
      e.locale_entry = undefined;
      var r = $("<li>");
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