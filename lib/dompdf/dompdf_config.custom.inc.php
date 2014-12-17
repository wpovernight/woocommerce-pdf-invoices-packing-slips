<?php 
global $wpo_wcpdf;
if ( is_object($wpo_wcpdf) ) {
	define("DOMPDF_TEMP_DIR", $wpo_wcpdf->export->tmp_path('DOMPDF_TEMP_DIR') );
	define("DOMPDF_FONT_DIR", $wpo_wcpdf->export->tmp_path('DOMPDF_FONT_DIR') ); //needs trailing slash!
	define("DOMPDF_FONT_CACHE", $wpo_wcpdf->export->tmp_path('DOMPDF_FONT_CACHE') );
}

//define("DOMPDF_CHROOT", DOMPDF_DIR);
//define("DOMPDF_UNICODE_ENABLED", true);
//define("DOMPDF_PDF_BACKEND", "PDFLib");
//define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
//define("DOMPDF_DEFAULT_PAPER_SIZE", "letter");
//define("DOMPDF_DEFAULT_FONT", "serif");
//define("DOMPDF_DPI", 72);
//define("DOMPDF_ENABLE_PHP", true);
//define("DOMPDF_ENABLE_REMOTE", true);
//define("DOMPDF_ENABLE_CSS_FLOAT", true);
//define("DOMPDF_ENABLE_JAVASCRIPT", false);
//define("DEBUGPNG", true);
//define("DEBUGKEEPTEMP", true);
//define("DEBUGCSS", true);
//define("DEBUG_LAYOUT", true);
//define("DEBUG_LAYOUT_LINES", false);
//define("DEBUG_LAYOUT_BLOCKS", false);
//define("DEBUG_LAYOUT_INLINE", false);
//define("DOMPDF_FONT_HEIGHT_RATIO", 1.0);
//define("DEBUG_LAYOUT_PADDINGBOX", false);
//define("DOMPDF_LOG_OUTPUT_FILE", DOMPDF_FONT_DIR."log.htm");
//define("DOMPDF_ENABLE_HTML5PARSER", true);
//define("DOMPDF_ENABLE_FONTSUBSETTING", true);

// DOMPDF authentication
//define("DOMPDF_ADMIN_USERNAME", "user");
//define("DOMPDF_ADMIN_PASSWORD", "password");