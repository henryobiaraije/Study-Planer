/*! This file is auto-generated */
window.wp = window.wp || {},
    function (l, g) {
        g.editor = g.editor || {},
            window.switchEditors = new function () {
                var h, v, t = {};

                function e() {
                    !h && window.tinymce && (h = window.tinymce,
                        (v = h.$)(document).on("click", function (e) {
                            e = v(e.target);
                            e.hasClass("wp-switch-editor") && n(e.attr("data-wp-editor-id"), e.hasClass("switch-tmce") ? "tmce" : "html")
                        }))
                }

                function b(e) {
                    e = v(".mce-toolbar-grp", e.getContainer())[0],
                        e = e && e.clientHeight;
                    return e && 10 < e && e < 200 ? parseInt(e, 10) : 30
                }

                function n(e, t) {
                    t                                   = t || "toggle";
                    var n, r, i, a, o, c, p, s, d, l, g = h.get(e = e || "content"), u = v("#wp-" + e + "-wrap"), w = v("#" + e), f = w[0];
                    if ("tmce" === (t = "toggle" === t ? g && !g.isHidden() ? "html" : "tmce" : t) || "tinymce" === t) {
                        if (g && !g.isHidden())
                            return !1;
                        void 0 !== window.QTags && window.QTags.closeAllTags(e);
                        var m = parseInt(f.style.height, 10) || 0;
                        (g ? g.getParam("wp_keep_scroll_position") : window.tinyMCEPreInit.mceInit[e] && window.tinyMCEPreInit.mceInit[e].wp_keep_scroll_position) && (a = w) && a.length && (o = a[0],
                            d = function (e, t) {
                                var n   = t.cursorStart
                                    , r = t.cursorEnd
                                    , t = x(e, n);
                                t && (n = -1 !== ["area", "base", "br", "col", "embed", "hr", "img", "input", "keygen", "link", "meta", "param", "source", "track", "wbr"].indexOf(t.tagType) ? t.ltPos : t.gtPos);
                                t = x(e, r);
                                t && (r = t.gtPos);
                                t = E(e, n);
                                t && !t.showAsPlainText && (n = t.urlAtStartOfContent ? t.endIndex : t.startIndex);
                                e = E(e, r);
                                e && !e.showAsPlainText && (r = e.urlAtEndOfContent ? e.startIndex : e.endIndex);
                                return {
                                    cursorStart: n,
                                    cursorEnd  : r
                                }
                            }(o.value, {
                                cursorStart: o.selectionStart,
                                cursorEnd  : o.selectionEnd
                            }),
                            c = d.cursorStart,
                            p = d.cursorEnd,
                            l = c !== p ? "range" : "single",
                            s = null,
                            a = y(v, "&#65279;").attr("data-mce-type", "bookmark"),
                        "range" == l && (d = o.value.slice(c, p),
                            l = a.clone().addClass("mce_SELRES_end"),
                            s = [d, l[0].outerHTML].join("")),
                            o.value = [o.value.slice(0, c), a.clone().addClass("mce_SELRES_start")[0].outerHTML, s, o.value.slice(p)].join("")),
                            g ? (g.show(),
                            !h.Env.iOS && m && 50 < (m = m - b(g) + 14) && m < 5e3 && g.theme.resizeTo(null, m),
                            g.getParam("wp_keep_scroll_position") && S(g)) : h.init(window.tinyMCEPreInit.mceInit[e]),
                            u.removeClass("html-active").addClass("tmce-active"),
                            w.attr("aria-hidden", !0),
                            window.setUserSetting("editor", "tinymce")
                    } else if ("html" === t) {
                        if (g && g.isHidden())
                            return !1;
                        g ? (h.Env.iOS || (m = (t = g.iframeElement) ? parseInt(t.style.height, 10) : 0) && 50 < (m = m + b(g) - 14) && m < 5e3 && (f.style.height = m + "px"),
                            m = null,
                        g.getParam("wp_keep_scroll_position") && (m = function (e) {
                            var t = e.getWin().getSelection();
                            if (t && !(t.rangeCount < 1)) {
                                var n   = "SELRES_" + Math.random()
                                    , r = y(e.$, n)
                                    , i = r.clone().addClass("mce_SELRES_start")
                                    , a = r.clone().addClass("mce_SELRES_end")
                                    , o = t.getRangeAt(0)
                                    , c = o.startContainer
                                    , p = o.startOffset
                                    , r = o.cloneRange();
                                0 < e.$(c).parents(".mce-offscreen-selection").length ? (c = e.$("[data-mce-selected]")[0],
                                    i.attr("data-mce-object-selection", "true"),
                                    a.attr("data-mce-object-selection", "true"),
                                    e.$(c).before(i[0]),
                                    e.$(c).after(a[0])) : (r.collapse(!1),
                                    r.insertNode(a[0]),
                                    r.setStart(c, p),
                                    r.collapse(!0),
                                    r.insertNode(i[0]),
                                    o.setStartAfter(i[0]),
                                    o.setEndBefore(a[0]),
                                    t.removeAllRanges(),
                                    t.addRange(o)),
                                    e.on("GetContent", _);
                                o = $(e.getContent());
                                e.off("GetContent", _),
                                    i.remove(),
                                    a.remove();
                                e = new RegExp('<span[^>]*\\s*class="mce_SELRES_start"[^>]+>\\s*' + n + "[^<]*<\\/span>(\\s*)"),
                                    i = new RegExp('(\\s*)<span[^>]*\\s*class="mce_SELRES_end"[^>]+>\\s*' + n + "[^<]*<\\/span>"),
                                    a = o.match(e),
                                    n = o.match(i);
                                if (!a)
                                    return null;
                                e = a.index,
                                    o = a[0].length,
                                    i = null;
                                return n && (-1 !== a[0].indexOf("data-mce-object-selection") && (o -= a[1].length),
                                    a = n.index,
                                -1 !== n[0].indexOf("data-mce-object-selection") && (a -= n[1].length),
                                    i = a - o),
                                    {
                                        start: e,
                                        end  : i
                                    }
                            }
                        }(g)),
                            g.hide(),
                        m && (g = g,
                        (m = m) && (n = g.getElement(),
                            r = m.start,
                            i = m.end || m.start,
                        n.focus && setTimeout(function () {
                            n.setSelectionRange(r, i),
                            n.blur && n.blur(),
                                n.focus()
                        }, 100)))) : w.css({
                            display   : "",
                            visibility: ""
                        }),
                            u.removeClass("tmce-active").addClass("html-active"),
                            w.attr("aria-hidden", !1),
                            window.setUserSetting("editor", "html")
                    }
                }

                function x(e, t) {
                    var n = e.lastIndexOf("<", t - 1);
                    if (e.lastIndexOf(">", t) < n || ">" === e.substr(t, 1)) {
                        var r   = e.substr(n)
                            , t = r.match(/<\s*(\/)?(\w+|\!-{2}.*-{2})/);
                        if (!t)
                            return null;
                        e = t[2];
                        return {
                            ltPos       : n,
                            gtPos       : n + r.indexOf(">") + 1,
                            tagType     : e,
                            isClosingTag: !!t[1]
                        }
                    }
                    return null
                }

                function E(e, t) {
                    for (var n = function (e) {
                        var t, n = function (e) {
                            var t   = e.match(/\[+([\w_-])+/g)
                                , n = [];
                            if (t)
                                for (var r = 0; r < t.length; r++) {
                                    var i = t[r].replace(/^\[+/g, "");
                                    -1 === n.indexOf(i) && n.push(i)
                                }
                            return n
                        }(e);
                        if (0 === n.length)
                            return [];
                        var r, i = g.shortcode.regexp(n.join("|")), a = [];
                        for (; r = i.exec(e);) {
                            var o = "[" === r[1];
                            t = {
                                shortcodeName  : r[2],
                                showAsPlainText: o,
                                startIndex     : r.index,
                                endIndex       : r.index + r[0].length,
                                length         : r[0].length
                            },
                                a.push(t)
                        }
                        var c = new RegExp('(^|[\\n\\r][\\n\\r]|<p>)(https?:\\/\\/[^s"]+?)(<\\/p>s*|[\\n\\r][\\n\\r]|$)', "gi");
                        for (; r = c.exec(e);)
                            t = {
                                shortcodeName      : "url",
                                showAsPlainText    : !1,
                                startIndex         : r.index,
                                endIndex           : r.index + r[0].length,
                                length             : r[0].length,
                                urlAtStartOfContent: "" === r[1],
                                urlAtEndOfContent  : "" === r[3]
                            },
                                a.push(t);
                        return a
                    }(e), r    = 0; r < n.length; r++) {
                        var i = n[r];
                        if (t >= i.startIndex && t <= i.endIndex)
                            return i
                    }
                }

                function y(e, t) {
                    return e("<span>").css({
                        display      : "inline-block",
                        width        : 0,
                        overflow     : "hidden",
                        "line-height": 0
                    }).html(t || "")
                }

                function S(e) {
                    var t, n = e.$(".mce_SELRES_start").attr("data-mce-bogus", 1), r = e.$(".mce_SELRES_end").attr("data-mce-bogus", 1);
                    n.length && (e.focus(),
                        r.length ? ((t = e.getDoc().createRange()).setStartAfter(n[0]),
                            t.setEndBefore(r[0]),
                            e.selection.setRng(t)) : e.selection.select(n[0])),
                    e.getParam("wp_keep_scroll_position") && function (e, t) {
                        var n, r = e.$(t).offset().top, i = e.$(e.getContentAreaContainer()).offset().top, a = b(e), o = l("#wp-content-editor-tools"), c = 0, t = 0;
                        o.length && (c = o.height(),
                            t = o.offset().top);
                        o = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
                            i += r,
                            a = o - (c + a);
                        i < a || (r = e.settings.wp_autoresize_on ? (n = l("html,body"),
                            Math.max(i - a / 2, t - c)) : (n = l(e.contentDocument).find("html,body"),
                            r),
                            n.animate({
                                scrollTop: parseInt(r, 10)
                            }, 100))
                    }(e, n),
                        i(n),
                        i(r),
                        e.save()
                }

                function i(e) {
                    var t = e.parent();
                    e.remove(),
                    !t.is("p") || t.children().length || t.text() || t.remove()
                }

                function _(e) {
                    e.content = e.content.replace(/<p>(?:<br ?\/?>|\u00a0|\uFEFF| )*<\/p>/g, "<p>&nbsp;</p>")
                }

                function $(e) {
                    var t   = "blockquote|ul|ol|li|dl|dt|dd|table|thead|tbody|tfoot|tr|th|td|h[1-6]|fieldset|figure"
                        , n = t + "|div|p"
                        , r = t + "|pre"
                        , i = !1
                        , t = !1
                        , a = [];
                    return e ? (-1 !== (e = -1 !== e.indexOf("<script") || -1 !== e.indexOf("<style") ? e.replace(/<(script|style)[^>]*>[\s\S]*?<\/\1>/g, function (e) {
                        return a.push(e),
                            "<wp-preserve>"
                    }) : e).indexOf("<pre") && (i = !0,
                        e = e.replace(/<pre[^>]*>[\s\S]+?<\/pre>/g, function (e) {
                            return (e = (e = e.replace(/<br ?\/?>(\r\n|\n)?/g, "<wp-line-break>")).replace(/<\/?p( [^>]*)?>(\r\n|\n)?/g, "<wp-line-break>")).replace(/\r?\n/g, "<wp-line-break>")
                        })),
                    -1 !== e.indexOf("[caption") && (t = !0,
                        e = e.replace(/\[caption[\s\S]+?\[\/caption\]/g, function (e) {
                            return e.replace(/<br([^>]*)>/g, "<wp-temp-br$1>").replace(/[\r\n\t]+/, "")
                        })),
                        e = (e = (e = (e = (e = -1 !== (e = -1 !== (e = -1 !== (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = e.replace(new RegExp("\\s*</(" + n + ")>\\s*", "g"), "</$1>\n")).replace(new RegExp("\\s*<((?:" + n + ")(?: [^>]*)?)>", "g"), "\n<$1>")).replace(/(<p [^>]+>.*?)<\/p>/g, "$1</p#>")).replace(/<div( [^>]*)?>\s*<p>/gi, "<div$1>\n\n")).replace(/\s*<p>/gi, "")).replace(/\s*<\/p>\s*/gi, "\n\n")).replace(/\n[\s\u00a0]+\n/g, "\n\n")).replace(/(\s*)<br ?\/?>\s*/gi, function (e, t) {
                            return t && -1 !== t.indexOf("\n") ? "\n\n" : "\n"
                        })).replace(/\s*<div/g, "\n<div")).replace(/<\/div>\s*/g, "</div>\n")).replace(/\s*\[caption([^\[]+)\[\/caption\]\s*/gi, "\n\n[caption$1[/caption]\n\n")).replace(/caption\]\n\n+\[caption/g, "caption]\n\n[caption")).replace(new RegExp("\\s*<((?:" + r + ")(?: [^>]*)?)\\s*>", "g"), "\n<$1>")).replace(new RegExp("\\s*</(" + r + ")>\\s*", "g"), "</$1>\n")).replace(/<((li|dt|dd)[^>]*)>/g, " \t<$1>")).indexOf("<option") ? (e = e.replace(/\s*<option/g, "\n<option")).replace(/\s*<\/select>/g, "\n</select>") : e).indexOf("<hr") ? e.replace(/\s*<hr( [^>]*)?>\s*/g, "\n\n<hr$1>\n\n") : e).indexOf("<object") ? e.replace(/<object[\s\S]+?<\/object>/g, function (e) {
                            return e.replace(/[\r\n]+/g, "")
                        }) : e).replace(/<\/p#>/g, "</p>\n")).replace(/\s*(<p [^>]+>[\s\S]*?<\/p>)/g, "\n$1")).replace(/^\s+/, "")).replace(/[\s\u00a0]+$/, ""),
                    i && (e = e.replace(/<wp-line-break>/g, "\n")),
                    t && (e = e.replace(/<wp-temp-br([^>]*)>/g, "<br$1>")),
                        a.length ? e.replace(/<wp-preserve>/g, function () {
                            return a.shift()
                        }) : e) : ""
                }

                function r(e) {
                    var t   = !1
                        , n = !1
                        , r = "table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary";
                    return -1 === (e = (e = -1 !== (e = e.replace(/\r\n|\r/g, "\n")).indexOf("<object") ? e.replace(/<object[\s\S]+?<\/object>/g, function (e) {
                        return e.replace(/\n+/g, "")
                    }) : e).replace(/<[^<>]+>/g, function (e) {
                        return e.replace(/[\n\t ]+/g, " ")
                    })).indexOf("<pre") && -1 === e.indexOf("<script") || (t = !0,
                        e = e.replace(/<(pre|script)[^>]*>[\s\S]*?<\/\1>/g, function (e) {
                            return e.replace(/\n/g, "<wp-line-break>")
                        })),
                    -1 !== (e = -1 !== e.indexOf("<figcaption") ? (e = e.replace(/\s*(<figcaption[^>]*>)/g, "$1")).replace(/<\/figcaption>\s*/g, "</figcaption>") : e).indexOf("[caption") && (n = !0,
                        e = e.replace(/\[caption[\s\S]+?\[\/caption\]/g, function (e) {
                            return (e = (e = e.replace(/<br([^>]*)>/g, "<wp-temp-br$1>")).replace(/<[^<>]+>/g, function (e) {
                                return e.replace(/[\n\t ]+/, " ")
                            })).replace(/\s*\n\s*/g, "<wp-temp-br />")
                        })),
                        e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e = (e += "\n\n").replace(/<br \/>\s*<br \/>/gi, "\n\n")).replace(new RegExp("(<(?:" + r + ")(?: [^>]*)?>)", "gi"), "\n\n$1")).replace(new RegExp("(</(?:" + r + ")>)", "gi"), "$1\n\n")).replace(/<hr( [^>]*)?>/gi, "<hr$1>\n\n")).replace(/\s*<option/gi, "<option")).replace(/<\/option>\s*/gi, "</option>")).replace(/\n\s*\n+/g, "\n\n")).replace(/([\s\S]+?)\n\n/g, "<p>$1</p>\n")).replace(/<p>\s*?<\/p>/gi, "")).replace(new RegExp("<p>\\s*(</?(?:" + r + ")(?: [^>]*)?>)\\s*</p>", "gi"), "$1")).replace(/<p>(<li.+?)<\/p>/gi, "$1")).replace(/<p>\s*<blockquote([^>]*)>/gi, "<blockquote$1><p>")).replace(/<\/blockquote>\s*<\/p>/gi, "</p></blockquote>")).replace(new RegExp("<p>\\s*(</?(?:" + r + ")(?: [^>]*)?>)", "gi"), "$1")).replace(new RegExp("(</?(?:" + r + ")(?: [^>]*)?>)\\s*</p>", "gi"), "$1")).replace(/(<br[^>]*>)\s*\n/gi, "$1")).replace(/\s*\n/g, "<br />\n")).replace(new RegExp("(</?(?:" + r + ")[^>]*>)\\s*<br />", "gi"), "$1")).replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi, "$1")).replace(/(?:<p>|<br ?\/?>)*\s*\[caption([^\[]+)\[\/caption\]\s*(?:<\/p>|<br ?\/?>)*/gi, "[caption$1[/caption]")).replace(/(<(?:div|th|td|form|fieldset|dd)[^>]*>)(.*?)<\/p>/g, function (e, t, n) {
                            return n.match(/<p( [^>]*)?>/) ? e : t + "<p>" + n + "</p>"
                        }),
                    t && (e = e.replace(/<wp-line-break>/g, "\n")),
                        e = n ? e.replace(/<wp-temp-br([^>]*)>/g, "<br$1>") : e
                }

                function a(e) {
                    e = {
                        o         : t,
                        data      : e,
                        unfiltered: e
                    };
                    return l && l("body").trigger("beforePreWpautop", [e]),
                        e.data = $(e.data),
                    l && l("body").trigger("afterPreWpautop", [e]),
                        e.data
                }

                function o(e) {
                    e = {
                        o         : t,
                        data      : e,
                        unfiltered: e
                    };
                    return l && l("body").trigger("beforeWpautop", [e]),
                        e.data = r(e.data),
                    l && l("body").trigger("afterWpautop", [e]),
                        e.data
                }

                return l(document).on("tinymce-editor-init.keep-scroll-position", function (e, t) {
                    t.$(".mce_SELRES_start").length && S(t)
                }),
                    l ? l(document).ready(e) : document.addEventListener ? (document.addEventListener("DOMContentLoaded", e, !1),
                        window.addEventListener("load", e, !1)) : window.attachEvent && (window.attachEvent("onload", e),
                        document.attachEvent("onreadystatechange", function () {
                            "complete" === document.readyState && e()
                        })),
                    g.editor.autop = o,
                    g.editor.removep = a,
                    t = {
                        go         : n,
                        wpautop    : o,
                        pre_wpautop: a,
                        _wp_Autop  : r,
                        _wp_Nop    : $
                    }
            }
            ,
            g.editor.initialize = function (e, t) {
                var n, r, i, a, o, c, p, s, d;
                l && e && g.editor.getDefaultSettings && (d = g.editor.getDefaultSettings(),
                (t = t || {
                    tinymce: !0
                }).tinymce && t.quicktags && (r = l("#" + e),
                    i = l("<div>").attr({
                        class: "wp-core-ui wp-editor-wrap tmce-active",
                        id   : "wp-" + e + "-wrap"
                    }),
                    a = l('<div class="wp-editor-container">'),
                    o = l("<button>").attr({
                        type               : "button",
                        "data-wp-editor-id": e
                    }),
                    c = l('<div class="wp-editor-tools">'),
                t.mediaButtons && (p = "Add Media",
                window._wpMediaViewsL10n && window._wpMediaViewsL10n.addMedia && (p = window._wpMediaViewsL10n.addMedia),
                    (s = l('<button type="button" class="button insert-media add_media">')).append('<span class="wp-media-buttons-icon"></span>'),
                    s.append(document.createTextNode(" " + p)),
                    s.data("editor", e),
                    c.append(l('<div class="wp-media-buttons">').append(s))),
                    i.append(c.append(l('<div class="wp-editor-tabs">').append(o.clone().attr({
                        id   : e + "-tmce",
                        class: "wp-switch-editor switch-tmce"
                    }).text(window.tinymce.translate("Visual"))).append(o.attr({
                        id   : e + "-html",
                        class: "wp-switch-editor switch-html"
                    }).text(window.tinymce.translate("Text")))).append(a)),
                    r.after(i),
                    a.append(r)),
                window.tinymce && t.tinymce && ("object" != typeof t.tinymce && (t.tinymce = {}),
                    (n = l.extend({}, d.tinymce, t.tinymce)).selector = "#" + e,
                    l(document).trigger("wp-before-tinymce-init", n),
                    window.tinymce.init(n),
                window.wpActiveEditor || (window.wpActiveEditor = e)),
                window.quicktags && t.quicktags && ("object" != typeof t.quicktags && (t.quicktags = {}),
                    (n = l.extend({}, d.quicktags, t.quicktags)).id = e,
                    l(document).trigger("wp-before-quicktags-init", n),
                    window.quicktags(n),
                window.wpActiveEditor || (window.wpActiveEditor = n.id)))
            }
            ,
            g.editor.remove = function (e) {
                var t, n, r = l("#wp-" + e + "-wrap");
                window.tinymce && (t = window.tinymce.get(e)) && (t.isHidden() || t.save(),
                    t.remove()),
                window.quicktags && (n = window.QTags.getInstance(e)) && n.remove(),
                r.length && (r.after(l("#" + e)),
                    r.remove())
            }
            ,
            g.editor.getContent = function (e) {
                var t;
                if (l && e)
                    return window.tinymce && (t = window.tinymce.get(e)) && !t.isHidden() && t.save(),
                        l("#" + e).val()
            }
    }(window.jQuery, window.wp);
