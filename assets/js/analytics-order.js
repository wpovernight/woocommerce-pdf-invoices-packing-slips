(() => {
    "use strict";
    const e = wp.hooks,
        r = wp.i18n;
    function t(e) {
        return (
            (function (e) {
                if (Array.isArray(e)) return n(e);
            })(e) ||
            (function (e) {
                if (("undefined" != typeof Symbol && null != e[Symbol.iterator]) || null != e["@@iterator"]) return Array.from(e);
            })(e) ||
            (function (e, r) {
                if (e) {
                    if ("string" == typeof e) return n(e, r);
                    var t = Object.prototype.toString.call(e).slice(8, -1);
                    return "Object" === t && e.constructor && (t = e.constructor.name), "Map" === t || "Set" === t ? Array.from(e) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? n(e, r) : void 0;
                }
            })(e) ||
            (function () {
                throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
            })()
        );
    }
    function n(e, r) {
        (null == r || r > e.length) && (r = e.length);
        for (var t = 0, n = new Array(r); t < r; t++) n[t] = e[t];
        return n;
    }
    (0, e.addFilter)("woocommerce_admin_report_table", "wpo-", function (e) {
        return "orders" !== e.endpoint
            ? e
            : ((e.headers = [].concat(t(e.headers), [
                { label: (0, r.__)("Invoice Number", "woocommerce-pdf-invoices-packing-slips"), key: "order_invoice_number", screenReaderLabel: (0, r.__)("Invoice Number", "woocommerce-pdf-invoices-packing-slips"), isSortable: !1 },
            ])),
                e.items && e.items.data && e.items.data.length
                    ? ((e.rows = e.rows.map(function (r, n) {
                        var o = e.items.data[n];
                        return [].concat(t(r), [{ display: o.invoice_number, value: o.invoice_number }]);
                    })),
                        e)
                    : e);
    });
})();
