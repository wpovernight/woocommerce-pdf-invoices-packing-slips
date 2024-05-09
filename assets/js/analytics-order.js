(() => {
	"use strict";
	function r(r) {
		return (
			(function (r) {
				if (Array.isArray(r)) return e(r);
			})(r) ||
			(function (r) {
				if (("undefined" != typeof Symbol && null != r[Symbol.iterator]) || null != r["@@iterator"]) return Array.from(r);
			})(r) ||
			(function (r, t) {
				if (r) {
					if ("string" == typeof r) return e(r, t);
					var n = Object.prototype.toString.call(r).slice(8, -1);
					return "Object" === n && r.constructor && (n = r.constructor.name), "Map" === n || "Set" === n ? Array.from(r) : "Arguments" === n || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n) ? e(r, t) : void 0;
				}
			})(r) ||
			(function () {
				throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
			})()
		);
	}
	function e(r, e) {
		(null == e || e > r.length) && (e = r.length);
		for (var t = 0, n = new Array(e); t < e; t++) n[t] = r[t];
		return n;
	}
	(0, wp.hooks.addFilter)("woocommerce_admin_report_table", "wpo-wc-admin-invoice-column", function (e) {
		return "orders" !== e.endpoint
			? e
			: ((e.headers = [].concat(r(e.headers), [{ label: wpo_wcpdf_analytics_order.label, key: "order_invoice_number", screenReaderLabel: wpo_wcpdf_analytics_order.label, isSortable: !1 }])),
				e.items && e.items.data && e.items.data.length
					? ((e.rows = e.rows.map(function (t, n) {
						var o = e.items.data[n];
						return [].concat(r(t), [{ display: o.invoice_number, value: o.invoice_number }]);
					})),
						e)
					: e);
	});
})();
