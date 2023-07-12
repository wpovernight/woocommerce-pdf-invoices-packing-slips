import { addFilter } from '@wordpress/hooks';

addFilter(
    'woocommerce_admin_report_table',
    'wpo-',
    (reportTableData) => {
        if (reportTableData.endpoint !== 'orders') {
            return reportTableData;
        }

        reportTableData.headers = [
            ...reportTableData.headers,
            {
                label: 'Invoice Number',
                key: 'order_invoice_number',
                screenReaderLabel: 'Invoice Number',
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