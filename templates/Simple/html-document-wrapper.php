<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!DOCTYPE html>
<html <?php echo esc_html( $this->language_attributes ); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php $this->title(); ?></title>
	<style type="text/css"><?php $this->template_styles(); ?></style>
	<style type="text/css"><?php do_action( 'wpo_wcpdf_custom_styles', $this->get_type(), $this ); ?></style>
</head>
<body class="<?php echo esc_html( $this->body_class ); ?>">
<?php echo $content; ?>
</body>
</html>
