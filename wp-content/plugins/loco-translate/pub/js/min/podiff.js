!function(e, t, u) {
var i, o = [], n = e.loco, s = n.conf, a = 0, r = s.paths.length - 2, d = t.getElementById("loco-ui"), f = t.getElementById("loco-fs"), l = d.getElementsByTagName("form").item(0), m = d.getElementsByTagName("button"), c = u(d).find("div.diff-meta"), g = m.item(0), p = m.item(1);
function h() {
return u(d).removeClass("loading");
}
function v(e) {
return u(d).find("div.diff").html(e);
}
function C(e) {
return h(), u('<p class="error"></p>').text(e).appendTo(v(""));
}
function y(e, t) {
var n, a = t.getElementsByTagName("tr"), r = a.length, i = t.getAttribute("data-diff").split(/\D+/), o = i[0], s = i[1], d = i[2], f = i[3];
function l(e, t, n) {
t <= n && u("<span></span>").text(String(t)).prependTo(e);
}
for (e = 0; e < r; e++) l((n = a[e].getElementsByTagName("td"))[0], o++, s), l(n[2], d++, f);
}
function j(a) {
i && i.abort();
var r = o[a];
if (null != r) return v(r), void h();
v(""), u(d).addClass("loading"), i = n.ajax.post("diff", {
lhs: s.paths[a],
rhs: s.paths[a + 1]
}, function(e, t, n) {
n === i && ((r = e && e.html) ? (v(o[a] = r).find("tbody").each(y), h()) : C(e && e.error || "Unknown error"));
}, function(e, t, n) {
e === i && (i = null, C("Failed to generate diff"));
});
}
function b(e) {
0 <= e && e <= r && (j(a = e), function() {
var e = a, t = e + 1;
g.disabled = r <= e, p.disabled = e <= 0, c.addClass("jshide").removeClass("diff-meta-current"), 
c.eq(e).removeClass("jshide").addClass("diff-meta-current"), c.eq(t).removeClass("jshide");
}());
}
f && l && n.fs.init(f).setForm(l), r && (u(g).on("click", function(e) {
return e.preventDefault(), b(a + 1), !1;
}).parent().removeClass("jshide"), u(p).on("click", function(e) {
return e.preventDefault(), b(a - 1), !1;
}).parent().removeClass("jshide")), b(0);
}(window, document, jQuery);