import { addFilter } from '@wordpress/hooks';

addFilter(
	'woocommerce_admin_report_table',
	'wpo-wc-admin-invoice-column',
	(reportTableData) => {
		if (reportTableData.endpoint !== 'orders') {
			return reportTableData;
		}

		reportTableData.headers = [
			...reportTableData.headers,
			{
				label: wpo_wcpdf_analytics_order.label,
				key: 'order_invoice_number',
				screenReaderLabel: wpo_wcpdf_analytics_order.label,
				isSortable: false,
			},
		];

		if (
			! reportTableData.items ||
			! reportTableData.items.data ||
			! reportTableData.items.data.length
		) {
			return reportTableData;
		}

		reportTableData.rows = reportTableData.rows.map((row, index) => {
			const order = reportTableData.items.data[index];
			return [
				...row,
				{
					display: order.invoice_number,
					value: order.invoice_number,
				},
			];
		});

		return reportTableData;
	}
);
