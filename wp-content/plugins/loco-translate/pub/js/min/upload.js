!function(o, n, t) {
var e, i, r, a = o.loco, u = (a && a.conf || {}).multipart && o.FormData && o.Blob, c = n.getElementById("loco-fs"), f = n.getElementById("loco-main");
function l(e) {
var n = t(f).find("button.button-primary");
return n.each(function(n, t) {
t.disabled = e;
}), n;
}
function d() {
l(!0).addClass("loco-loading");
}
function s(n) {
l(n).removeClass("loco-loading");
}
function m() {
f.path.value = i + "/" + r, d(), e.connect();
}
function v() {
return i && r && e.authed();
}
function p(n, t, e) {
n.redirect ? (s(!0), o.location.assign(n.redirect)) : s(!1);
}
function g() {
s(!1);
}
c && f && (e = o.loco.fs.init(c).setForm(f).listen(function(n) {
s(!(n && i && r));
}), t(f).change(function(n) {
r = String(f.f.value).split(/[\\\/]/).pop();
var t, e = n.target || {};
if ("dir" === e.name && e.checked) {
if ((t = e.value) && t !== i && (i = t, r)) return void m();
} else if ("f" === e.name && i) return void m();
l(!v());
}).submit(function(n) {
if (v()) {
if (u) {
n.preventDefault();
var t = new FormData(f);
return d(), a.ajax.post("upload", t, p, g), !1;
}
return !0;
}
return n.preventDefault(), !1;
}));
}(window, document, window.jQuery);