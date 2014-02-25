<?php global $wpo_wcpdf; ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo ($wpo_wcpdf->export->template_type == 'invoice')?__( 'Invoice', 'wpo_wcpdf' ):__( 'Packing Slip', 'wpo_wcpdf' ) ?></title>
	<style><?php $wpo_wcpdf->template_styles(); ?></style>
</head>
<body>
<?php echo $wpo_wcpdf->export->output_body; ?>
</body>
</html>