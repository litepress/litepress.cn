!function(t, n, e) {
var o = t.loco, i = n.getElementById("loco-fs"), r = n.getElementById("loco-potinit");
function c(t) {
var n = t && t.redirect;
n && location.assign(n);
}
e(r).on("submit", function(t) {
return t.preventDefault(), o.ajax.submit(t.target, c), !1;
}), i && o.fs.init(i).setForm(r);
}(window, document, jQuery);