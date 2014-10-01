<h3 id="system">System Configuration</h3>

<?php
require_once(WooCommerce_PDF_Invoices::$plugin_path."lib/dompdf/dompdf_config.inc.php");

$memory_limit = function_exists('wc_let_to_num')?wc_let_to_num( WP_MEMORY_LIMIT ):woocommerce_let_to_num( WP_MEMORY_LIMIT );

$server_configs = array(
	"PHP Version" => array(
		"required" => "5.0",
		"value"    => phpversion(),
		"result"   => version_compare(phpversion(), "5.0"),
	),
	"DOMDocument extension" => array(
		"required" => true,
		"value"    => phpversion("DOM"),
		"result"   => class_exists("DOMDocument"),
	),
	"PCRE" => array(
		"required" => true,
		"value"    => phpversion("pcre"),
		"result"   => function_exists("preg_match") && @preg_match("/./u", "a"),
		"failure"  => "PCRE is required with Unicode support (the \"u\" modifier)",
	),
	"Zlib" => array(
		"required" => true,
		"value"    => phpversion("zlib"),
		"result"   => function_exists("gzcompress"),
		"fallback" => "Recommended to compress PDF documents",
	),
	"MBString extension" => array(
		"required" => true,
		"value"    => phpversion("mbstring"),
		"result"   => function_exists("mb_send_mail"), // Should never be reimplemented in dompdf
		"fallback" => "Recommended, will use fallback functions",
	),
	"GD" => array(
		"required" => true,
		"value"    => phpversion("gd"),
		"result"   => function_exists("imagecreate"),
		"fallback" => "Required if you have images in your documents",
	),
	"opcache" => array(
		"required" => "For better performances",
		"value"    => null,
		"result"   => false,
		"fallback" => "Recommended for better performances",
	),
	"GMagick or IMagick" => array(
		"required" => "Better with transparent PNG images",
		"value"    => null,
		"result"   => extension_loaded("gmagick") || extension_loaded("imagick"),
		"fallback" => "Recommended for better performances",
	),
	"WP Memory Limit" => array(
		"required" => 'Recommended: 64MB (128MB for optimal performance)<br/>See: <a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Increasing memory allocated to PHP</a>',
		"value"    => WP_MEMORY_LIMIT,
		"result"   => $memory_limit > 67108864,
	),

);

if (($xc = extension_loaded("xcache")) || ($apc = extension_loaded("apc")) || ($zop = extension_loaded("Zend OPcache")) || ($op = extension_loaded("opcache"))) {
	$server_configs["opcache"]["result"] = true;
	$server_configs["opcache"]["value"] = (
		$xc ? "XCache ".phpversion("xcache") : (
			$apc ? "APC ".phpversion("apc") : (
				$zop ? "Zend OPCache ".phpversion("Zend OPcache") : "PHP OPCache ".phpversion("opcache")
			)
		)
	);
}
if (($gm = extension_loaded("gmagick")) || ($im = extension_loaded("imagick"))) {
	$server_configs["GMagick or IMagick"]["value"] = ($im ? "IMagick ".phpversion("imagick") : "GMagick ".phpversion("gmagick"));
}

?>

<table cellspacing="1px" cellpadding="4px" style="background-color: white; padding: 5px; border: 1px solid #ccc;">
	<tr>
		<th align="left">&nbsp;</th>
		<th align="left">Required</th>
		<th align="left">Present</th>
	</tr>

	<?php foreach($server_configs as $label => $server_config) {
		if ($server_config["result"]) {
			$background = "#9e4";
			$color = "black";
		} elseif (isset($server_config["fallback"])) {
			$background = "#FCC612";
			$color = "black";
		} else {
			$background = "#f43";
			$color = "white";
		}
		?>
		<tr>
			<td class="title"><?php echo $label; ?></td>
			<td><?php echo ($server_config["required"] === true ? "Yes" : $server_config["required"]); ?></td>
			<td style="background-color:<?php echo $background; ?>; color:<?php echo $color; ?>">
				<?php
				echo $server_config["value"];
				if ($server_config["result"] && !$server_config["value"]) echo "Yes";
				if (!$server_config["result"]) {
					if (isset($server_config["fallback"])) {
						echo "<div>No. ".$server_config["fallback"]."</div>";
					}
					if (isset($server_config["failure"])) {
						echo "<div>".$server_config["failure"]."</div>";
					}
				}
				?>
			</td>
		</tr>
	<?php } ?>

</table>

<?php
$permissions = array(
	'DOMPDF_FONT_DIR'		=> array (
			'description'		=> 'Directory containing fonts loaded into DOMPDF',
			'value'				=> DOMPDF_FONT_DIR,
			'status'			=> (is_writable(DOMPDF_FONT_DIR) ? "ok" : "failed"),			
			'status_message'	=> (is_writable(DOMPDF_FONT_DIR) ? "Writable" : "Not writable"),
		),
	'DOMPDF_FONT_CACHE'		=> array (
			'description'		=> 'Font metrics cache (used mainly by CPDF)',
			'value'				=> DOMPDF_FONT_CACHE,
			'status'			=> (is_writable(DOMPDF_FONT_CACHE) ? "ok" : "failed"),			
			'status_message'	=> (is_writable(DOMPDF_FONT_CACHE) ? "Writable" : "Not writable"),
		),
	'DOMPDF_TEMP_DIR'		=> array (
			'description'		=> 'Temporary PDF engine folder',
			'value'				=> DOMPDF_TEMP_DIR,
			'status'			=> (is_writable(DOMPDF_TEMP_DIR) ? "ok" : "failed"),			
			'status_message'	=> (is_writable(DOMPDF_TEMP_DIR) ? "Writable" : "Not writable"),
		),
	'DOMPDF_ENABLE_REMOTE'	=> array (
			'description'		=> 'Allow remote stylesheets and images',
			'value'				=> DOMPDF_ENABLE_REMOTE,
			'status'			=> (ini_get("allow_url_fopen")) ? "ok" : "failed",			
			'status_message'	=> (ini_get("allow_url_fopen")) ? "allow_url_fopen enabled" : "allow_url_fopen disabled",
		),
	'WCPDF_TEMP_DIR'		=> array (
			'description'		=> 'Temporary plugin folder',
			'value'				=> WooCommerce_PDF_Invoices::$plugin_path . 'tmp/',
			'status'			=> (is_writable(WooCommerce_PDF_Invoices::$plugin_path . 'tmp/') ? "ok" : "failed"),			
			'status_message'	=> (is_writable(WooCommerce_PDF_Invoices::$plugin_path . 'tmp/') ? "Writable" : "Not writable"),
		),
	);

?>
<br />
<h3 id="system">Permissions</h3>
<table cellspacing="1px" cellpadding="4px" style="background-color: white; padding: 5px; border: 1px solid #ccc;">
	<tr>
		<th align="left">Description</th>
		<th align="left">Value</th>
		<th align="left">Status</th>
	</tr>
	<?php
	foreach ($permissions as $permission) {
		if ($permission['status'] == 'ok') {
			$background = "#9e4";
			$color = "black";
		} else {
			$background = "#f43";
			$color = "white";
		}
		?>
	<tr>
		<td><?php echo $permission['description']; ?></td>
		<td><?php echo $permission['value']; ?></td>
		<td style="background-color:<?php echo $background; ?>; color:<?php echo $color; ?>"><?php echo $permission['status_message']; ?></td>
	</tr>

	<?php } ?>

</table>
