!function(e, o, f) {
var a, c, i, l, r, p, n, s, u, d = e.loco, t = d.conf, h = d.po.ref.init(d, t), v = o.getElementById("loco-po");
function g() {
r.length && (p.push([ i, l ]), c.push(r), r = []), i = null;
}
function m(t) {
return f('<ol class="msgcat"></ol>').attr("start", t).appendTo(a);
}
function x(t) {
n !== t && (f("#loco-content")[t ? "removeClass" : "addClass"]("loco-invalid"), 
n = t);
}
a = v, c = d.fulltext.init(), r = [], p = [], s = !(n = !0), (u = f(a).find("li")).each(function(t, n) {
var e, o = f(n);
o.find("span.po-none").length ? g() : (l = t, null == i && (i = t), (e = o.find(".po-text").text()) && (r = r.concat(e.replace(/\\[ntvfab\\"]/g, " ").split(" "))));
}), g(), d.watchtext(f(a.parentNode).find("form.loco-filter")[0].q, function(t) {
t ? function(t) {
var n, e, o, i = c.find(t), l = -1, r = i.length;
if (f("ol", a).remove(), r) {
for (;++l < r; ) for (o = m((n = (e = p[i[l]])[0]) + 1); n <= e[1]; n++) o.append(u[n]);
x(!0);
} else x(!1), m(0).append(f("<li></li>").text(d.l10n._("Nothing matches the text filter")));
s = !0, C();
}(t) : s && (x(!0), s = !1, f("ol", a).remove(), m(1).append(u), C());
}), f(v).removeClass("loco-loading");
var w, y, C = (y = v.clientHeight - 2, function() {
var t = function(t, n) {
for (var e = t.offsetTop || 0; (t = t.offsetParent) && t !== n; ) e += t.offsetTop || 0;
return e;
}(v, o.body), n = e.innerHeight - t - 20;
w !== n && (v.style.height = n < y ? String(n) + "px" : "", w = n);
});
C(), f(e).resize(C), f(v).on("click", function(t) {
var n = t.target;
if (n.hasAttribute("href")) return t.preventDefault(), h.load(n.textContent), !1;
});
}(window, document, window.jQuery);