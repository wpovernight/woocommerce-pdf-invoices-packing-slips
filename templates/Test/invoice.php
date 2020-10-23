<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'wpo_wcpdf_before_document', $this->type, $this->order ); ?>

<!-- Test 1 -->
<!--
	DOMPDF
		Big logo: x
		Small logo: ✓
	mPDF
		Big logo: ✓
		Small logo: x
-->
<div style="width:100%; overflow:auto; margin-bottom:5mm;">
	<?php if( $this->has_header_logo() ) : ?>
		<div class="logo" style="float:left; pading-right:1cm;"><?php $this->header_logo(); ?></div>
	<?php endif; ?>
	&nbsp;&nbsp;
	<div style="width:40%; float:right;">
		<div><h3><?php $this->shop_name(); ?></h3></div>
		<div style="margin-bottom:5mm;"><?php $this->shop_address(); ?></div>
	</div>
</div>

<div class="page-break"></div>

<!-- Test 2 -->
<!--
	DOMPDF
		Big logo: x
		Small logo: ✓
	mPDF
		Big logo: x
		Small logo: ✓
-->
<div style="width:100%; overflow:auto; margin-bottom:5mm;">
	<div style="width:40%; float:right;">
		<div><h3><?php $this->shop_name(); ?></h3></div>
		<div style="margin-bottom:5mm;"><?php $this->shop_address(); ?></div>
	</div>
	&nbsp;&nbsp;
	<?php if( $this->has_header_logo() ) : ?>
		<div class="logo" style="float:left; pading-right:1cm;"><?php $this->header_logo(); ?></div>
	<?php endif; ?>
</div>

<div class="page-break"></div>

<!-- Test 3 -->
<!--
	DOMPDF
		Big logo: x
		Small logo: ✓
	mPDF
		Big logo: ✓
		Small logo: x
-->
<div style="width:100%; overflow:auto; margin-bottom:5mm;">
	<?php if( $this->has_header_logo() ) : ?>
		<div class="logo" style="display:inline-block; pading-right:1cm;"><?php $this->header_logo(); ?></div>
	<?php endif; ?>
	&nbsp;&nbsp;
	<div style="width:40%; display:inline-block">
		<div><h3><?php $this->shop_name(); ?></h3></div>
		<div style="margin-bottom:5mm;"><?php $this->shop_address(); ?></div>
	</div>
</div>