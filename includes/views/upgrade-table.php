<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<style>
	#wpo-wcpdf-settings { display: none; }

	div.upgrade-table-description {
		padding: 0 0 3em 1em;
	}

	div.upgrade-table-description h1 {
		font-family: serif;
		letter-spacing: -1px;
		font-size: 3em;
	}

	div.upgrade-table-description p {
		font-size: 1.1em;
	}

	#upgrade-table {
		width: 100%;
		border-collapse: collapse;
		font-size: 1.2em;
		margin-bottom: 3em;
	}

	#upgrade-table th,
	#upgrade-table td  {
		padding: 0.8em 2em;
		border-bottom: 1px solid #ccc;
		text-align: center;
	}

	#upgrade-table th {
		font-weight: normal;
		font-size: 1.1em;
	}

	#upgrade-table th:not(.last),
	#upgrade-table td:not(.last) {
		width: 200px;
	}

	#upgrade-table tr:last-child td {
		border: none;
	}

	#upgrade-table td.feature-label {
		text-align: left;
		padding-left: 1em;
		font-weight: bold;
		width: 500px;
	}

	#upgrade-table td.feature-label span.description {
		display: inline-block;
		padding-top: 10px;
		font-size: 0.8em;
		line-height: 1.4em;
		font-weight: normal;
		color: #555;
	}

	#upgrade-table td span.feature-available {
		display: inline-block;
		width: 24px;
		height: 24px;
		background-image: url("<?php echo WPO_WCPDF()->plugin_url() . "/assets/images/checkmark.svg"; ?>");
		background-repeat: no-repeat;
		background-size: cover;
	}

	div.upgrade-table-description a,
	#upgrade-table a {
		color: #6e1edc;
		white-space: nowrap;

	}

	#upgrade-table .upgrade-links a {
		display: inline-block;
		background: white;
		padding: 1em 3em 1em 2em;
		border-radius: 12px;
		border: 1px solid #6e1edc;
		text-decoration: none;
		margin: 2em 0;
		position: relative;
	}

	#upgrade-table .upgrade-links a:after {
		content: ' \2192'; /* ASCII code for right arrow */
		display: block;
		position: absolute;
		right: 1.8em;
		top: 1.1em;
		transition: 0.5s;
	}

	#upgrade-table .upgrade-links a:hover:after {
		right: 1.1em;
		font-weight: bold;
	}
	

	#upgrade-table .upgrade-links a:focus,
	#upgrade-table .upgrade-links a:hover {
		background: #6e1edc;
		color: #fcfbf7;
	}

	@media screen and (max-width: 1100px) {
		#upgrade-table {
			font-size: 1em;
			line-height: 1.2em;
		}

		#upgrade-table th:not(.last),
		#upgrade-table td:not(.last) {
			width: 25%;
			padding: 0.8em 1em;
		}

		#upgrade-table th.last,
		#upgrade-table td.last {
			width: 0;
			padding: 0;
		}

		#upgrade-table td.feature-label span.description {
			padding-top: 6px;
		}
	}

	@media screen and (max-width: 767px) {
		#upgrade-table td.feature-label span.description {
			display: none;
		}

		#upgrade-table th:not(.last),
		#upgrade-table td:not(.last) {
			width: 20%;
		}

		#upgrade-table td.first {
			width: 40%;
		}
	}

	@media screen and (max-width: 649px) {

		div.upgrade-table-description {
			padding-left: 0.8em;
		}

		div.upgrade-table-description p {
			font-size: 1em;
		}

		#upgrade-table {
			font-size: 0.8em;
		}

		#upgrade-table th,
		#upgrade-table td {
			padding: 0.5em 0.8em!important;
		}

		#upgrade-table td span.feature-available {
			width: 18px;
			height: 18px;
		}

		#upgrade-table .upgrade-links a {
			white-space: normal;
			padding: 0.6em 0.8em;
			border-radius: 6px;
		}

		#upgrade-table .upgrade-links a:after {
			display: none;
		}
	}
</style>

<div class="upgrade-table-description">
	<h1><?php esc_html_e( 'Wait, there is more...', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
	<p>
		<span><?php esc_html_e( 'A quick overview of the features our PDF Invoices & Packing Slips extensions have to offer.', 'woocommerce-pdf-invoices-packing-slips' ); ?><span>
		<span><?php printf( '%s: %s', esc_html_e( 'If you have any questions feel free to send us an email at', 'woocommerce-pdf-invoices-packing-slips' ), '<a href="mailto:support@wpovernight.com">support@wpovernight.com</a>' ); ?><span>
	</p>
</div>

<table id="upgrade-table">
	<tr>
		<th class="first" align="left">&nbsp;</th>
		<th align="left"><?php esc_html_e( 'Professional', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left"><?php esc_html_e( 'Premium Templates', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left"><?php esc_html_e( 'PDF Invoices Bundle', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="last">&nbsp;</td>
	</tr>

	<?php
	foreach ( $features as $feature ) {
		echo '<tr><td class="first feature-label">' . $feature['label'];
		echo ! empty( $feature['description'] ) ? '<br><span class="description">' . $feature['description'] . '</span></td>' : '</td>';
		foreach ( ['pro', 'templates', 'bundle'] as $extension ) {
			echo in_array( $extension, $feature['extensions'] ) ? '<td><span class="feature-available"></span></td>' : '<td>-</td>';
		}
		echo '<td align="left" class="last">&nbsp;</td></tr>';
	}
	?>
	<tr class="upgrade-links">
		<td class="first" align="left">&nbsp;</td>
		<?php
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		?>
		<td align="left" class="last">&nbsp;</td>
	</tr>

	
</table>