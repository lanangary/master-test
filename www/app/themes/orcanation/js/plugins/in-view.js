/*!
 * kudago-in-view 2.0.2 - Get notified when a DOM element enters or exits the viewport.
 * Copyright (c) 2017 Yura Trambitskiy <tram.yura@gmail.com> - https://github.com/kudago/in-view
 * License: MIT
 */
!(function(t, e) {
    'object' == typeof exports && 'object' == typeof module
        ? (module.exports = e())
        : 'function' == typeof define && define.amd
            ? define([], e)
            : 'object' == typeof exports
                ? (exports.inView = e())
                : (t.inView = e());
})(this, function() {
    return (function(t) {
        function e(r) {
            if (n[r]) return n[r].exports;
            var o = (n[r] = { exports: {}, id: r, loaded: !1 });
            return t[r].call(o.exports, o, o.exports, e), (o.loaded = !0), o.exports;
        }
        var n = {};
        return (e.m = t), (e.c = n), (e.p = ''), e(0);
    })([
        function(t, e, n) {
            'use strict';
            function r(t) {
                return t && t.__esModule ? t : { default: t };
            }
            Object.defineProperty(e, '__esModule', { value: !0 });
            var o = n(2),
                i = r(o),
                u = n(3),
                f = n(8),
                s = r(f),
                c = function() {
                    if ('undefined' != typeof window) {
                        var t = 100,
                            e = ['scroll', 'resize', 'load'],
                            n = { history: [] },
                            r = { offset: {}, threshold: 0, test: u.inViewport },
                            o = (0, s.default)(function() {
                                n.history.forEach(function(t) {
                                    n[t].check();
                                });
                            }, t);
                        e.forEach(function(t) {
                            return addEventListener(t, o);
                        }),
                            window.MutationObserver &&
                                addEventListener('DOMContentLoaded', function() {
                                    new MutationObserver(o).observe(document.body, {
                                        attributes: !0,
                                        childList: !0,
                                        subtree: !0
                                    });
                                });
                        var f = function(t) {
                            if ('string' == typeof t) {
                                var e = [].slice.call(document.querySelectorAll(t));
                                return (
                                    n.history.indexOf(t) > -1
                                        ? (n[t].elements = e)
                                        : ((n[t] = (0, i.default)(e, r)),
                                          n.history.push(t)),
                                    n[t]
                                );
                            }
                        };
                        return (
                            (f.offset = function(t) {
                                if (void 0 === t) return r.offset;
                                var e = function(t) {
                                    return 'number' == typeof t;
                                };
                                return (
                                    ['top', 'right', 'bottom', 'left'].forEach(
                                        e(t)
                                            ? function(e) {
                                                  return (r.offset[e] = t);
                                              }
                                            : function(n) {
                                                  return e(t[n])
                                                      ? (r.offset[n] = t[n])
                                                      : null;
                                              }
                                    ),
                                    r.offset
                                );
                            }),
                            (f.threshold = function(t) {
                                return 'number' == typeof t && t >= 0 && t <= 1
                                    ? (r.threshold = t)
                                    : r.threshold;
                            }),
                            (f.test = function(t) {
                                return 'function' == typeof t ? (r.test = t) : r.test;
                            }),
                            (f.is = function(t) {
                                return r.test(t, r);
                            }),
                            f.offset(0),
                            f
                        );
                    }
                };
            e.default = c;
        },
        function(t, e) {
            function n(t) {
                var e = typeof t;
                return null != t && ('object' == e || 'function' == e);
            }
            t.exports = n;
        },
        function(t, e) {
            'use strict';
            function n(t, e) {
                if (!(t instanceof e))
                    throw new TypeError('Cannot call a class as a function');
            }
            Object.defineProperty(e, '__esModule', { value: !0 });
            var r = (function() {
                    function t(t, e) {
                        for (var n = 0; n < e.length; n++) {
                            var r = e[n];
                            (r.enumerable = r.enumerable || !1),
                                (r.configurable = !0),
                                'value' in r && (r.writable = !0),
                                Object.defineProperty(t, r.key, r);
                        }
                    }
                    return function(e, n, r) {
                        return n && t(e.prototype, n), r && t(e, r), e;
                    };
                })(),
                o = (function() {
                    function t(e, r) {
                        n(this, t),
                            (this.options = r),
                            (this.elements = e),
                            (this.current = []),
                            (this.handlers = { enter: [], exit: [] }),
                            (this.singles = { enter: [], exit: [] });
                    }
                    return (
                        r(t, [
                            {
                                key: 'check',
                                value: function() {
                                    var t = this;
                                    return (
                                        this.elements.forEach(function(e) {
                                            var n = t.options.test(e, t.options),
                                                r = t.current.indexOf(e),
                                                o = r > -1,
                                                i = n && !o,
                                                u = !n && o;
                                            i && (t.current.push(e), t.emit('enter', e)),
                                                u &&
                                                    (t.current.splice(r, 1),
                                                    t.emit('exit', e));
                                        }),
                                        this
                                    );
                                }
                            },
                            {
                                key: 'on',
                                value: function(t, e) {
                                    return this.handlers[t].push(e), this;
                                }
                            },
                            {
                                key: 'once',
                                value: function(t, e) {
                                    return this.singles[t].unshift(e), this;
                                }
                            },
                            {
                                key: 'off',
                                value: function(t, e) {
                                    return (
                                        (this.handlers[t] = this.handlers[t].filter(
                                            function(t) {
                                                return t !== e;
                                            }
                                        )),
                                        this
                                    );
                                }
                            },
                            {
                                key: 'emit',
                                value: function(t, e) {
                                    for (; this.singles[t].length; )
                                        this.singles[t].pop()(e);
                                    for (var n = this.handlers[t].length; --n > -1; )
                                        this.handlers[t][n](e);
                                    return this;
                                }
                            }
                        ]),
                        t
                    );
                })();
            e.default = function(t, e) {
                return new o(t, e);
            };
        },
        function(t, e) {
            'use strict';
            function n(t, e) {
                var n = t.getBoundingClientRect(),
                    r = n.top,
                    o = n.right,
                    i = n.bottom,
                    u = n.left,
                    f = n.width,
                    s = n.height,
                    c = {
                        t: i,
                        r: window.innerWidth - u,
                        b: window.innerHeight - r,
                        l: o
                    },
                    a = { x: e.threshold * f, y: e.threshold * s },
                    l =
                        (c.t > e.offset.top + a.y && c.b > e.offset.bottom + a.y) ||
                        (c.t < -e.offset.top && c.b < -e.offset.bottom),
                    h =
                        (c.r > e.offset.right + a.x && c.l > e.offset.left + a.x) ||
                        (c.r < -e.offset.right && c.l < -e.offset.left);
                return l && h;
            }
            Object.defineProperty(e, '__esModule', { value: !0 }), (e.inViewport = n);
        },
        function(t, e) {
            (function(e) {
                var n = 'object' == typeof e && e && e.Object === Object && e;
                t.exports = n;
            }.call(
                e,
                (function() {
                    return this;
                })()
            ));
        },
        function(t, e, n) {
            var r = n(4),
                o = 'object' == typeof self && self && self.Object === Object && self,
                i = r || o || Function('return this')();
            t.exports = i;
        },
        function(t, e, n) {
            function r(t, e, n) {
                function r(e) {
                    var n = m,
                        r = x;
                    return (m = x = void 0), (E = e), (w = t.apply(r, n));
                }
                function a(t) {
                    return (E = t), (j = setTimeout(d, e)), M ? r(t) : w;
                }
                function l(t) {
                    var n = t - O,
                        r = t - E,
                        o = e - n;
                    return k ? c(o, g - r) : o;
                }
                function h(t) {
                    var n = t - O,
                        r = t - E;
                    return void 0 === O || n >= e || n < 0 || (k && r >= g);
                }
                function d() {
                    var t = i();
                    return h(t) ? p(t) : void (j = setTimeout(d, l(t)));
                }
                function p(t) {
                    return (j = void 0), T && m ? r(t) : ((m = x = void 0), w);
                }
                function v() {
                    void 0 !== j && clearTimeout(j), (E = 0), (m = O = x = j = void 0);
                }
                function y() {
                    return void 0 === j ? w : p(i());
                }
                function b() {
                    var t = i(),
                        n = h(t);
                    if (((m = arguments), (x = this), (O = t), n)) {
                        if (void 0 === j) return a(O);
                        if (k) return (j = setTimeout(d, e)), r(O);
                    }
                    return void 0 === j && (j = setTimeout(d, e)), w;
                }
                var m,
                    x,
                    g,
                    w,
                    j,
                    O,
                    E = 0,
                    M = !1,
                    k = !1,
                    T = !0;
                if ('function' != typeof t) throw new TypeError(f);
                return (
                    (e = u(e) || 0),
                    o(n) &&
                        ((M = !!n.leading),
                        (k = 'maxWait' in n),
                        (g = k ? s(u(n.maxWait) || 0, e) : g),
                        (T = 'trailing' in n ? !!n.trailing : T)),
                    (b.cancel = v),
                    (b.flush = y),
                    b
                );
            }
            var o = n(1),
                i = n(7),
                u = n(9),
                f = 'Expected a function',
                s = Math.max,
                c = Math.min;
            t.exports = r;
        },
        function(t, e, n) {
            var r = n(5),
                o = function() {
                    return r.Date.now();
                };
            t.exports = o;
        },
        function(t, e, n) {
            function r(t, e, n) {
                var r = !0,
                    f = !0;
                if ('function' != typeof t) throw new TypeError(u);
                return (
                    i(n) &&
                        ((r = 'leading' in n ? !!n.leading : r),
                        (f = 'trailing' in n ? !!n.trailing : f)),
                    o(t, e, { leading: r, maxWait: e, trailing: f })
                );
            }
            var o = n(6),
                i = n(1),
                u = 'Expected a function';
            t.exports = r;
        },
        function(t, e) {
            function n(t) {
                return t;
            }
            t.exports = n;
        }
    ]);
});
