!function(a, r) {
var e = a.conf, o = r("#loco-utf8-check")[0].textContent;
function t(e, o, n) {
"success" !== o && (n = a.ajax.parse(a.ajax.strip(e.responseText))), r("#loco-ajax-check").text("FAILED: " + n).addClass("loco-danger");
}
1 === o.length && 10003 === o.charCodeAt(0) || a.notices.warn("This page has a problem rendering UTF-8").stick(), 
window.ajaxurl && r("#loco-ajax-url").text(window.ajaxurl), r("#loco-vers-jquery").text([ r.fn && r.fn.jquery || "unknown", "ui/" + (r.ui && r.ui.version || "none"), "migrate/" + (r.migrateVersion || "none") ].join("; ")), 
a.ajax.post("ping", {
echo: "ΟΚ ✓"
}, function(e, o, n) {
e && e.ping ? r("#loco-ajax-check").text(e.ping) : t(n, o, e && e.error && e.error.message);
}, t);
var n, i = e.apis, c = i.length, s = -1, l = a.locale.parse("fr");
function u(e, o) {
return r("#loco-api-" + e).text(o);
}
function d(e) {
var n = e.getId();
e.key() ? e.translate("OK", l, function(e, o) {
o ? u(n, "OK ✓") : u(n, "FAILED").addClass("loco-danger");
}) : u(n, "No API key");
}
if (a.apis) for (;++s < c; ) {
n = i[s];
try {
d(a.apis.create(n));
} catch (e) {
u(n.id, String(e));
}
} else a.notices.error("admin.js is out of date. Please empty your browser cache.");
}(window.loco, window.jQuery, window.wp);