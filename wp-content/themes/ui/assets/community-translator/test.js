(globalThis.webpackChunkcalypso = globalThis.webpackChunkcalypso || []).push([[43], {
    "../node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js": (e, t, s) => {
        "use strict";

        function n(e, t) {
            (null == t || t > e.length) && (t = e.length);
            for (var s = 0, n = new Array(t); s < t; s++) n[s] = e[s];
            return n
        }

        s.d(t, {Z: () => n})
    }, "../node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js": (e, t, s) => {
        "use strict";

        function n(e) {
            if (Array.isArray(e)) return e
        }

        s.d(t, {Z: () => n})
    }, "../node_modules/@babel/runtime/helpers/esm/nonIterableRest.js": (e, t, s) => {
        "use strict";

        function n() {
            throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")
        }

        s.d(t, {Z: () => n})
    }, "../node_modules/@babel/runtime/helpers/esm/slicedToArray.js": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => a});
        var n = s("../node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js"),
            r = s("../node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js"),
            i = s("../node_modules/@babel/runtime/helpers/esm/nonIterableRest.js");

        function a(e, t) {
            return (0, n.Z)(e) || function (e, t) {
                if ("undefined" != typeof Symbol && Symbol.iterator in Object(e)) {
                    var s = [], n = !0, r = !1, i = void 0;
                    try {
                        for (var a, l = e[Symbol.iterator](); !(n = (a = l.next()).done) && (s.push(a.value), !t || s.length !== t); n = !0) ;
                    } catch (e) {
                        r = !0, i = e
                    } finally {
                        try {
                            n || null == l.return || l.return()
                        } finally {
                            if (r) throw i
                        }
                    }
                    return s
                }
            }(e, t) || (0, r.Z)(e, t) || (0, i.Z)()
        }
    }, "../node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => r});
        var n = s("../node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

        function r(e, t) {
            if (e) {
                if ("string" == typeof e) return (0, n.Z)(e, t);
                var s = Object.prototype.toString.call(e).slice(8, -1);
                return "Object" === s && e.constructor && (s = e.constructor.name), "Map" === s || "Set" === s ? Array.from(e) : "Arguments" === s || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s) ? (0, n.Z)(e, t) : void 0
            }
        }
    }, "../node_modules/@wordpress/url/build-module/add-query-args.js": (e, t, s) => {
        "use strict";
        s.d(t, {f: () => o});
        var n = s("../node_modules/@wordpress/url/build-module/get-query-args.js"),
            r = s("../node_modules/@babel/runtime/helpers/esm/slicedToArray.js");

        function i(e, t) {
            var s;
            if ("undefined" == typeof Symbol || null == e[Symbol.iterator]) {
                if (Array.isArray(e) || (s = function (e, t) {
                    if (e) {
                        if ("string" == typeof e) return a(e, t);
                        var s = Object.prototype.toString.call(e).slice(8, -1);
                        return "Object" === s && e.constructor && (s = e.constructor.name), "Map" === s || "Set" === s ? Array.from(e) : "Arguments" === s || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s) ? a(e, t) : void 0
                    }
                }(e)) || t && e && "number" == typeof e.length) {
                    s && (e = s);
                    var n = 0, r = function () {
                    };
                    return {
                        s: r, n: function () {
                            return n >= e.length ? {done: !0} : {done: !1, value: e[n++]}
                        }, e: function (e) {
                            throw e
                        }, f: r
                    }
                }
                throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")
            }
            var i, l = !0, o = !1;
            return {
                s: function () {
                    s = e[Symbol.iterator]()
                }, n: function () {
                    var e = s.next();
                    return l = e.done, e
                }, e: function (e) {
                    o = !0, i = e
                }, f: function () {
                    try {
                        l || null == s.return || s.return()
                    } finally {
                        if (o) throw i
                    }
                }
            }
        }

        function a(e, t) {
            (null == t || t > e.length) && (t = e.length);
            for (var s = 0, n = new Array(t); s < t; s++) n[s] = e[s];
            return n
        }

        function l(e) {
            for (var t, s = "", n = Array.from(Object.entries(e)); t = n.shift();) {
                var a = t, l = (0, r.Z)(a, 2), o = l[0], d = l[1];
                if (Array.isArray(d) || d && d.constructor === Object) {
                    var c, u = i(Object.entries(d).reverse());
                    try {
                        for (u.s(); !(c = u.n()).done;) {
                            var h = (0, r.Z)(c.value, 2), g = h[0], m = h[1];
                            n.unshift(["".concat(o, "[").concat(g, "]"), m])
                        }
                    } catch (e) {
                        u.e(e)
                    } finally {
                        u.f()
                    }
                } else void 0 !== d && (null === d && (d = ""), s += "&" + [o, d].map(encodeURIComponent).join("="))
            }
            return s.substr(1)
        }

        function o() {
            var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
                t = arguments.length > 1 ? arguments[1] : void 0;
            if (!t || !Object.keys(t).length) return e;
            var s = e, r = e.indexOf("?");
            return -1 !== r && (t = Object.assign((0, n.w)(e), t), s = s.substr(0, r)), s + "?" + l(t)
        }
    }, "../node_modules/@wordpress/url/build-module/get-query-args.js": (e, t, s) => {
        "use strict";
        s.d(t, {w: () => o});
        var n = s("../node_modules/@babel/runtime/helpers/esm/slicedToArray.js"),
            r = s("../node_modules/@babel/runtime/helpers/esm/defineProperty.js"),
            i = s("../node_modules/@wordpress/url/build-module/get-query-string.js");

        function a(e, t) {
            var s = Object.keys(e);
            if (Object.getOwnPropertySymbols) {
                var n = Object.getOwnPropertySymbols(e);
                t && (n = n.filter((function (t) {
                    return Object.getOwnPropertyDescriptor(e, t).enumerable
                }))), s.push.apply(s, n)
            }
            return s
        }

        function l(e) {
            for (var t = 1; t < arguments.length; t++) {
                var s = null != arguments[t] ? arguments[t] : {};
                t % 2 ? a(Object(s), !0).forEach((function (t) {
                    (0, r.Z)(e, t, s[t])
                })) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : a(Object(s)).forEach((function (t) {
                    Object.defineProperty(e, t, Object.getOwnPropertyDescriptor(s, t))
                }))
            }
            return e
        }

        function o(e) {
            return ((0, i.W)(e) || "").replace(/\+/g, "%20").split("&").reduce((function (e, t) {
                var s = t.split("=").filter(Boolean).map(decodeURIComponent), r = (0, n.Z)(s, 2), i = r[0], a = r[1],
                    o = void 0 === a ? "" : a;
                return i && function (e, t, s) {
                    for (var n = t.length, r = n - 1, i = 0; i < n; i++) {
                        var a = t[i];
                        !a && Array.isArray(e) && (a = e.length.toString());
                        var o = !isNaN(Number(t[i + 1]));
                        e[a] = i === r ? s : e[a] || (o ? [] : {}), Array.isArray(e[a]) && !o && (e[a] = l({}, e[a])), e = e[a]
                    }
                }(e, i.replace(/\]/g, "").split("["), o), e
            }), {})
        }
    }, "../node_modules/@wordpress/url/build-module/get-query-string.js": (e, t, s) => {
        "use strict";

        function n(e) {
            var t;
            try {
                t = new URL(e, "http://example.com").search.substring(1)
            } catch (e) {
            }
            if (t) return t
        }

        s.d(t, {W: () => n})
    }, "./components/data/query-user-settings/index.jsx": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => l});
        var n = s("../node_modules/react/index.js"), r = s("../node_modules/react-redux/es/index.js"),
            i = s("./state/user-settings/actions.js");

        class a extends n.Component {
            componentDidMount() {
                this.props.fetchUserSettings()
            }

            render() {
                return null
            }
        }

        const l = (0, r.$j)(null, {fetchUserSettings: i.j_})(a)
    }, "./components/forms/form-checkbox/index.tsx": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => o});
        var n = s("../node_modules/@babel/runtime/helpers/extends.js"), r = s.n(n),
            i = s("../node_modules/react/index.js"), a = s("../node_modules/classnames/index.js"), l = s.n(a);
        const o = ({className: e, ...t}) => i.createElement("input", r()({}, t, {
            type: "checkbox",
            className: l()(e, "form-checkbox")
        }))
    }, "./components/forms/form-label/index.tsx": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => d});
        var n = s("../node_modules/@babel/runtime/helpers/extends.js"), r = s.n(n),
            i = s("../node_modules/react/index.js"), a = s("../node_modules/classnames/index.js"), l = s.n(a),
            o = s("../packages/i18n-calypso/src/index.js");
        const d = ({children: e, required: t, optional: s, className: n, ...a}) => {
            const d = (0, o.qM)(), c = i.Children.count(e) > 0;
            return i.createElement("label", r()({}, a, {className: l()(n, "form-label")}), e, c && t && i.createElement("small", {className: "form-label__required"}, d("Required")), c && s && i.createElement("small", {className: "form-label__optional"}, d("Optional")))
        }
    }, "./components/forms/form-text-input/index.jsx": (e, t, s) => {
        "use strict";
        s.d(t, {Z: () => g});
        var n = s("../node_modules/@babel/runtime/helpers/extends.js"), r = s.n(n),
            i = s("../node_modules/@babel/runtime/helpers/defineProperty.js"), a = s.n(i),
            l = s("../node_modules/react/index.js"), o = s("../node_modules/prop-types/index.js"), d = s.n(o),
            c = s("../node_modules/classnames/index.js"), u = s.n(c), h = s("../node_modules/lodash-es/omit.js");

        class g extends l.PureComponent {
            constructor(...e) {
                super(...e), a()(this, "state", {value: this.props.value || ""}), a()(this, "currentTextField", void 0), a()(this, "textFieldRef", (e => {
                    this.currentTextField = e;
                    const {inputRef: t} = this.props;
                    t && ("function" == typeof t ? t(e) : t.current = e)
                })), a()(this, "selectOnFocus", (e => {
                    this.props.selectOnFocus && e.target.select()
                })), a()(this, "onChange", (e => {
                    var t, s;
                    this.setState({value: e.target.value}), null === (t = (s = this.props).onChange) || void 0 === t || t.call(s, e)
                }))
            }

            componentDidUpdate(e) {
                this.updateValueIfNeeded(e.value)
            }

            updateValueIfNeeded(e) {
                const {value: t} = this.props;
                e === t && t === this.state.value || this.setState({value: t})
            }

            focus() {
                this.currentTextField && this.currentTextField.focus()
            }

            render() {
                const e = (0, h.Z)(this.props, "isError", "isValid", "selectOnFocus", "inputRef", "onChange", "value"),
                    t = u()("form-text-input", this.props.className, {
                        "is-error": this.props.isError,
                        "is-valid": this.props.isValid
                    });
                return l.createElement("input", r()({type: "text"}, e, {
                    value: this.state.value,
                    ref: this.textFieldRef,
                    className: t,
                    onClick: this.selectOnFocus,
                    onChange: this.onChange
                }))
            }
        }

        a()(g, "propTypes", {isError: d().bool, isValid: d().bool, selectOnFocus: d().bool, className: d().string})
    }, "./layout/community-translator/launcher.jsx": (e, t, s) => {
        "use strict";
        s.r(t), s.d(t, {default: () => se});
        var n = s("../node_modules/@babel/runtime/helpers/defineProperty.js"), r = s.n(n),
            i = s("../node_modules/prop-types/index.js"), a = s.n(i), l = s("../packages/i18n-calypso/src/index.js"),
            o = s("../node_modules/react/index.js"), d = s("../node_modules/react-dom/index.js"),
            c = s("./components/gridicon/index.tsx"), u = s("../node_modules/react-redux/es/index.js"),
            h = s("../node_modules/classnames/index.js"), g = s.n(h),
            m = s("../node_modules/@wordpress/url/build-module/add-query-args.js"), p = s("./config/index.js"),
            b = s("../packages/viewport/src/index.js"), f = s("../node_modules/debug/src/browser.js"), v = s.n(f),
            y = s("../node_modules/lodash-es/isUndefined.js"), j = s("../node_modules/lodash-es/find.js"),
            _ = s("../packages/languages/src/index.ts"),
            S = s("./lib/load-jquery-dependent-script-desktop-wrapper/index.js"), w = s("./lib/user/index.js"),
            E = s("./lib/analytics/tracks.js"), T = s("./lib/i18n-utils/browser.js");
        const x = v()("calypso:community-translator"), k = {de_formal: "formal"}, C = {
            localeCode: "en",
            languageName: "English",
            pluralForms: "nplurals=2; plural=(n != 1)",
            contentChangedCallback() {
            },
            glotPress: {url: "https://translate.wordpress.com", project: "wpcom", translation_set_slug: "default"}
        };
        let D, O, A, I = !1, Z = !1;
        const N = {
            isEnabled() {
                const e = (0, w.default)().get();
                return !(!(e && e.localeSlug && (0, T.BK)(e.localeSlug)) || e.localeVariant && !(0, T.BK)(e.localeVariant) || !I || !A || !N.isValidBrowser())
            }, isActivated: () => Z, wrapTranslation(e, t, s) {
                if (!this.isEnabled() || !this.isActivated() || s.textOnly) return t;
                if ("object" != typeof s && (s = {}), "string" != typeof e) return x("unknown original format"), t;
                if ("boolean" == typeof s.textOnly && s.textOnly) return x('respecting textOnly for string "' + e + '"'), t;
                const n = {className: "translatable", "data-singular": e};
                "string" == typeof s.context && (n["data-context"] = s.context), "string" == typeof s.plural && (n["data-plural"] = s.plural);
                const r = Object.assign({}, o.createElement("data", n, t));
                return r.toString = () => t, Object.freeze(r), r
            }, init(e) {
                const t = l.ZP.getLocale() || {"": {}}, {localeSlug: s, localeVariant: n} = t[""];
                s && t ? this.updateTranslationData(s, t, n) : x("trying to initialize translator without loaded language"), O || ((0, y.Z)(e) || (I = e), I ? this.isEnabled() ? s && t && (x("Successfully initialized"), O = !0) : x("not initializing, not enabled") : x("initialization failed because userSettings are not ready"))
            }, updateTranslationData(e, t, s = null) {
                if (C.localeCode === e) return x("skipping updating translation data with same localeCode"), !0;
                x("Translator Jumpstart: loading locale file for " + e), C.localeCode = e, C.pluralForms = t[""].plural_forms || t[""]["Plural-Forms"] || t[""]["plural-forms"] || C.pluralForms, C.currentUserId = (0, w.default)().get().ID;
                const n = (0, j.Z)(_.Z, (t => t.langSlug === e));
                n && (C.languageName = n.name.replace(/^(?:[a-z]{2,3}|[a-z]{2}-[a-z]{2})\s+-\s+/, "")), this.setInjectionURL("community-translator.min.js"), C.glotPress.translation_set_slug = k[s] || "default"
            }, setInjectionURL(e) {
                D = "https://widgets.wp.com/community-translator/" + e + "?v=1.160729", x("setting injection url", D)
            }, toggle() {
                let e = !1;

                function t() {
                    return Z = !0, l.ZP.reRenderTranslations(), window.communityTranslator.load(), x("Translator activated"), !0
                }

                return C.contentChangedCallback = () => {
                    e || (x("Translator notified of page change, but handler was not registered"), e = !0)
                }, window.translatorJumpstart = C, void 0 === window.communityTranslator ? D ? (x("loading community translator"), (0, S.t)(D, (function (e) {
                    e ? x("Script " + e.src + " failed to load.") : (x("Script loaded!"), window.communityTranslator.registerTranslatedCallback(N.updateTranslation), t())
                })), !1) : (x("Community translator toggled before initialization"), Z = !1, !1) : (this.isActivated() ? (window.communityTranslator.unload(), Z = !1, l.ZP.reRenderTranslations(), x("Translator deactivated")) : t(), this.isActivated())
            }, updateTranslation(e) {
                const t = l.ZP.getLocale(), s = e.key, n = e.plural, r = e.translations;
                x("Updating ", e.singular, "from", t[s], "to", [n].concat(r)), t[s] = [n].concat(r), l.ZP.setLocale(t)
            }, isValidBrowser: () => !(0, b.tq)()
        };
        l.ZP.registerTranslateHook(((e, t) => N.wrapTranslation(t.original, e, t))), l.ZP.registerComponentUpdateHook((() => {
            if ("function" == typeof C.contentChangedCallback) return C.contentChangedCallback()
        })), l.ZP.on("change", N.init.bind(N));
        const R = N;
        var P = s("../node_modules/store/dist/store.modern.js"), U = s.n(P),
            L = s("../packages/components/src/button/index.jsx"), F = s("../packages/components/src/dialog/index.jsx"),
            V = s("./lib/analytics/mc.js"), M = s("../node_modules/lodash-es/debounce.js"),
            H = s("../node_modules/cookie/index.js"), q = s("./lib/i18n-utils/glotpress.js");
        const W = v()("calypso:translation-scanner");
        var z = s("./lib/i18n-utils/empathy-mode.js"), G = s("./state/selectors/get-user-settings.js"),
            $ = s("../node_modules/lodash-es/get.js");

        function B(e, t) {
            return (0, $.Z)(e, ["userSettings", "settings", t], null)
        }

        var K = s("./state/ui/language/actions.js"), Q = s("./state/selectors/get-current-locale-slug.js"),
            J = s("./components/data/query-user-settings/index.jsx"),
            Y = s("./components/forms/form-text-input/index.jsx"), X = s("./components/forms/form-checkbox/index.tsx"),
            ee = s("./components/forms/form-label/index.tsx");

        class te extends o.Component {
            constructor(...e) {
                super(...e), r()(this, "state", {
                    infoDialogVisible: !1,
                    firstActivation: !0,
                    isActive: R.isActivated(),
                    isEnabled: R.isEnabled(),
                    isDeliverableHighlightEnabled: !1,
                    deliverableTarget: null,
                    selectedDeliverableTarget: null,
                    deliverableTitle: "",
                    scrollTop: 0
                }), r()(this, "highlightRef", o.createRef()), r()(this, "getOriginalIds", (() => {
                    const {selectedDeliverableTarget: e} = this.state;
                    return [e].concat(Array.from(e.querySelectorAll("[class*=translator-original-]"))).reduce(((e, t) => {
                        const [, s] = t.className && t.className.match(/translator-original-(\d+)/) || [];
                        return s && -1 === e.indexOf(s) && e.push(s), e
                    }), [])
                })), r()(this, "getCreateDeliverableUrl", (() => {
                    const {deliverableTitle: e} = this.state;
                    return (0, m.f)("https://translate.wordpress.com/deliverables/create", {
                        original_ids: this.getOriginalIds().join(","),
                        title: e
                    })
                })), r()(this, "onI18nChange", (() => {
                    !this.state.isActive && R.isActivated() ? (this.setState({isActive: !0}), U().get("translator_hide_infodialog") || this.setState({infoDialogVisible: !0}), this.state.firstActivation && ((0, V.P)("calypso_translator_toggle", "intial_activation"), this.setState({firstActivation: !1}))) : this.state.isActive && !R.isActivated() && this.setState({isActive: !1})
                })), r()(this, "handleKeyDown", (e => {
                    const {isActive: t, selectedDeliverableTarget: s} = this.state;
                    t && e.getModifierState("Control") && "d" === e.key.toLowerCase() && (s && this.toggleSelectedDeliverableTarget(), this.toggleDeliverableHighlight())
                })), r()(this, "handleWindowScroll", (() => {
                    this.setState({scrollTop: window.scrollY})
                })), r()(this, "handleHighlightMouseMove", (e => {
                    const {deliverableTarget: t} = this.state;
                    t !== e.target && this.setState({deliverableTarget: e.target})
                })), r()(this, "handleHighlightMouseDown", (e => {
                    e.preventDefault(), e.stopPropagation(), this.highlightRef.current && (this.highlightRef.current.style.pointerEvents = "all")
                })), r()(this, "handleHighlightClick", (e => {
                    e.preventDefault(), e.stopPropagation(), this.highlightRef.current && (this.highlightRef.current.style.pointerEvents = ""), this.toggleSelectedDeliverableTarget(), this.toggleDeliverableHighlight()
                })), r()(this, "handleDeliverableTitleChange", (e => {
                    this.setState({deliverableTitle: e.target.value})
                })), r()(this, "handleDeliverableLinkClick", (() => {
                    this.toggleSelectedDeliverableTarget()
                })), r()(this, "handleDeliverableCancelClick", (() => {
                    this.toggleSelectedDeliverableTarget()
                })), r()(this, "handleDeliverableSubmit", (e => {
                    e.preventDefault(), window.open(this.getCreateDeliverableUrl(), "_blank"), this.toggleSelectedDeliverableTarget()
                })), r()(this, "toggleInfoCheckbox", (e => {
                    U().set("translator_hide_infodialog", e.target.checked)
                })), r()(this, "infoDialogClose", (() => {
                    this.setState({infoDialogVisible: !1})
                })), r()(this, "toggleEmpathyMode", (() => {
                    (0, z.Fs)()
                })), r()(this, "toggle", (e => {
                    e.preventDefault();
                    const {isEmpathyModeEnabled: t} = this.props;
                    if (t) return void this.toggleEmpathyMode();
                    const s = R.toggle();
                    (0, V.P)("calypso_translator_toggle", s ? "on" : "off"), this.setState({isActive: s})
                })), r()(this, "toggleDeliverableHighlight", (() => {
                    const e = !this.state.isDeliverableHighlightEnabled;
                    this.setState({
                        isDeliverableHighlightEnabled: e,
                        deliverableTarget: null
                    }), e ? (window.addEventListener("scroll", this.handleWindowScroll), window.addEventListener("mousemove", this.handleHighlightMouseMove), window.addEventListener("mousedown", this.handleHighlightMouseDown), window.addEventListener("click", this.handleHighlightClick)) : (window.removeEventListener("mousemove", this.handleHighlightMouseMove), window.removeEventListener("mousedown", this.handleHighlightMouseDown), window.removeEventListener("click", this.handleHighlightClick))
                })), r()(this, "toggleSelectedDeliverableTarget", (() => {
                    this.setState((({deliverableTarget: e, selectedDeliverableTarget: t}) => ({
                        selectedDeliverableTarget: t ? null : e,
                        deliverableTitle: ""
                    })), (() => {
                        const e = !!this.state.selectedDeliverableTarget;
                        if (document.body.classList.toggle("has-deliverable-highlighted", e), e) {
                            window.addEventListener("scroll", this.handleWindowScroll), this.selectedLanguageSlug = this.props.selectedLanguageSlug;
                            const e = "en";
                            (0, K.i)(e)
                        } else window.removeEventListener("scroll", this.handleWindowScroll), this.selectedLanguageSlug && this.props.setLocale(this.selectedLanguageSlug)
                    }))
                }))
            }

            static getDerivedStateFromProps(e, t) {
                return R.init(e.isUserSettingsReady), function (e) {
                    const t = e,
                        s = t ? "calypso_community_translator_enabled" : "calypso_community_translator_disabled";
                    A !== t && void 0 !== A && (x(s), (0, E.recordTracksEvent)(s, {locale: (0, w.default)().get().localeSlug})), A = t
                }(e.isTranslatorEnabled), t.isEnabled !== R.isEnabled() ? {...t, isEnabled: R.isEnabled()} : null
            }

            componentDidMount() {
                l.ZP.on("change", this.onI18nChange), window.addEventListener("keydown", this.handleKeyDown)
            }

            componentWillUnmount() {
                l.ZP.off("change", this.onI18nChange), window.removeEventListener("keydown", this.handleKeyDown)
            }

            renderDeliverableForm() {
                const {selectedDeliverableTarget: e, deliverableTitle: t} = this.state, {translate: s} = this.props;
                if (!e) return;
                const n = this.getOriginalIds().length;
                return o.createElement("div", {className: "masterbar community-translator__bar"}, o.createElement("form", {
                    className: "community-translator__bar-form",
                    onSubmit: this.handleDeliverableSubmit
                }, o.createElement("div", {className: "community-translator__bar-label"}, s("%d string found.", "%d strings found.", {
                    count: n,
                    args: [n]
                }), " ", s("Enter a title:")), o.createElement(Y.Z, {
                    autoFocus: !0,
                    value: t,
                    onChange: this.handleDeliverableTitleChange
                }), o.createElement(L.Z, {
                    href: this.getCreateDeliverableUrl(),
                    target: "_blank",
                    onClick: this.handleDeliverableLinkClick,
                    primary: !0
                }, s("Create Deliverable")), o.createElement(L.Z, {onClick: this.handleDeliverableCancelClick}, s("Cancel"))))
            }

            renderDeliverableHighlight() {
                const {deliverableTarget: e, selectedDeliverableTarget: t, scrollTop: s} = this.state, n = e || t;
                if (!n) return null;
                const {left: r, top: i, width: a, height: l} = n.getBoundingClientRect(),
                    c = {transform: `translate(${r}px, ${i + s}px)`, width: `${a}px`, height: `${l}px`};
                return d.createPortal(o.createElement(o.Fragment, null, o.createElement("div", {
                    ref: this.highlightRef,
                    className: "community-translator__highlight",
                    style: c
                }), this.renderDeliverableForm()), document.body)
            }

            renderConfirmationModal() {
                const {translate: e} = this.props, t = [{action: "cancel", label: e("OK")}];
                return o.createElement(F.Z, {
                    isVisible: !0,
                    buttons: t,
                    onClose: this.infoDialogClose,
                    additionalClassNames: "community-translator__modal"
                }, o.createElement("h1", null, e("Community Translator")), o.createElement("p", null, e("You have now enabled the translator. Right click the text to translate it.")), o.createElement("p", null, o.createElement(ee.Z, {htmlFor: "toggle"}, o.createElement(X.Z, {
                    id: "toggle",
                    onClick: this.toggleInfoCheckbox
                }), o.createElement("span", null, e("Don't show again")))))
            }

            render() {
                const {translate: e, isEmpathyModeEnabled: t, selectedLanguageSlug: s} = this.props, {isEnabled: n, isActive: r, infoDialogVisible: i} = this.state,
                    a = g()("community-translator", {"is-active": r, "is-incompatible": t}),
                    l = e(r ? "Disable Translator" : "Enable Translator"),
                    d = (0, z.V0)() ? "Deactivate Empathy mode" : "Activate Empathy mode", u = t ? d : l, h = n || t;
                return o.createElement(o.Fragment, null, o.createElement(J.Z, null), h && o.createElement(o.Fragment, null, o.createElement("div", {className: a}, o.createElement("button", {
                    onClick: this.toggle,
                    className: "community-translator__button",
                    title: e("Community Translator")
                }, o.createElement(c.Z, {icon: "globe"}), t && o.createElement("span", {className: "community-translator__badge"}, s), o.createElement("div", {className: "community-translator__text"}, u))), i && this.renderConfirmationModal()), this.renderDeliverableHighlight())
            }
        }

        r()(te, "propTypes", {translate: a().func}), r()(te, "translationScanner", p.ZP.isEnabled("i18n/translation-scanner") && new class {
            constructor(e = !0) {
                r()(this, "sendPendingOriginals", (0, M.Z)(this._sendPendingOriginalsImmediately.bind(this), 500, {maxWait: 500})), Object.assign(this, {
                    installed: !1,
                    active: !1,
                    pendingOriginals: {},
                    sessionId: null,
                    cookieWatcherInterval: null,
                    previousCookies: null
                }), e && this.install()
            }

            translationFilter(...e) {
                const [t, s] = e;
                return this.active && this.sessionId && this.recordOriginal(s.original, s.context || ""), t
            }

            install() {
                return this.installed || "undefined" == typeof document || (W("Installing Translation Scanner"), (0, l.Xd)(this.translationFilter.bind(this)), this.cookieWatcherInterval = setInterval(this.checkCookie.bind(this), 1e3), this.installed = !0, this.checkCookie()), this
            }

            uninstall() {
                return W("stopping cookie watcher"), clearInterval(this.cookieWatcherInterval), this.cookieWatcherInterval = null, this.installed = !1, this
            }

            checkCookie() {
                if ("undefined" == typeof document) return void W("no document in checkCookie");
                if (this.previousCookies === document.cookies) return;
                const e = H.Q(document.cookie)["gp-record"];
                e !== this.sessionId && (W("New session Id:", e), this.setSessionId(e))
            }

            recordOriginal(e, t = "") {
                this.pendingOriginals[(0, q.cB)({original: e, context: t})] = !0, this.sendPendingOriginals()
            }

            _sendPendingOriginalsImmediately() {
                const e = Object.keys(this.pendingOriginals);
                e.length && (W(`Sending ${e.length} originals to GP_Record`), (0, q.zZ)(e), this.pendingOriginals = {})
            }

            setSessionId(e) {
                this.sessionId = e, e ? this.start() : this.stop()
            }

            start() {
                return W("Translation Scanner started"), this.clear(), this.active = !0, this
            }

            stop() {
                return W("Translation Scanner stopped"), this.active = !1, this
            }

            clear() {
                return this.pendingOriginals = {}, this
            }
        });
        const se = (0, u.$j)((e => ({
            isUserSettingsReady: !!(0, G.Z)(e),
            isTranslatorEnabled: B(e, "enable_translator"),
            isEmpathyModeEnabled: p.ZP.isEnabled("i18n/empathy-mode") && B(e, "i18n_empathy_mode"),
            selectedLanguageSlug: (0, Q.Z)(e)
        })), {setLocale: K.i})((0, l.NC)(te))
    }, "./lib/load-jquery-dependent-script-desktop-wrapper/index.js": (e, t, s) => {
        "use strict";
        s.d(t, {t: () => a});
        var n = s("../packages/load-script/src/index.js"),
            r = (s("./config/index.js"), s("../node_modules/debug/src/browser.js"));
        const i = s.n(r)()("lib/load-jquery-dependent-script-desktop-wrapper");

        function a(e, t) {
            i(`Loading a jQuery dependent script from "${e}"`), (0, n.D1)(e, t)
        }
    }, "./state/selectors/get-unsaved-user-settings.js": (e, t, s) => {
        "use strict";

        function n(e) {
            return e.userSettings.unsavedSettings
        }

        s.d(t, {Z: () => n})
    }, "./state/selectors/get-user-settings.js": (e, t, s) => {
        "use strict";

        function n(e) {
            return e.userSettings.settings
        }

        s.d(t, {Z: () => n})
    }, "./state/user-settings/actions.js": (e, t, s) => {
        "use strict";
        s.d(t, {KV: () => E, j_: () => j, _L: () => _, vz: () => w, uF: () => S, If: () => T});
        var n = s("../node_modules/debug/src/browser.js"), r = s.n(n), i = s("../node_modules/lodash-es/get.js"),
            a = s("./state/selectors/get-user-settings.js"), l = s("../node_modules/lodash-es/mapValues.js"),
            o = s("../node_modules/lodash-es/isEmpty.js"), d = s("../node_modules/lodash-es/noop.js"),
            c = s("../packages/i18n-calypso/src/index.js"), u = s("./lib/formatting/decode-entities.js"),
            h = s("./state/data-layer/wpcom-http/utils.js"), g = s("./state/selectors/get-unsaved-user-settings.js"),
            m = s("./state/data-layer/wpcom-http/actions.js"), p = s("./state/data-layer/handler-registry.js"),
            b = s("./state/notices/actions.js");
        const f = new Set(["display_name", "description", "user_URL"]),
            v = e => (0, l.Z)(e, ((e, t) => f.has(t) ? (0, u.S)(e) : e));
        (0, p.Z9)("state/data-layer/wpcom/me/settings/index.js", {
            USER_SETTINGS_REQUEST: [(0, h.BN)({
                fetch: e => (0, m.d)({
                    apiVersion: "1.1",
                    method: "GET",
                    path: "/me/settings"
                }, e), onSuccess: (e, t) => S(t), onError: d.Z, fromApi: v
            })], USER_SETTINGS_SAVE: [(0, h.BN)({
                fetch: function (e) {
                    return (t, s) => {
                        const {settingsOverride: n} = e, r = n || (0, g.Z)(s());
                        (0, o.Z)(r) || t((0, m.d)({
                            apiVersion: "1.1",
                            method: "POST",
                            path: "/me/settings",
                            body: r
                        }, e))
                    }
                }, onSuccess: ({settingsOverride: e}, t) => n => {
                    if (n(S(v(t))), n(E(e ? Object.keys(e) : null)), null != e && e.password) return void (window.location = window.location.pathname + "?updated=password");
                    const r = s("./lib/user/index.js");
                    (r.default ? r.default : r)().fetch(), n((0, b.RT)((0, c.Iu)("Settings saved successfully!"), {id: "save-user-settings"}))
                }, onError: function ({settingsOverride: e}, t) {
                    return null != e && e.password ? [(0, b.tF)((0, c.Iu)("There was a problem saving your password. Please, try again."), {id: "save-user-settings"}), w(e, t)] : [(0, b.tF)(t.message || (0, c.Iu)("There was a problem saving your changes."), {id: "save-user-settings"}), w(e, t)]
                }, fromApi: v
            })]
        });
        const y = r()("calypso:user:settings"), j = () => ({type: "USER_SETTINGS_REQUEST"}),
            _ = e => ({type: "USER_SETTINGS_SAVE", settingsOverride: e}),
            S = e => ({type: "USER_SETTINGS_SAVE_SUCCCESS", settingValues: e}),
            w = (e, t) => ({type: "USER_SETTINGS_SAVE_FAILURE", settingsOverride: e, error: t}),
            E = (e = null) => ({type: "USER_SETTINGS_UNSAVED_CLEAR", settingNames: e});

        function T(e, t) {
            return (s, n) => {
                const r = (0, a.Z)(n());
                return void 0 === (0, i.Z)(r, e) ? (y(e + " does not exist in user-settings data module."), !1) : (r[e] === t && "user_login" !== e ? (y("Removing " + e + " from changed settings."), s((e => ({
                    type: "USER_SETTINGS_UNSAVED_REMOVE",
                    settingName: e
                }))(e))) : s(((e, t) => ({type: "USER_SETTINGS_UNSAVED_SET", settingName: e, value: t}))(e, t)), !0)
            }
        }
    }
}]);