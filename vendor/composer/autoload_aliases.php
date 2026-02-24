<?php

// Functions and constants

namespace {

}
namespace Sabre\Uri {
    if(!function_exists('\\Sabre\\Uri\\resolve')){
        function resolve(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\resolve(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\normalize')){
        function normalize(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\normalize(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\parse')){
        function parse(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\parse(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\build')){
        function build(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\build(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\split')){
        function split(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\split(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\_parse_fallback')){
        function _parse_fallback(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\_parse_fallback(...func_get_args());
        }
    }
}
namespace Sabre\Xml\Deserializer {
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\keyValue')){
        function keyValue(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\keyValue(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\enum')){
        function enum(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\enum(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\valueObject')){
        function valueObject(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\valueObject(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\repeatingElements')){
        function repeatingElements(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\repeatingElements(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\mixedContent')){
        function mixedContent(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\mixedContent(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\functionCaller')){
        function functionCaller(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\functionCaller(...func_get_args());
        }
    }
}
namespace Sabre\Xml\Serializer {
    if(!function_exists('\\Sabre\\Xml\\Serializer\\enum')){
        function enum(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\enum(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\valueObject')){
        function valueObject(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\valueObject(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\repeatingElements')){
        function repeatingElements(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\repeatingElements(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\standardSerializer')){
        function standardSerializer(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\standardSerializer(...func_get_args());
        }
    }
}
namespace Safe {
    if(!function_exists('\\Safe\\apache_get_version')){
        function apache_get_version(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_get_version(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apache_getenv')){
        function apache_getenv(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_getenv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apache_lookup_uri')){
        function apache_lookup_uri(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_lookup_uri(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apache_request_headers')){
        function apache_request_headers(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_request_headers(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apache_response_headers')){
        function apache_response_headers(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_response_headers(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apache_setenv')){
        function apache_setenv(...$args) {
            return \WPO\IPS\Vendor\Safe\apache_setenv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getallheaders')){
        function getallheaders(...$args) {
            return \WPO\IPS\Vendor\Safe\getallheaders(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\virtual')){
        function virtual(...$args) {
            return \WPO\IPS\Vendor\Safe\virtual(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_cache_info')){
        function apcu_cache_info(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_cache_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_cas')){
        function apcu_cas(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_cas(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_dec')){
        function apcu_dec(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_dec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_inc')){
        function apcu_inc(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_inc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_sma_info')){
        function apcu_sma_info(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_sma_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_combine')){
        function array_combine(...$args) {
            return \WPO\IPS\Vendor\Safe\array_combine(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_flip')){
        function array_flip(...$args) {
            return \WPO\IPS\Vendor\Safe\array_flip(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_replace_recursive')){
        function array_replace_recursive(...$args) {
            return \WPO\IPS\Vendor\Safe\array_replace_recursive(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_replace')){
        function array_replace(...$args) {
            return \WPO\IPS\Vendor\Safe\array_replace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_walk_recursive')){
        function array_walk_recursive(...$args) {
            return \WPO\IPS\Vendor\Safe\array_walk_recursive(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shuffle')){
        function shuffle(...$args) {
            return \WPO\IPS\Vendor\Safe\shuffle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bzclose')){
        function bzclose(...$args) {
            return \WPO\IPS\Vendor\Safe\bzclose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bzflush')){
        function bzflush(...$args) {
            return \WPO\IPS\Vendor\Safe\bzflush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bzopen')){
        function bzopen(...$args) {
            return \WPO\IPS\Vendor\Safe\bzopen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bzread')){
        function bzread(...$args) {
            return \WPO\IPS\Vendor\Safe\bzread(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bzwrite')){
        function bzwrite(...$args) {
            return \WPO\IPS\Vendor\Safe\bzwrite(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\unixtojd')){
        function unixtojd(...$args) {
            return \WPO\IPS\Vendor\Safe\unixtojd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\class_alias')){
        function class_alias(...$args) {
            return \WPO\IPS\Vendor\Safe\class_alias(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\com_create_guid')){
        function com_create_guid(...$args) {
            return \WPO\IPS\Vendor\Safe\com_create_guid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\com_event_sink')){
        function com_event_sink(...$args) {
            return \WPO\IPS\Vendor\Safe\com_event_sink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\com_load_typelib')){
        function com_load_typelib(...$args) {
            return \WPO\IPS\Vendor\Safe\com_load_typelib(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\com_print_typeinfo')){
        function com_print_typeinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\com_print_typeinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\variant_date_to_timestamp')){
        function variant_date_to_timestamp(...$args) {
            return \WPO\IPS\Vendor\Safe\variant_date_to_timestamp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\variant_round')){
        function variant_round(...$args) {
            return \WPO\IPS\Vendor\Safe\variant_round(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_bind')){
        function cubrid_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_col_size')){
        function cubrid_col_size(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_col_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_column_names')){
        function cubrid_column_names(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_column_names(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_column_types')){
        function cubrid_column_types(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_column_types(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_commit')){
        function cubrid_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_connect_with_url')){
        function cubrid_connect_with_url(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_connect_with_url(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_connect')){
        function cubrid_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_current_oid')){
        function cubrid_current_oid(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_current_oid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_disconnect')){
        function cubrid_disconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_disconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_drop')){
        function cubrid_drop(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_drop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_free_result')){
        function cubrid_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_charset')){
        function cubrid_get_charset(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_charset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_class_name')){
        function cubrid_get_class_name(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_class_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_client_info')){
        function cubrid_get_client_info(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_client_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_db_parameter')){
        function cubrid_get_db_parameter(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_db_parameter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_query_timeout')){
        function cubrid_get_query_timeout(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_query_timeout(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_get_server_info')){
        function cubrid_get_server_info(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_get_server_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_insert_id')){
        function cubrid_insert_id(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_insert_id(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob_close')){
        function cubrid_lob_close(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob_export')){
        function cubrid_lob_export(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob_get')){
        function cubrid_lob_get(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob_send')){
        function cubrid_lob_send(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob_send(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob_size')){
        function cubrid_lob_size(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_bind')){
        function cubrid_lob2_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_close')){
        function cubrid_lob2_close(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_export')){
        function cubrid_lob2_export(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_import')){
        function cubrid_lob2_import(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_import(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_new')){
        function cubrid_lob2_new(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_read')){
        function cubrid_lob2_read(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_seek')){
        function cubrid_lob2_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_seek64')){
        function cubrid_lob2_seek64(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_seek64(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_size')){
        function cubrid_lob2_size(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_size64')){
        function cubrid_lob2_size64(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_size64(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_tell')){
        function cubrid_lob2_tell(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_tell(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_tell64')){
        function cubrid_lob2_tell64(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_tell64(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lob2_write')){
        function cubrid_lob2_write(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lob2_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lock_read')){
        function cubrid_lock_read(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lock_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_lock_write')){
        function cubrid_lock_write(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_lock_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_move_cursor')){
        function cubrid_move_cursor(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_move_cursor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_next_result')){
        function cubrid_next_result(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_next_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_pconnect_with_url')){
        function cubrid_pconnect_with_url(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_pconnect_with_url(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_pconnect')){
        function cubrid_pconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_pconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_prepare')){
        function cubrid_prepare(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_prepare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_put')){
        function cubrid_put(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_put(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_rollback')){
        function cubrid_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_schema')){
        function cubrid_schema(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_schema(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_seq_drop')){
        function cubrid_seq_drop(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_seq_drop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_seq_insert')){
        function cubrid_seq_insert(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_seq_insert(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_seq_put')){
        function cubrid_seq_put(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_seq_put(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_set_add')){
        function cubrid_set_add(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_set_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_set_autocommit')){
        function cubrid_set_autocommit(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_set_autocommit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_set_db_parameter')){
        function cubrid_set_db_parameter(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_set_db_parameter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_set_drop')){
        function cubrid_set_drop(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_set_drop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cubrid_set_query_timeout')){
        function cubrid_set_query_timeout(...$args) {
            return \WPO\IPS\Vendor\Safe\cubrid_set_query_timeout(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_copy_handle')){
        function curl_copy_handle(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_copy_handle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_escape')){
        function curl_escape(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_escape(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_exec')){
        function curl_exec(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_exec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_getinfo')){
        function curl_getinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_getinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_init')){
        function curl_init(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_multi_info_read')){
        function curl_multi_info_read(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_multi_info_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_multi_init')){
        function curl_multi_init(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_multi_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_multi_setopt')){
        function curl_multi_setopt(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_multi_setopt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_setopt')){
        function curl_setopt(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_setopt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_share_errno')){
        function curl_share_errno(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_share_errno(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_share_setopt')){
        function curl_share_setopt(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_share_setopt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_unescape')){
        function curl_unescape(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_unescape(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_parse_from_format')){
        function date_parse_from_format(...$args) {
            return \WPO\IPS\Vendor\Safe\date_parse_from_format(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_parse')){
        function date_parse(...$args) {
            return \WPO\IPS\Vendor\Safe\date_parse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_sun_info')){
        function date_sun_info(...$args) {
            return \WPO\IPS\Vendor\Safe\date_sun_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_sunrise')){
        function date_sunrise(...$args) {
            return \WPO\IPS\Vendor\Safe\date_sunrise(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_sunset')){
        function date_sunset(...$args) {
            return \WPO\IPS\Vendor\Safe\date_sunset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date')){
        function date(...$args) {
            return \WPO\IPS\Vendor\Safe\date(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gmmktime')){
        function gmmktime(...$args) {
            return \WPO\IPS\Vendor\Safe\gmmktime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gmstrftime')){
        function gmstrftime(...$args) {
            return \WPO\IPS\Vendor\Safe\gmstrftime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\idate')){
        function idate(...$args) {
            return \WPO\IPS\Vendor\Safe\idate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mktime')){
        function mktime(...$args) {
            return \WPO\IPS\Vendor\Safe\mktime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\strftime')){
        function strftime(...$args) {
            return \WPO\IPS\Vendor\Safe\strftime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\strptime')){
        function strptime(...$args) {
            return \WPO\IPS\Vendor\Safe\strptime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\strtotime')){
        function strtotime(...$args) {
            return \WPO\IPS\Vendor\Safe\strtotime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\timezone_name_from_abbr')){
        function timezone_name_from_abbr(...$args) {
            return \WPO\IPS\Vendor\Safe\timezone_name_from_abbr(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\chdir')){
        function chdir(...$args) {
            return \WPO\IPS\Vendor\Safe\chdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\chroot')){
        function chroot(...$args) {
            return \WPO\IPS\Vendor\Safe\chroot(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\dir')){
        function dir(...$args) {
            return \WPO\IPS\Vendor\Safe\dir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getcwd')){
        function getcwd(...$args) {
            return \WPO\IPS\Vendor\Safe\getcwd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\opendir')){
        function opendir(...$args) {
            return \WPO\IPS\Vendor\Safe\opendir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\scandir')){
        function scandir(...$args) {
            return \WPO\IPS\Vendor\Safe\scandir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_busy')){
        function eio_busy(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_busy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_chmod')){
        function eio_chmod(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_chmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_chown')){
        function eio_chown(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_chown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_close')){
        function eio_close(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_custom')){
        function eio_custom(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_custom(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_dup2')){
        function eio_dup2(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_dup2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_event_loop')){
        function eio_event_loop(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_event_loop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fallocate')){
        function eio_fallocate(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fallocate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fchmod')){
        function eio_fchmod(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fchmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fchown')){
        function eio_fchown(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fchown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fdatasync')){
        function eio_fdatasync(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fdatasync(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fstat')){
        function eio_fstat(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fstat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fstatvfs')){
        function eio_fstatvfs(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fstatvfs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_fsync')){
        function eio_fsync(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_fsync(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_ftruncate')){
        function eio_ftruncate(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_ftruncate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_futime')){
        function eio_futime(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_futime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_grp')){
        function eio_grp(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_grp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_lstat')){
        function eio_lstat(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_lstat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_mkdir')){
        function eio_mkdir(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_mkdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_mknod')){
        function eio_mknod(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_mknod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_nop')){
        function eio_nop(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_nop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_readahead')){
        function eio_readahead(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_readahead(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_readdir')){
        function eio_readdir(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_readdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_readlink')){
        function eio_readlink(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_readlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_rename')){
        function eio_rename(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_rename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_rmdir')){
        function eio_rmdir(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_rmdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_seek')){
        function eio_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_sendfile')){
        function eio_sendfile(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_sendfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_stat')){
        function eio_stat(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_stat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_statvfs')){
        function eio_statvfs(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_statvfs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_symlink')){
        function eio_symlink(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_symlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_sync_file_range')){
        function eio_sync_file_range(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_sync_file_range(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_sync')){
        function eio_sync(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_sync(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_syncfs')){
        function eio_syncfs(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_syncfs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_truncate')){
        function eio_truncate(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_truncate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_unlink')){
        function eio_unlink(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_unlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_utime')){
        function eio_utime(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_utime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\eio_write')){
        function eio_write(...$args) {
            return \WPO\IPS\Vendor\Safe\eio_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\error_log')){
        function error_log(...$args) {
            return \WPO\IPS\Vendor\Safe\error_log(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\exec')){
        function exec(...$args) {
            return \WPO\IPS\Vendor\Safe\exec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\proc_close')){
        function proc_close(...$args) {
            return \WPO\IPS\Vendor\Safe\proc_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\proc_nice')){
        function proc_nice(...$args) {
            return \WPO\IPS\Vendor\Safe\proc_nice(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\proc_open')){
        function proc_open(...$args) {
            return \WPO\IPS\Vendor\Safe\proc_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shell_exec')){
        function shell_exec(...$args) {
            return \WPO\IPS\Vendor\Safe\shell_exec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\system')){
        function system(...$args) {
            return \WPO\IPS\Vendor\Safe\system(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\finfo_close')){
        function finfo_close(...$args) {
            return \WPO\IPS\Vendor\Safe\finfo_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\finfo_open')){
        function finfo_open(...$args) {
            return \WPO\IPS\Vendor\Safe\finfo_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mime_content_type')){
        function mime_content_type(...$args) {
            return \WPO\IPS\Vendor\Safe\mime_content_type(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\chgrp')){
        function chgrp(...$args) {
            return \WPO\IPS\Vendor\Safe\chgrp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\chmod')){
        function chmod(...$args) {
            return \WPO\IPS\Vendor\Safe\chmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\chown')){
        function chown(...$args) {
            return \WPO\IPS\Vendor\Safe\chown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\copy')){
        function copy(...$args) {
            return \WPO\IPS\Vendor\Safe\copy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\disk_free_space')){
        function disk_free_space(...$args) {
            return \WPO\IPS\Vendor\Safe\disk_free_space(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\disk_total_space')){
        function disk_total_space(...$args) {
            return \WPO\IPS\Vendor\Safe\disk_total_space(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fclose')){
        function fclose(...$args) {
            return \WPO\IPS\Vendor\Safe\fclose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fdatasync')){
        function fdatasync(...$args) {
            return \WPO\IPS\Vendor\Safe\fdatasync(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fflush')){
        function fflush(...$args) {
            return \WPO\IPS\Vendor\Safe\fflush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\file_get_contents')){
        function file_get_contents(...$args) {
            return \WPO\IPS\Vendor\Safe\file_get_contents(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\file_put_contents')){
        function file_put_contents(...$args) {
            return \WPO\IPS\Vendor\Safe\file_put_contents(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\file')){
        function file(...$args) {
            return \WPO\IPS\Vendor\Safe\file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fileatime')){
        function fileatime(...$args) {
            return \WPO\IPS\Vendor\Safe\fileatime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filectime')){
        function filectime(...$args) {
            return \WPO\IPS\Vendor\Safe\filectime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fileinode')){
        function fileinode(...$args) {
            return \WPO\IPS\Vendor\Safe\fileinode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filemtime')){
        function filemtime(...$args) {
            return \WPO\IPS\Vendor\Safe\filemtime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fileowner')){
        function fileowner(...$args) {
            return \WPO\IPS\Vendor\Safe\fileowner(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fileperms')){
        function fileperms(...$args) {
            return \WPO\IPS\Vendor\Safe\fileperms(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filesize')){
        function filesize(...$args) {
            return \WPO\IPS\Vendor\Safe\filesize(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filetype')){
        function filetype(...$args) {
            return \WPO\IPS\Vendor\Safe\filetype(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\flock')){
        function flock(...$args) {
            return \WPO\IPS\Vendor\Safe\flock(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fopen')){
        function fopen(...$args) {
            return \WPO\IPS\Vendor\Safe\fopen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fread')){
        function fread(...$args) {
            return \WPO\IPS\Vendor\Safe\fread(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fstat')){
        function fstat(...$args) {
            return \WPO\IPS\Vendor\Safe\fstat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fsync')){
        function fsync(...$args) {
            return \WPO\IPS\Vendor\Safe\fsync(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftell')){
        function ftell(...$args) {
            return \WPO\IPS\Vendor\Safe\ftell(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftruncate')){
        function ftruncate(...$args) {
            return \WPO\IPS\Vendor\Safe\ftruncate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fwrite')){
        function fwrite(...$args) {
            return \WPO\IPS\Vendor\Safe\fwrite(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\glob')){
        function glob(...$args) {
            return \WPO\IPS\Vendor\Safe\glob(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\lchgrp')){
        function lchgrp(...$args) {
            return \WPO\IPS\Vendor\Safe\lchgrp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\lchown')){
        function lchown(...$args) {
            return \WPO\IPS\Vendor\Safe\lchown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\link')){
        function link(...$args) {
            return \WPO\IPS\Vendor\Safe\link(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\lstat')){
        function lstat(...$args) {
            return \WPO\IPS\Vendor\Safe\lstat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mkdir')){
        function mkdir(...$args) {
            return \WPO\IPS\Vendor\Safe\mkdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\parse_ini_file')){
        function parse_ini_file(...$args) {
            return \WPO\IPS\Vendor\Safe\parse_ini_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\parse_ini_string')){
        function parse_ini_string(...$args) {
            return \WPO\IPS\Vendor\Safe\parse_ini_string(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pclose')){
        function pclose(...$args) {
            return \WPO\IPS\Vendor\Safe\pclose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\popen')){
        function popen(...$args) {
            return \WPO\IPS\Vendor\Safe\popen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readfile')){
        function readfile(...$args) {
            return \WPO\IPS\Vendor\Safe\readfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readlink')){
        function readlink(...$args) {
            return \WPO\IPS\Vendor\Safe\readlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\realpath')){
        function realpath(...$args) {
            return \WPO\IPS\Vendor\Safe\realpath(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rename')){
        function rename(...$args) {
            return \WPO\IPS\Vendor\Safe\rename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rewind')){
        function rewind(...$args) {
            return \WPO\IPS\Vendor\Safe\rewind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rmdir')){
        function rmdir(...$args) {
            return \WPO\IPS\Vendor\Safe\rmdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\symlink')){
        function symlink(...$args) {
            return \WPO\IPS\Vendor\Safe\symlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\tempnam')){
        function tempnam(...$args) {
            return \WPO\IPS\Vendor\Safe\tempnam(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\tmpfile')){
        function tmpfile(...$args) {
            return \WPO\IPS\Vendor\Safe\tmpfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\touch')){
        function touch(...$args) {
            return \WPO\IPS\Vendor\Safe\touch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\unlink')){
        function unlink(...$args) {
            return \WPO\IPS\Vendor\Safe\unlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filter_input_array')){
        function filter_input_array(...$args) {
            return \WPO\IPS\Vendor\Safe\filter_input_array(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\filter_var_array')){
        function filter_var_array(...$args) {
            return \WPO\IPS\Vendor\Safe\filter_var_array(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fastcgi_finish_request')){
        function fastcgi_finish_request(...$args) {
            return \WPO\IPS\Vendor\Safe\fastcgi_finish_request(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_alloc')){
        function ftp_alloc(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_alloc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_append')){
        function ftp_append(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_append(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_cdup')){
        function ftp_cdup(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_cdup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_chdir')){
        function ftp_chdir(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_chdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_chmod')){
        function ftp_chmod(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_chmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_close')){
        function ftp_close(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_connect')){
        function ftp_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_delete')){
        function ftp_delete(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_delete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_fget')){
        function ftp_fget(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_fget(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_fput')){
        function ftp_fput(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_fput(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_get')){
        function ftp_get(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_login')){
        function ftp_login(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_login(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_mkdir')){
        function ftp_mkdir(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_mkdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_mlsd')){
        function ftp_mlsd(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_mlsd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_nb_put')){
        function ftp_nb_put(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_nb_put(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_nlist')){
        function ftp_nlist(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_nlist(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_pasv')){
        function ftp_pasv(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_pasv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_put')){
        function ftp_put(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_put(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_pwd')){
        function ftp_pwd(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_pwd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_rename')){
        function ftp_rename(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_rename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_rmdir')){
        function ftp_rmdir(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_rmdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_site')){
        function ftp_site(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_site(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_size')){
        function ftp_size(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_ssl_connect')){
        function ftp_ssl_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_ssl_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_systype')){
        function ftp_systype(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_systype(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\register_tick_function')){
        function register_tick_function(...$args) {
            return \WPO\IPS\Vendor\Safe\register_tick_function(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\bindtextdomain')){
        function bindtextdomain(...$args) {
            return \WPO\IPS\Vendor\Safe\bindtextdomain(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gmp_random_seed')){
        function gmp_random_seed(...$args) {
            return \WPO\IPS\Vendor\Safe\gmp_random_seed(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_adddecryptkey')){
        function gnupg_adddecryptkey(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_adddecryptkey(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_addencryptkey')){
        function gnupg_addencryptkey(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_addencryptkey(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_addsignkey')){
        function gnupg_addsignkey(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_addsignkey(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_cleardecryptkeys')){
        function gnupg_cleardecryptkeys(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_cleardecryptkeys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_clearencryptkeys')){
        function gnupg_clearencryptkeys(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_clearencryptkeys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_clearsignkeys')){
        function gnupg_clearsignkeys(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_clearsignkeys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_setarmor')){
        function gnupg_setarmor(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_setarmor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_setsignmode')){
        function gnupg_setsignmode(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_setsignmode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\hash_hkdf')){
        function hash_hkdf(...$args) {
            return \WPO\IPS\Vendor\Safe\hash_hkdf(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\hash_update_file')){
        function hash_update_file(...$args) {
            return \WPO\IPS\Vendor\Safe\hash_update_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fbird_blob_cancel')){
        function fbird_blob_cancel(...$args) {
            return \WPO\IPS\Vendor\Safe\fbird_blob_cancel(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_add_user')){
        function ibase_add_user(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_add_user(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_backup')){
        function ibase_backup(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_backup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_blob_cancel')){
        function ibase_blob_cancel(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_blob_cancel(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_blob_create')){
        function ibase_blob_create(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_blob_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_blob_get')){
        function ibase_blob_get(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_blob_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_close')){
        function ibase_close(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_commit_ret')){
        function ibase_commit_ret(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_commit_ret(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_commit')){
        function ibase_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_connect')){
        function ibase_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_delete_user')){
        function ibase_delete_user(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_delete_user(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_drop_db')){
        function ibase_drop_db(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_drop_db(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_free_event_handler')){
        function ibase_free_event_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_free_event_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_free_query')){
        function ibase_free_query(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_free_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_free_result')){
        function ibase_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_maintain_db')){
        function ibase_maintain_db(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_maintain_db(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_modify_user')){
        function ibase_modify_user(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_modify_user(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_name_result')){
        function ibase_name_result(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_name_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_pconnect')){
        function ibase_pconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_pconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_restore')){
        function ibase_restore(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_restore(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_rollback_ret')){
        function ibase_rollback_ret(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_rollback_ret(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_rollback')){
        function ibase_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_service_attach')){
        function ibase_service_attach(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_service_attach(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ibase_service_detach')){
        function ibase_service_detach(...$args) {
            return \WPO\IPS\Vendor\Safe\ibase_service_detach(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_autocommit')){
        function db2_autocommit(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_autocommit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_bind_param')){
        function db2_bind_param(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_bind_param(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_client_info')){
        function db2_client_info(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_client_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_close')){
        function db2_close(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_commit')){
        function db2_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_execute')){
        function db2_execute(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_execute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_free_result')){
        function db2_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_free_stmt')){
        function db2_free_stmt(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_free_stmt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_get_option')){
        function db2_get_option(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_get_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_pclose')){
        function db2_pclose(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_pclose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_rollback')){
        function db2_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_server_info')){
        function db2_server_info(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_server_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_set_option')){
        function db2_set_option(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_set_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv_get_encoding')){
        function iconv_get_encoding(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv_get_encoding(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv_mime_decode')){
        function iconv_mime_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv_mime_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv_mime_encode')){
        function iconv_mime_encode(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv_mime_encode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv_set_encoding')){
        function iconv_set_encoding(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv_set_encoding(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv_strlen')){
        function iconv_strlen(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv_strlen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iconv')){
        function iconv(...$args) {
            return \WPO\IPS\Vendor\Safe\iconv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getimagesize')){
        function getimagesize(...$args) {
            return \WPO\IPS\Vendor\Safe\getimagesize(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\image_type_to_extension')){
        function image_type_to_extension(...$args) {
            return \WPO\IPS\Vendor\Safe\image_type_to_extension(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageaffine')){
        function imageaffine(...$args) {
            return \WPO\IPS\Vendor\Safe\imageaffine(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageaffinematrixconcat')){
        function imageaffinematrixconcat(...$args) {
            return \WPO\IPS\Vendor\Safe\imageaffinematrixconcat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageaffinematrixget')){
        function imageaffinematrixget(...$args) {
            return \WPO\IPS\Vendor\Safe\imageaffinematrixget(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagealphablending')){
        function imagealphablending(...$args) {
            return \WPO\IPS\Vendor\Safe\imagealphablending(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageantialias')){
        function imageantialias(...$args) {
            return \WPO\IPS\Vendor\Safe\imageantialias(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagearc')){
        function imagearc(...$args) {
            return \WPO\IPS\Vendor\Safe\imagearc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageavif')){
        function imageavif(...$args) {
            return \WPO\IPS\Vendor\Safe\imageavif(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagebmp')){
        function imagebmp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagebmp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagechar')){
        function imagechar(...$args) {
            return \WPO\IPS\Vendor\Safe\imagechar(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecharup')){
        function imagecharup(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecharup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecolorat')){
        function imagecolorat(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecolorat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecolordeallocate')){
        function imagecolordeallocate(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecolordeallocate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecolormatch')){
        function imagecolormatch(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecolormatch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecolorset')){
        function imagecolorset(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecolorset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecolorsforindex')){
        function imagecolorsforindex(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecolorsforindex(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageconvolution')){
        function imageconvolution(...$args) {
            return \WPO\IPS\Vendor\Safe\imageconvolution(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecopy')){
        function imagecopy(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecopy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecopymerge')){
        function imagecopymerge(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecopymerge(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecopymergegray')){
        function imagecopymergegray(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecopymergegray(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecopyresampled')){
        function imagecopyresampled(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecopyresampled(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecopyresized')){
        function imagecopyresized(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecopyresized(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreate')){
        function imagecreate(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromavif')){
        function imagecreatefromavif(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromavif(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefrombmp')){
        function imagecreatefrombmp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefrombmp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromgd')){
        function imagecreatefromgd(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromgd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromgd2')){
        function imagecreatefromgd2(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromgd2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromgd2part')){
        function imagecreatefromgd2part(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromgd2part(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromgif')){
        function imagecreatefromgif(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromgif(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromjpeg')){
        function imagecreatefromjpeg(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromjpeg(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefrompng')){
        function imagecreatefrompng(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefrompng(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromstring')){
        function imagecreatefromstring(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromstring(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromtga')){
        function imagecreatefromtga(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromtga(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromwbmp')){
        function imagecreatefromwbmp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromwbmp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromwebp')){
        function imagecreatefromwebp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromwebp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromxbm')){
        function imagecreatefromxbm(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromxbm(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatefromxpm')){
        function imagecreatefromxpm(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatefromxpm(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecreatetruecolor')){
        function imagecreatetruecolor(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecreatetruecolor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecrop')){
        function imagecrop(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecrop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagecropauto')){
        function imagecropauto(...$args) {
            return \WPO\IPS\Vendor\Safe\imagecropauto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagedashedline')){
        function imagedashedline(...$args) {
            return \WPO\IPS\Vendor\Safe\imagedashedline(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagedestroy')){
        function imagedestroy(...$args) {
            return \WPO\IPS\Vendor\Safe\imagedestroy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageellipse')){
        function imageellipse(...$args) {
            return \WPO\IPS\Vendor\Safe\imageellipse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefill')){
        function imagefill(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefill(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefilledarc')){
        function imagefilledarc(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefilledarc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefilledellipse')){
        function imagefilledellipse(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefilledellipse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefilledrectangle')){
        function imagefilledrectangle(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefilledrectangle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefilltoborder')){
        function imagefilltoborder(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefilltoborder(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefilter')){
        function imagefilter(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefilter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageflip')){
        function imageflip(...$args) {
            return \WPO\IPS\Vendor\Safe\imageflip(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageftbbox')){
        function imageftbbox(...$args) {
            return \WPO\IPS\Vendor\Safe\imageftbbox(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagefttext')){
        function imagefttext(...$args) {
            return \WPO\IPS\Vendor\Safe\imagefttext(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegammacorrect')){
        function imagegammacorrect(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegammacorrect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegd')){
        function imagegd(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegd2')){
        function imagegd2(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegd2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegif')){
        function imagegif(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegif(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegrabscreen')){
        function imagegrabscreen(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegrabscreen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagegrabwindow')){
        function imagegrabwindow(...$args) {
            return \WPO\IPS\Vendor\Safe\imagegrabwindow(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagejpeg')){
        function imagejpeg(...$args) {
            return \WPO\IPS\Vendor\Safe\imagejpeg(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagelayereffect')){
        function imagelayereffect(...$args) {
            return \WPO\IPS\Vendor\Safe\imagelayereffect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageline')){
        function imageline(...$args) {
            return \WPO\IPS\Vendor\Safe\imageline(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageloadfont')){
        function imageloadfont(...$args) {
            return \WPO\IPS\Vendor\Safe\imageloadfont(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagepng')){
        function imagepng(...$args) {
            return \WPO\IPS\Vendor\Safe\imagepng(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagerectangle')){
        function imagerectangle(...$args) {
            return \WPO\IPS\Vendor\Safe\imagerectangle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imageresolution')){
        function imageresolution(...$args) {
            return \WPO\IPS\Vendor\Safe\imageresolution(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagerotate')){
        function imagerotate(...$args) {
            return \WPO\IPS\Vendor\Safe\imagerotate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesavealpha')){
        function imagesavealpha(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesavealpha(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagescale')){
        function imagescale(...$args) {
            return \WPO\IPS\Vendor\Safe\imagescale(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetbrush')){
        function imagesetbrush(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetbrush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetclip')){
        function imagesetclip(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetclip(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetinterpolation')){
        function imagesetinterpolation(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetinterpolation(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetpixel')){
        function imagesetpixel(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetpixel(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetstyle')){
        function imagesetstyle(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetstyle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesetthickness')){
        function imagesetthickness(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesetthickness(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesettile')){
        function imagesettile(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesettile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagestring')){
        function imagestring(...$args) {
            return \WPO\IPS\Vendor\Safe\imagestring(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagestringup')){
        function imagestringup(...$args) {
            return \WPO\IPS\Vendor\Safe\imagestringup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesx')){
        function imagesx(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesx(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagesy')){
        function imagesy(...$args) {
            return \WPO\IPS\Vendor\Safe\imagesy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagetruecolortopalette')){
        function imagetruecolortopalette(...$args) {
            return \WPO\IPS\Vendor\Safe\imagetruecolortopalette(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagettfbbox')){
        function imagettfbbox(...$args) {
            return \WPO\IPS\Vendor\Safe\imagettfbbox(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagettftext')){
        function imagettftext(...$args) {
            return \WPO\IPS\Vendor\Safe\imagettftext(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagewbmp')){
        function imagewbmp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagewbmp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagewebp')){
        function imagewebp(...$args) {
            return \WPO\IPS\Vendor\Safe\imagewebp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imagexbm')){
        function imagexbm(...$args) {
            return \WPO\IPS\Vendor\Safe\imagexbm(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iptcembed')){
        function iptcembed(...$args) {
            return \WPO\IPS\Vendor\Safe\iptcembed(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\iptcparse')){
        function iptcparse(...$args) {
            return \WPO\IPS\Vendor\Safe\iptcparse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_8bit')){
        function imap_8bit(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_8bit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_append')){
        function imap_append(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_append(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_base64')){
        function imap_base64(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_base64(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_binary')){
        function imap_binary(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_binary(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_body')){
        function imap_body(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_body(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_bodystruct')){
        function imap_bodystruct(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_bodystruct(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_check')){
        function imap_check(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_check(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_clearflag_full')){
        function imap_clearflag_full(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_clearflag_full(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_close')){
        function imap_close(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_createmailbox')){
        function imap_createmailbox(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_createmailbox(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_deletemailbox')){
        function imap_deletemailbox(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_deletemailbox(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_fetch_overview')){
        function imap_fetch_overview(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_fetch_overview(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_fetchbody')){
        function imap_fetchbody(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_fetchbody(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_fetchheader')){
        function imap_fetchheader(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_fetchheader(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_fetchmime')){
        function imap_fetchmime(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_fetchmime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_fetchstructure')){
        function imap_fetchstructure(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_fetchstructure(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_gc')){
        function imap_gc(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_gc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_getacl')){
        function imap_getacl(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_getacl(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_getmailboxes')){
        function imap_getmailboxes(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_getmailboxes(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_getsubscribed')){
        function imap_getsubscribed(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_getsubscribed(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_headerinfo')){
        function imap_headerinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_headerinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_headers')){
        function imap_headers(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_headers(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_listscan')){
        function imap_listscan(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_listscan(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_lsub')){
        function imap_lsub(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_lsub(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mail_compose')){
        function imap_mail_compose(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mail_compose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mail_copy')){
        function imap_mail_copy(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mail_copy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mail_move')){
        function imap_mail_move(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mail_move(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mail')){
        function imap_mail(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mail(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mailboxmsginfo')){
        function imap_mailboxmsginfo(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mailboxmsginfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mime_header_decode')){
        function imap_mime_header_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mime_header_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_mutf7_to_utf8')){
        function imap_mutf7_to_utf8(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_mutf7_to_utf8(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_num_msg')){
        function imap_num_msg(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_num_msg(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_open')){
        function imap_open(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_qprint')){
        function imap_qprint(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_qprint(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_renamemailbox')){
        function imap_renamemailbox(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_renamemailbox(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_rfc822_write_address')){
        function imap_rfc822_write_address(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_rfc822_write_address(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_savebody')){
        function imap_savebody(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_savebody(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_set_quota')){
        function imap_set_quota(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_set_quota(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_setacl')){
        function imap_setacl(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_setacl(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_setflag_full')){
        function imap_setflag_full(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_setflag_full(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_sort')){
        function imap_sort(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_sort(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_status')){
        function imap_status(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_status(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_subscribe')){
        function imap_subscribe(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_subscribe(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_thread')){
        function imap_thread(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_thread(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_timeout')){
        function imap_timeout(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_timeout(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_undelete')){
        function imap_undelete(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_undelete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_unsubscribe')){
        function imap_unsubscribe(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_unsubscribe(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\imap_utf8_to_mutf7')){
        function imap_utf8_to_mutf7(...$args) {
            return \WPO\IPS\Vendor\Safe\imap_utf8_to_mutf7(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\assert_options')){
        function assert_options(...$args) {
            return \WPO\IPS\Vendor\Safe\assert_options(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\cli_set_process_title')){
        function cli_set_process_title(...$args) {
            return \WPO\IPS\Vendor\Safe\cli_set_process_title(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\dl')){
        function dl(...$args) {
            return \WPO\IPS\Vendor\Safe\dl(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\get_cfg_var')){
        function get_cfg_var(...$args) {
            return \WPO\IPS\Vendor\Safe\get_cfg_var(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\get_include_path')){
        function get_include_path(...$args) {
            return \WPO\IPS\Vendor\Safe\get_include_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getlastmod')){
        function getlastmod(...$args) {
            return \WPO\IPS\Vendor\Safe\getlastmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getmygid')){
        function getmygid(...$args) {
            return \WPO\IPS\Vendor\Safe\getmygid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getmyinode')){
        function getmyinode(...$args) {
            return \WPO\IPS\Vendor\Safe\getmyinode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getmypid')){
        function getmypid(...$args) {
            return \WPO\IPS\Vendor\Safe\getmypid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getmyuid')){
        function getmyuid(...$args) {
            return \WPO\IPS\Vendor\Safe\getmyuid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getopt')){
        function getopt(...$args) {
            return \WPO\IPS\Vendor\Safe\getopt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getrusage')){
        function getrusage(...$args) {
            return \WPO\IPS\Vendor\Safe\getrusage(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ini_get')){
        function ini_get(...$args) {
            return \WPO\IPS\Vendor\Safe\ini_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ini_set')){
        function ini_set(...$args) {
            return \WPO\IPS\Vendor\Safe\ini_set(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\php_sapi_name')){
        function php_sapi_name(...$args) {
            return \WPO\IPS\Vendor\Safe\php_sapi_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\phpcredits')){
        function phpcredits(...$args) {
            return \WPO\IPS\Vendor\Safe\phpcredits(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\phpinfo')){
        function phpinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\phpinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\putenv')){
        function putenv(...$args) {
            return \WPO\IPS\Vendor\Safe\putenv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\set_include_path')){
        function set_include_path(...$args) {
            return \WPO\IPS\Vendor\Safe\set_include_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\set_time_limit')){
        function set_time_limit(...$args) {
            return \WPO\IPS\Vendor\Safe\set_time_limit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inotify_init')){
        function inotify_init(...$args) {
            return \WPO\IPS\Vendor\Safe\inotify_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inotify_rm_watch')){
        function inotify_rm_watch(...$args) {
            return \WPO\IPS\Vendor\Safe\inotify_rm_watch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\json_encode')){
        function json_encode(...$args) {
            return \WPO\IPS\Vendor\Safe\json_encode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_8859_to_t61')){
        function ldap_8859_to_t61(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_8859_to_t61(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_add')){
        function ldap_add(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_bind')){
        function ldap_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_compare')){
        function ldap_compare(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_compare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_control_paged_result_response')){
        function ldap_control_paged_result_response(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_control_paged_result_response(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_control_paged_result')){
        function ldap_control_paged_result(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_control_paged_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_count_entries')){
        function ldap_count_entries(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_count_entries(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_delete')){
        function ldap_delete(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_delete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_dn2ufn')){
        function ldap_dn2ufn(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_dn2ufn(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_exop_passwd')){
        function ldap_exop_passwd(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_exop_passwd(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_exop_whoami')){
        function ldap_exop_whoami(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_exop_whoami(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_exop')){
        function ldap_exop(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_exop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_explode_dn')){
        function ldap_explode_dn(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_explode_dn(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_first_attribute')){
        function ldap_first_attribute(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_first_attribute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_first_entry')){
        function ldap_first_entry(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_first_entry(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_free_result')){
        function ldap_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_attributes')){
        function ldap_get_attributes(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_attributes(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_dn')){
        function ldap_get_dn(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_dn(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_entries')){
        function ldap_get_entries(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_entries(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_option')){
        function ldap_get_option(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_values_len')){
        function ldap_get_values_len(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_values_len(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_get_values')){
        function ldap_get_values(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_get_values(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_mod_add')){
        function ldap_mod_add(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_mod_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_mod_del')){
        function ldap_mod_del(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_mod_del(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_mod_replace')){
        function ldap_mod_replace(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_mod_replace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_modify_batch')){
        function ldap_modify_batch(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_modify_batch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_next_attribute')){
        function ldap_next_attribute(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_next_attribute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_parse_exop')){
        function ldap_parse_exop(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_parse_exop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_parse_result')){
        function ldap_parse_result(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_parse_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_rename')){
        function ldap_rename(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_rename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_sasl_bind')){
        function ldap_sasl_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_sasl_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_set_option')){
        function ldap_set_option(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_set_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ldap_unbind')){
        function ldap_unbind(...$args) {
            return \WPO\IPS\Vendor\Safe\ldap_unbind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\libxml_set_external_entity_loader')){
        function libxml_set_external_entity_loader(...$args) {
            return \WPO\IPS\Vendor\Safe\libxml_set_external_entity_loader(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\lzf_compress')){
        function lzf_compress(...$args) {
            return \WPO\IPS\Vendor\Safe\lzf_compress(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\lzf_decompress')){
        function lzf_decompress(...$args) {
            return \WPO\IPS\Vendor\Safe\lzf_decompress(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mailparse_msg_extract_part_file')){
        function mailparse_msg_extract_part_file(...$args) {
            return \WPO\IPS\Vendor\Safe\mailparse_msg_extract_part_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mailparse_msg_free')){
        function mailparse_msg_free(...$args) {
            return \WPO\IPS\Vendor\Safe\mailparse_msg_free(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mailparse_msg_parse_file')){
        function mailparse_msg_parse_file(...$args) {
            return \WPO\IPS\Vendor\Safe\mailparse_msg_parse_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mailparse_msg_parse')){
        function mailparse_msg_parse(...$args) {
            return \WPO\IPS\Vendor\Safe\mailparse_msg_parse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mailparse_stream_encode')){
        function mailparse_stream_encode(...$args) {
            return \WPO\IPS\Vendor\Safe\mailparse_stream_encode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_chr')){
        function mb_chr(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_chr(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_convert_encoding')){
        function mb_convert_encoding(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_convert_encoding(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_convert_variables')){
        function mb_convert_variables(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_convert_variables(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_detect_order')){
        function mb_detect_order(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_detect_order(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_encoding_aliases')){
        function mb_encoding_aliases(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_encoding_aliases(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_replace_callback')){
        function mb_ereg_replace_callback(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_replace_callback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_replace')){
        function mb_ereg_replace(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_replace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_search_getregs')){
        function mb_ereg_search_getregs(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_search_getregs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_search_init')){
        function mb_ereg_search_init(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_search_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_search_regs')){
        function mb_ereg_search_regs(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_search_regs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ereg_search_setpos')){
        function mb_ereg_search_setpos(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ereg_search_setpos(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_eregi_replace')){
        function mb_eregi_replace(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_eregi_replace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_get_info')){
        function mb_get_info(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_get_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_http_output')){
        function mb_http_output(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_http_output(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_internal_encoding')){
        function mb_internal_encoding(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_internal_encoding(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_ord')){
        function mb_ord(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_ord(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_parse_str')){
        function mb_parse_str(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_parse_str(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_regex_encoding')){
        function mb_regex_encoding(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_regex_encoding(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_send_mail')){
        function mb_send_mail(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_send_mail(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mb_split')){
        function mb_split(...$args) {
            return \WPO\IPS\Vendor\Safe\mb_split(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\define')){
        function define(...$args) {
            return \WPO\IPS\Vendor\Safe\define(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\highlight_file')){
        function highlight_file(...$args) {
            return \WPO\IPS\Vendor\Safe\highlight_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\highlight_string')){
        function highlight_string(...$args) {
            return \WPO\IPS\Vendor\Safe\highlight_string(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\hrtime')){
        function hrtime(...$args) {
            return \WPO\IPS\Vendor\Safe\hrtime(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pack')){
        function pack(...$args) {
            return \WPO\IPS\Vendor\Safe\pack(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sapi_windows_cp_conv')){
        function sapi_windows_cp_conv(...$args) {
            return \WPO\IPS\Vendor\Safe\sapi_windows_cp_conv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sapi_windows_cp_set')){
        function sapi_windows_cp_set(...$args) {
            return \WPO\IPS\Vendor\Safe\sapi_windows_cp_set(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sapi_windows_generate_ctrl_event')){
        function sapi_windows_generate_ctrl_event(...$args) {
            return \WPO\IPS\Vendor\Safe\sapi_windows_generate_ctrl_event(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sapi_windows_set_ctrl_handler')){
        function sapi_windows_set_ctrl_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\sapi_windows_set_ctrl_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sapi_windows_vt100_support')){
        function sapi_windows_vt100_support(...$args) {
            return \WPO\IPS\Vendor\Safe\sapi_windows_vt100_support(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sleep')){
        function sleep(...$args) {
            return \WPO\IPS\Vendor\Safe\sleep(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\time_nanosleep')){
        function time_nanosleep(...$args) {
            return \WPO\IPS\Vendor\Safe\time_nanosleep(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\time_sleep_until')){
        function time_sleep_until(...$args) {
            return \WPO\IPS\Vendor\Safe\time_sleep_until(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\unpack')){
        function unpack(...$args) {
            return \WPO\IPS\Vendor\Safe\unpack(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_close')){
        function mysql_close(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_connect')){
        function mysql_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_create_db')){
        function mysql_create_db(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_create_db(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_data_seek')){
        function mysql_data_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_data_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_db_name')){
        function mysql_db_name(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_db_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_db_query')){
        function mysql_db_query(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_db_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_drop_db')){
        function mysql_drop_db(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_drop_db(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_fetch_lengths')){
        function mysql_fetch_lengths(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_fetch_lengths(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_field_flags')){
        function mysql_field_flags(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_field_flags(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_field_len')){
        function mysql_field_len(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_field_len(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_field_name')){
        function mysql_field_name(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_field_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_field_seek')){
        function mysql_field_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_field_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_free_result')){
        function mysql_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_get_host_info')){
        function mysql_get_host_info(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_get_host_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_get_proto_info')){
        function mysql_get_proto_info(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_get_proto_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_get_server_info')){
        function mysql_get_server_info(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_get_server_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_info')){
        function mysql_info(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_list_dbs')){
        function mysql_list_dbs(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_list_dbs(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_list_fields')){
        function mysql_list_fields(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_list_fields(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_list_processes')){
        function mysql_list_processes(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_list_processes(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_list_tables')){
        function mysql_list_tables(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_list_tables(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_num_fields')){
        function mysql_num_fields(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_num_fields(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_num_rows')){
        function mysql_num_rows(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_num_rows(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_query')){
        function mysql_query(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_real_escape_string')){
        function mysql_real_escape_string(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_real_escape_string(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_result')){
        function mysql_result(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_select_db')){
        function mysql_select_db(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_select_db(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_set_charset')){
        function mysql_set_charset(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_set_charset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_tablename')){
        function mysql_tablename(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_tablename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_thread_id')){
        function mysql_thread_id(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_thread_id(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysql_unbuffered_query')){
        function mysql_unbuffered_query(...$args) {
            return \WPO\IPS\Vendor\Safe\mysql_unbuffered_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\mysqli_get_client_stats')){
        function mysqli_get_client_stats(...$args) {
            return \WPO\IPS\Vendor\Safe\mysqli_get_client_stats(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\closelog')){
        function closelog(...$args) {
            return \WPO\IPS\Vendor\Safe\closelog(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\dns_get_record')){
        function dns_get_record(...$args) {
            return \WPO\IPS\Vendor\Safe\dns_get_record(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fsockopen')){
        function fsockopen(...$args) {
            return \WPO\IPS\Vendor\Safe\fsockopen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gethostname')){
        function gethostname(...$args) {
            return \WPO\IPS\Vendor\Safe\gethostname(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getprotobyname')){
        function getprotobyname(...$args) {
            return \WPO\IPS\Vendor\Safe\getprotobyname(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getprotobynumber')){
        function getprotobynumber(...$args) {
            return \WPO\IPS\Vendor\Safe\getprotobynumber(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\getservbyport')){
        function getservbyport(...$args) {
            return \WPO\IPS\Vendor\Safe\getservbyport(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\header_register_callback')){
        function header_register_callback(...$args) {
            return \WPO\IPS\Vendor\Safe\header_register_callback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inet_ntop')){
        function inet_ntop(...$args) {
            return \WPO\IPS\Vendor\Safe\inet_ntop(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inet_pton')){
        function inet_pton(...$args) {
            return \WPO\IPS\Vendor\Safe\inet_pton(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\long2ip')){
        function long2ip(...$args) {
            return \WPO\IPS\Vendor\Safe\long2ip(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openlog')){
        function openlog(...$args) {
            return \WPO\IPS\Vendor\Safe\openlog(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pfsockopen')){
        function pfsockopen(...$args) {
            return \WPO\IPS\Vendor\Safe\pfsockopen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\syslog')){
        function syslog(...$args) {
            return \WPO\IPS\Vendor\Safe\syslog(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_bind_array_by_name')){
        function oci_bind_array_by_name(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_bind_array_by_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_bind_by_name')){
        function oci_bind_by_name(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_bind_by_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_cancel')){
        function oci_cancel(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_cancel(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_commit')){
        function oci_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_connect')){
        function oci_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_define_by_name')){
        function oci_define_by_name(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_define_by_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_execute')){
        function oci_execute(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_execute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_name')){
        function oci_field_name(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_precision')){
        function oci_field_precision(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_precision(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_scale')){
        function oci_field_scale(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_scale(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_size')){
        function oci_field_size(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_type_raw')){
        function oci_field_type_raw(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_type_raw(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_field_type')){
        function oci_field_type(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_field_type(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_free_descriptor')){
        function oci_free_descriptor(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_free_descriptor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_free_statement')){
        function oci_free_statement(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_free_statement(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_new_collection')){
        function oci_new_collection(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_new_collection(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_new_connect')){
        function oci_new_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_new_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_new_cursor')){
        function oci_new_cursor(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_new_cursor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_new_descriptor')){
        function oci_new_descriptor(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_new_descriptor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_num_rows')){
        function oci_num_rows(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_num_rows(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_parse')){
        function oci_parse(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_parse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_pconnect')){
        function oci_pconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_pconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_register_taf_callback')){
        function oci_register_taf_callback(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_register_taf_callback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_result')){
        function oci_result(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_rollback')){
        function oci_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_server_version')){
        function oci_server_version(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_server_version(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_action')){
        function oci_set_action(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_action(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_call_timeout')){
        function oci_set_call_timeout(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_call_timeout(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_client_identifier')){
        function oci_set_client_identifier(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_client_identifier(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_client_info')){
        function oci_set_client_info(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_client_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_db_operation')){
        function oci_set_db_operation(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_db_operation(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_edition')){
        function oci_set_edition(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_edition(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_module_name')){
        function oci_set_module_name(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_module_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_prefetch')){
        function oci_set_prefetch(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_prefetch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_statement_type')){
        function oci_statement_type(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_statement_type(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_unregister_taf_callback')){
        function oci_unregister_taf_callback(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_unregister_taf_callback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\opcache_compile_file')){
        function opcache_compile_file(...$args) {
            return \WPO\IPS\Vendor\Safe\opcache_compile_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\opcache_get_status')){
        function opcache_get_status(...$args) {
            return \WPO\IPS\Vendor\Safe\opcache_get_status(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cipher_iv_length')){
        function openssl_cipher_iv_length(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cipher_iv_length(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cms_decrypt')){
        function openssl_cms_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cms_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cms_encrypt')){
        function openssl_cms_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cms_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cms_read')){
        function openssl_cms_read(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cms_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cms_sign')){
        function openssl_cms_sign(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cms_sign(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cms_verify')){
        function openssl_cms_verify(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cms_verify(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_export_to_file')){
        function openssl_csr_export_to_file(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_export_to_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_export')){
        function openssl_csr_export(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_get_public_key')){
        function openssl_csr_get_public_key(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_get_public_key(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_get_subject')){
        function openssl_csr_get_subject(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_get_subject(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_new')){
        function openssl_csr_new(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_csr_sign')){
        function openssl_csr_sign(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_csr_sign(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_decrypt')){
        function openssl_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_dh_compute_key')){
        function openssl_dh_compute_key(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_dh_compute_key(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_digest')){
        function openssl_digest(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_digest(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_get_curve_names')){
        function openssl_get_curve_names(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_get_curve_names(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_open')){
        function openssl_open(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pbkdf2')){
        function openssl_pbkdf2(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pbkdf2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs12_export_to_file')){
        function openssl_pkcs12_export_to_file(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs12_export_to_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs12_export')){
        function openssl_pkcs12_export(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs12_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs12_read')){
        function openssl_pkcs12_read(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs12_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs7_decrypt')){
        function openssl_pkcs7_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs7_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs7_encrypt')){
        function openssl_pkcs7_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs7_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs7_read')){
        function openssl_pkcs7_read(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs7_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkcs7_sign')){
        function openssl_pkcs7_sign(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkcs7_sign(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_derive')){
        function openssl_pkey_derive(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_derive(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_export_to_file')){
        function openssl_pkey_export_to_file(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_export_to_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_export')){
        function openssl_pkey_export(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_get_details')){
        function openssl_pkey_get_details(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_get_details(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_get_private')){
        function openssl_pkey_get_private(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_get_private(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_get_public')){
        function openssl_pkey_get_public(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_get_public(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_pkey_new')){
        function openssl_pkey_new(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_pkey_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_private_decrypt')){
        function openssl_private_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_private_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_private_encrypt')){
        function openssl_private_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_private_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_public_decrypt')){
        function openssl_public_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_public_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_public_encrypt')){
        function openssl_public_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_public_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_random_pseudo_bytes')){
        function openssl_random_pseudo_bytes(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_random_pseudo_bytes(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_seal')){
        function openssl_seal(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_seal(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_sign')){
        function openssl_sign(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_sign(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_spki_export_challenge')){
        function openssl_spki_export_challenge(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_spki_export_challenge(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_spki_export')){
        function openssl_spki_export(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_spki_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_spki_new')){
        function openssl_spki_new(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_spki_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_spki_verify')){
        function openssl_spki_verify(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_spki_verify(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_verify')){
        function openssl_verify(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_verify(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_x509_checkpurpose')){
        function openssl_x509_checkpurpose(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_x509_checkpurpose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_x509_export_to_file')){
        function openssl_x509_export_to_file(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_x509_export_to_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_x509_export')){
        function openssl_x509_export(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_x509_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_x509_fingerprint')){
        function openssl_x509_fingerprint(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_x509_fingerprint(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_x509_read')){
        function openssl_x509_read(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_x509_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_clean')){
        function ob_clean(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_clean(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_end_clean')){
        function ob_end_clean(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_end_clean(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_end_flush')){
        function ob_end_flush(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_end_flush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_flush')){
        function ob_flush(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_flush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_get_clean')){
        function ob_get_clean(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_get_clean(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_start')){
        function ob_start(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_start(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\output_add_rewrite_var')){
        function output_add_rewrite_var(...$args) {
            return \WPO\IPS\Vendor\Safe\output_add_rewrite_var(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\output_reset_rewrite_vars')){
        function output_reset_rewrite_vars(...$args) {
            return \WPO\IPS\Vendor\Safe\output_reset_rewrite_vars(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_getpriority')){
        function pcntl_getpriority(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_getpriority(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_setpriority')){
        function pcntl_setpriority(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_setpriority(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_signal_dispatch')){
        function pcntl_signal_dispatch(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_signal_dispatch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_signal')){
        function pcntl_signal(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_signal(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_sigprocmask')){
        function pcntl_sigprocmask(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_sigprocmask(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_sigtimedwait')){
        function pcntl_sigtimedwait(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_sigtimedwait(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_sigwaitinfo')){
        function pcntl_sigwaitinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_sigwaitinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_grep')){
        function preg_grep(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_grep(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_match_all')){
        function preg_match_all(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_match_all(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_match')){
        function preg_match(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_match(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_replace_callback_array')){
        function preg_replace_callback_array(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_replace_callback_array(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_replace_callback')){
        function preg_replace_callback(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_replace_callback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_split')){
        function preg_split(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_split(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_cancel_query')){
        function pg_cancel_query(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_cancel_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_connect')){
        function pg_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_connection_reset')){
        function pg_connection_reset(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_connection_reset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_convert')){
        function pg_convert(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_convert(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_copy_from')){
        function pg_copy_from(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_copy_from(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_copy_to')){
        function pg_copy_to(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_copy_to(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_delete')){
        function pg_delete(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_delete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_end_copy')){
        function pg_end_copy(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_end_copy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_execute')){
        function pg_execute(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_execute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_field_num')){
        function pg_field_num(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_field_num(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_field_table')){
        function pg_field_table(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_field_table(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_flush')){
        function pg_flush(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_flush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_free_result')){
        function pg_free_result(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_free_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_host')){
        function pg_host(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_host(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_insert')){
        function pg_insert(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_insert(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_last_oid')){
        function pg_last_oid(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_last_oid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_close')){
        function pg_lo_close(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_export')){
        function pg_lo_export(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_import')){
        function pg_lo_import(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_import(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_open')){
        function pg_lo_open(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_read')){
        function pg_lo_read(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_seek')){
        function pg_lo_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_truncate')){
        function pg_lo_truncate(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_truncate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_unlink')){
        function pg_lo_unlink(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_unlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_lo_write')){
        function pg_lo_write(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_lo_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_meta_data')){
        function pg_meta_data(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_meta_data(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_parameter_status')){
        function pg_parameter_status(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_parameter_status(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_pconnect')){
        function pg_pconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_pconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_ping')){
        function pg_ping(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_ping(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_prepare')){
        function pg_prepare(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_prepare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_put_line')){
        function pg_put_line(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_put_line(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_query_params')){
        function pg_query_params(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_query_params(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_query')){
        function pg_query(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_result_error_field')){
        function pg_result_error_field(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_result_error_field(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_result_seek')){
        function pg_result_seek(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_result_seek(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_select')){
        function pg_select(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_select(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_socket')){
        function pg_socket(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_socket(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_trace')){
        function pg_trace(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_trace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_update')){
        function pg_update(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_update(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_access')){
        function posix_access(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_access(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getgrgid')){
        function posix_getgrgid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getgrgid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getgrnam')){
        function posix_getgrnam(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getgrnam(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getgroups')){
        function posix_getgroups(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getgroups(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getlogin')){
        function posix_getlogin(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getlogin(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getpwuid')){
        function posix_getpwuid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getpwuid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getrlimit')){
        function posix_getrlimit(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getrlimit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getsid')){
        function posix_getsid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getsid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_initgroups')){
        function posix_initgroups(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_initgroups(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_kill')){
        function posix_kill(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_kill(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_mkfifo')){
        function posix_mkfifo(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_mkfifo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_mknod')){
        function posix_mknod(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_mknod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setegid')){
        function posix_setegid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setegid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_seteuid')){
        function posix_seteuid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_seteuid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setgid')){
        function posix_setgid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setgid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setpgid')){
        function posix_setpgid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setpgid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setrlimit')){
        function posix_setrlimit(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setrlimit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setsid')){
        function posix_setsid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setsid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_setuid')){
        function posix_setuid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_setuid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_times')){
        function posix_times(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_times(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_uname')){
        function posix_uname(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_uname(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_add_launchlink')){
        function ps_add_launchlink(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_add_launchlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_add_locallink')){
        function ps_add_locallink(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_add_locallink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_add_note')){
        function ps_add_note(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_add_note(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_add_pdflink')){
        function ps_add_pdflink(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_add_pdflink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_add_weblink')){
        function ps_add_weblink(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_add_weblink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_arc')){
        function ps_arc(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_arc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_arcn')){
        function ps_arcn(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_arcn(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_begin_page')){
        function ps_begin_page(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_begin_page(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_begin_pattern')){
        function ps_begin_pattern(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_begin_pattern(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_begin_template')){
        function ps_begin_template(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_begin_template(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_circle')){
        function ps_circle(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_circle(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_clip')){
        function ps_clip(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_clip(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_close_image')){
        function ps_close_image(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_close_image(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_close')){
        function ps_close(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_closepath_stroke')){
        function ps_closepath_stroke(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_closepath_stroke(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_closepath')){
        function ps_closepath(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_closepath(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_continue_text')){
        function ps_continue_text(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_continue_text(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_curveto')){
        function ps_curveto(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_curveto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_delete')){
        function ps_delete(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_delete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_end_page')){
        function ps_end_page(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_end_page(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_end_pattern')){
        function ps_end_pattern(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_end_pattern(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_end_template')){
        function ps_end_template(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_end_template(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_fill_stroke')){
        function ps_fill_stroke(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_fill_stroke(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_fill')){
        function ps_fill(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_fill(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_get_parameter')){
        function ps_get_parameter(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_get_parameter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_hyphenate')){
        function ps_hyphenate(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_hyphenate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_include_file')){
        function ps_include_file(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_include_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_lineto')){
        function ps_lineto(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_lineto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_moveto')){
        function ps_moveto(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_moveto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_new')){
        function ps_new(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_open_file')){
        function ps_open_file(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_open_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_place_image')){
        function ps_place_image(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_place_image(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_rect')){
        function ps_rect(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_rect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_restore')){
        function ps_restore(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_restore(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_rotate')){
        function ps_rotate(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_rotate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_save')){
        function ps_save(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_save(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_scale')){
        function ps_scale(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_scale(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_border_color')){
        function ps_set_border_color(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_border_color(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_border_dash')){
        function ps_set_border_dash(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_border_dash(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_border_style')){
        function ps_set_border_style(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_border_style(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_info')){
        function ps_set_info(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_parameter')){
        function ps_set_parameter(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_parameter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_text_pos')){
        function ps_set_text_pos(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_text_pos(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_set_value')){
        function ps_set_value(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_set_value(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setcolor')){
        function ps_setcolor(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setcolor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setdash')){
        function ps_setdash(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setdash(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setflat')){
        function ps_setflat(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setflat(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setfont')){
        function ps_setfont(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setfont(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setgray')){
        function ps_setgray(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setgray(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setlinecap')){
        function ps_setlinecap(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setlinecap(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setlinejoin')){
        function ps_setlinejoin(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setlinejoin(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setlinewidth')){
        function ps_setlinewidth(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setlinewidth(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setmiterlimit')){
        function ps_setmiterlimit(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setmiterlimit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setoverprintmode')){
        function ps_setoverprintmode(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setoverprintmode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_setpolydash')){
        function ps_setpolydash(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_setpolydash(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_shading_pattern')){
        function ps_shading_pattern(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_shading_pattern(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_shading')){
        function ps_shading(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_shading(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_shfill')){
        function ps_shfill(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_shfill(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_show_xy')){
        function ps_show_xy(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_show_xy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_show_xy2')){
        function ps_show_xy2(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_show_xy2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_show')){
        function ps_show(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_show(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_show2')){
        function ps_show2(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_show2(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_stroke')){
        function ps_stroke(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_stroke(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_symbol')){
        function ps_symbol(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_symbol(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ps_translate')){
        function ps_translate(...$args) {
            return \WPO\IPS\Vendor\Safe\ps_translate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_add_to_personal')){
        function pspell_add_to_personal(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_add_to_personal(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_add_to_session')){
        function pspell_add_to_session(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_add_to_session(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_clear_session')){
        function pspell_clear_session(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_clear_session(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_create')){
        function pspell_config_create(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_data_dir')){
        function pspell_config_data_dir(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_data_dir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_dict_dir')){
        function pspell_config_dict_dir(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_dict_dir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_ignore')){
        function pspell_config_ignore(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_ignore(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_mode')){
        function pspell_config_mode(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_mode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_personal')){
        function pspell_config_personal(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_personal(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_repl')){
        function pspell_config_repl(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_repl(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_runtogether')){
        function pspell_config_runtogether(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_runtogether(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_config_save_repl')){
        function pspell_config_save_repl(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_config_save_repl(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_new_config')){
        function pspell_new_config(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_new_config(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_new_personal')){
        function pspell_new_personal(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_new_personal(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_new')){
        function pspell_new(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_new(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_save_wordlist')){
        function pspell_save_wordlist(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_save_wordlist(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pspell_store_replacement')){
        function pspell_store_replacement(...$args) {
            return \WPO\IPS\Vendor\Safe\pspell_store_replacement(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_add_history')){
        function readline_add_history(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_add_history(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_callback_handler_install')){
        function readline_callback_handler_install(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_callback_handler_install(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_clear_history')){
        function readline_clear_history(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_clear_history(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_completion_function')){
        function readline_completion_function(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_completion_function(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_read_history')){
        function readline_read_history(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_read_history(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readline_write_history')){
        function readline_write_history(...$args) {
            return \WPO\IPS\Vendor\Safe\readline_write_history(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rpmaddtag')){
        function rpmaddtag(...$args) {
            return \WPO\IPS\Vendor\Safe\rpmaddtag(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_create')){
        function rrd_create(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_first')){
        function rrd_first(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_first(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_graph')){
        function rrd_graph(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_graph(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_info')){
        function rrd_info(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_lastupdate')){
        function rrd_lastupdate(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_lastupdate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_restore')){
        function rrd_restore(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_restore(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_tune')){
        function rrd_tune(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_tune(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_update')){
        function rrd_update(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_update(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rrd_xport')){
        function rrd_xport(...$args) {
            return \WPO\IPS\Vendor\Safe\rrd_xport(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_get_queue')){
        function msg_get_queue(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_get_queue(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_queue_exists')){
        function msg_queue_exists(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_queue_exists(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_receive')){
        function msg_receive(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_receive(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_remove_queue')){
        function msg_remove_queue(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_remove_queue(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_send')){
        function msg_send(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_send(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_set_queue')){
        function msg_set_queue(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_set_queue(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\msg_stat_queue')){
        function msg_stat_queue(...$args) {
            return \WPO\IPS\Vendor\Safe\msg_stat_queue(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sem_acquire')){
        function sem_acquire(...$args) {
            return \WPO\IPS\Vendor\Safe\sem_acquire(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sem_get')){
        function sem_get(...$args) {
            return \WPO\IPS\Vendor\Safe\sem_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sem_release')){
        function sem_release(...$args) {
            return \WPO\IPS\Vendor\Safe\sem_release(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sem_remove')){
        function sem_remove(...$args) {
            return \WPO\IPS\Vendor\Safe\sem_remove(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shm_attach')){
        function shm_attach(...$args) {
            return \WPO\IPS\Vendor\Safe\shm_attach(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shm_detach')){
        function shm_detach(...$args) {
            return \WPO\IPS\Vendor\Safe\shm_detach(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shm_put_var')){
        function shm_put_var(...$args) {
            return \WPO\IPS\Vendor\Safe\shm_put_var(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shm_remove_var')){
        function shm_remove_var(...$args) {
            return \WPO\IPS\Vendor\Safe\shm_remove_var(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shm_remove')){
        function shm_remove(...$args) {
            return \WPO\IPS\Vendor\Safe\shm_remove(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_abort')){
        function session_abort(...$args) {
            return \WPO\IPS\Vendor\Safe\session_abort(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_cache_expire')){
        function session_cache_expire(...$args) {
            return \WPO\IPS\Vendor\Safe\session_cache_expire(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_cache_limiter')){
        function session_cache_limiter(...$args) {
            return \WPO\IPS\Vendor\Safe\session_cache_limiter(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_create_id')){
        function session_create_id(...$args) {
            return \WPO\IPS\Vendor\Safe\session_create_id(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_decode')){
        function session_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\session_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_destroy')){
        function session_destroy(...$args) {
            return \WPO\IPS\Vendor\Safe\session_destroy(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_encode')){
        function session_encode(...$args) {
            return \WPO\IPS\Vendor\Safe\session_encode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_gc')){
        function session_gc(...$args) {
            return \WPO\IPS\Vendor\Safe\session_gc(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_id')){
        function session_id(...$args) {
            return \WPO\IPS\Vendor\Safe\session_id(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_module_name')){
        function session_module_name(...$args) {
            return \WPO\IPS\Vendor\Safe\session_module_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_name')){
        function session_name(...$args) {
            return \WPO\IPS\Vendor\Safe\session_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_regenerate_id')){
        function session_regenerate_id(...$args) {
            return \WPO\IPS\Vendor\Safe\session_regenerate_id(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_reset')){
        function session_reset(...$args) {
            return \WPO\IPS\Vendor\Safe\session_reset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_save_path')){
        function session_save_path(...$args) {
            return \WPO\IPS\Vendor\Safe\session_save_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_start')){
        function session_start(...$args) {
            return \WPO\IPS\Vendor\Safe\session_start(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_unset')){
        function session_unset(...$args) {
            return \WPO\IPS\Vendor\Safe\session_unset(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\session_write_close')){
        function session_write_close(...$args) {
            return \WPO\IPS\Vendor\Safe\session_write_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shmop_delete')){
        function shmop_delete(...$args) {
            return \WPO\IPS\Vendor\Safe\shmop_delete(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\shmop_read')){
        function shmop_read(...$args) {
            return \WPO\IPS\Vendor\Safe\shmop_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_accept')){
        function socket_accept(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_accept(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_addrinfo_bind')){
        function socket_addrinfo_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_addrinfo_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_addrinfo_connect')){
        function socket_addrinfo_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_addrinfo_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_addrinfo_lookup')){
        function socket_addrinfo_lookup(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_addrinfo_lookup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_bind')){
        function socket_bind(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_bind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_connect')){
        function socket_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_create_listen')){
        function socket_create_listen(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_create_listen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_create_pair')){
        function socket_create_pair(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_create_pair(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_create')){
        function socket_create(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_export_stream')){
        function socket_export_stream(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_export_stream(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_get_option')){
        function socket_get_option(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_get_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_getpeername')){
        function socket_getpeername(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_getpeername(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_getsockname')){
        function socket_getsockname(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_getsockname(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_import_stream')){
        function socket_import_stream(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_import_stream(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_listen')){
        function socket_listen(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_listen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_read')){
        function socket_read(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_send')){
        function socket_send(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_send(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_sendmsg')){
        function socket_sendmsg(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_sendmsg(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_sendto')){
        function socket_sendto(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_sendto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_set_block')){
        function socket_set_block(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_set_block(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_set_nonblock')){
        function socket_set_nonblock(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_set_nonblock(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_set_option')){
        function socket_set_option(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_set_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_shutdown')){
        function socket_shutdown(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_shutdown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_wsaprotocol_info_export')){
        function socket_wsaprotocol_info_export(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_wsaprotocol_info_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_wsaprotocol_info_import')){
        function socket_wsaprotocol_info_import(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_wsaprotocol_info_import(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_wsaprotocol_info_release')){
        function socket_wsaprotocol_info_release(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_wsaprotocol_info_release(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_aes256gcm_decrypt')){
        function sodium_crypto_aead_aes256gcm_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_aes256gcm_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_chacha20poly1305_decrypt')){
        function sodium_crypto_aead_chacha20poly1305_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_chacha20poly1305_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_chacha20poly1305_encrypt')){
        function sodium_crypto_aead_chacha20poly1305_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_chacha20poly1305_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_chacha20poly1305_ietf_decrypt')){
        function sodium_crypto_aead_chacha20poly1305_ietf_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_chacha20poly1305_ietf_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_chacha20poly1305_ietf_encrypt')){
        function sodium_crypto_aead_chacha20poly1305_ietf_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_chacha20poly1305_ietf_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_xchacha20poly1305_ietf_decrypt')){
        function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')){
        function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_auth_verify')){
        function sodium_crypto_auth_verify(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_auth_verify(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_box_open')){
        function sodium_crypto_box_open(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_box_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_box_seal_open')){
        function sodium_crypto_box_seal_open(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_box_seal_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_generichash_update')){
        function sodium_crypto_generichash_update(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_generichash_update(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_secretbox_open')){
        function sodium_crypto_secretbox_open(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_secretbox_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_sign_open')){
        function sodium_crypto_sign_open(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_sign_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_sign_verify_detached')){
        function sodium_crypto_sign_verify_detached(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_sign_verify_detached(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\solr_get_version')){
        function solr_get_version(...$args) {
            return \WPO\IPS\Vendor\Safe\solr_get_version(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\class_implements')){
        function class_implements(...$args) {
            return \WPO\IPS\Vendor\Safe\class_implements(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\class_parents')){
        function class_parents(...$args) {
            return \WPO\IPS\Vendor\Safe\class_parents(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\class_uses')){
        function class_uses(...$args) {
            return \WPO\IPS\Vendor\Safe\class_uses(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\spl_autoload_register')){
        function spl_autoload_register(...$args) {
            return \WPO\IPS\Vendor\Safe\spl_autoload_register(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\spl_autoload_unregister')){
        function spl_autoload_unregister(...$args) {
            return \WPO\IPS\Vendor\Safe\spl_autoload_unregister(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_begin_transaction')){
        function sqlsrv_begin_transaction(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_begin_transaction(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_cancel')){
        function sqlsrv_cancel(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_cancel(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_client_info')){
        function sqlsrv_client_info(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_client_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_close')){
        function sqlsrv_close(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_commit')){
        function sqlsrv_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_configure')){
        function sqlsrv_configure(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_configure(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_execute')){
        function sqlsrv_execute(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_execute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_fetch_array')){
        function sqlsrv_fetch_array(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_fetch_array(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_fetch_object')){
        function sqlsrv_fetch_object(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_fetch_object(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_fetch')){
        function sqlsrv_fetch(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_fetch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_free_stmt')){
        function sqlsrv_free_stmt(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_free_stmt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_get_field')){
        function sqlsrv_get_field(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_get_field(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_next_result')){
        function sqlsrv_next_result(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_next_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_num_fields')){
        function sqlsrv_num_fields(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_num_fields(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_num_rows')){
        function sqlsrv_num_rows(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_num_rows(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_prepare')){
        function sqlsrv_prepare(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_prepare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_query')){
        function sqlsrv_query(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_query(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sqlsrv_rollback')){
        function sqlsrv_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\sqlsrv_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssdeep_fuzzy_compare')){
        function ssdeep_fuzzy_compare(...$args) {
            return \WPO\IPS\Vendor\Safe\ssdeep_fuzzy_compare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssdeep_fuzzy_hash_filename')){
        function ssdeep_fuzzy_hash_filename(...$args) {
            return \WPO\IPS\Vendor\Safe\ssdeep_fuzzy_hash_filename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssdeep_fuzzy_hash')){
        function ssdeep_fuzzy_hash(...$args) {
            return \WPO\IPS\Vendor\Safe\ssdeep_fuzzy_hash(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_auth_agent')){
        function ssh2_auth_agent(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_auth_agent(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_auth_hostbased_file')){
        function ssh2_auth_hostbased_file(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_auth_hostbased_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_auth_password')){
        function ssh2_auth_password(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_auth_password(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_auth_pubkey_file')){
        function ssh2_auth_pubkey_file(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_auth_pubkey_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_connect')){
        function ssh2_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_disconnect')){
        function ssh2_disconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_disconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_exec')){
        function ssh2_exec(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_exec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_forward_accept')){
        function ssh2_forward_accept(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_forward_accept(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_forward_listen')){
        function ssh2_forward_listen(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_forward_listen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_publickey_add')){
        function ssh2_publickey_add(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_publickey_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_publickey_init')){
        function ssh2_publickey_init(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_publickey_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_publickey_remove')){
        function ssh2_publickey_remove(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_publickey_remove(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_scp_recv')){
        function ssh2_scp_recv(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_scp_recv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_scp_send')){
        function ssh2_scp_send(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_scp_send(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_send_eof')){
        function ssh2_send_eof(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_send_eof(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_chmod')){
        function ssh2_sftp_chmod(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_chmod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_mkdir')){
        function ssh2_sftp_mkdir(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_mkdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_rename')){
        function ssh2_sftp_rename(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_rename(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_rmdir')){
        function ssh2_sftp_rmdir(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_rmdir(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_symlink')){
        function ssh2_sftp_symlink(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_symlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp_unlink')){
        function ssh2_sftp_unlink(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp_unlink(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_sftp')){
        function ssh2_sftp(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_sftp(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ssh2_shell')){
        function ssh2_shell(...$args) {
            return \WPO\IPS\Vendor\Safe\ssh2_shell(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_context_set_params')){
        function stream_context_set_params(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_context_set_params(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_copy_to_stream')){
        function stream_copy_to_stream(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_copy_to_stream(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_filter_append')){
        function stream_filter_append(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_filter_append(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_filter_prepend')){
        function stream_filter_prepend(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_filter_prepend(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_filter_register')){
        function stream_filter_register(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_filter_register(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_filter_remove')){
        function stream_filter_remove(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_filter_remove(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_get_contents')){
        function stream_get_contents(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_get_contents(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_get_line')){
        function stream_get_line(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_get_line(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_isatty')){
        function stream_isatty(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_isatty(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_resolve_include_path')){
        function stream_resolve_include_path(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_resolve_include_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_set_blocking')){
        function stream_set_blocking(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_set_blocking(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_set_timeout')){
        function stream_set_timeout(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_set_timeout(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_accept')){
        function stream_socket_accept(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_accept(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_client')){
        function stream_socket_client(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_client(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_get_name')){
        function stream_socket_get_name(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_get_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_pair')){
        function stream_socket_pair(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_pair(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_server')){
        function stream_socket_server(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_server(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_shutdown')){
        function stream_socket_shutdown(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_shutdown(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_supports_lock')){
        function stream_supports_lock(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_supports_lock(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_wrapper_register')){
        function stream_wrapper_register(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_wrapper_register(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_wrapper_restore')){
        function stream_wrapper_restore(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_wrapper_restore(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_wrapper_unregister')){
        function stream_wrapper_unregister(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_wrapper_unregister(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\convert_uudecode')){
        function convert_uudecode(...$args) {
            return \WPO\IPS\Vendor\Safe\convert_uudecode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\hex2bin')){
        function hex2bin(...$args) {
            return \WPO\IPS\Vendor\Safe\hex2bin(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\md5_file')){
        function md5_file(...$args) {
            return \WPO\IPS\Vendor\Safe\md5_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sha1_file')){
        function sha1_file(...$args) {
            return \WPO\IPS\Vendor\Safe\sha1_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_async_dns_lookup')){
        function swoole_async_dns_lookup(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_async_dns_lookup(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_async_readfile')){
        function swoole_async_readfile(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_async_readfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_async_write')){
        function swoole_async_write(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_async_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_async_writefile')){
        function swoole_async_writefile(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_async_writefile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_event_defer')){
        function swoole_event_defer(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_event_defer(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_event_del')){
        function swoole_event_del(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_event_del(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\swoole_event_write')){
        function swoole_event_write(...$args) {
            return \WPO\IPS\Vendor\Safe\swoole_event_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_autocommit')){
        function odbc_autocommit(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_autocommit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_binmode')){
        function odbc_binmode(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_binmode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_columnprivileges')){
        function odbc_columnprivileges(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_columnprivileges(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_columns')){
        function odbc_columns(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_columns(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_commit')){
        function odbc_commit(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_commit(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_connect')){
        function odbc_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_cursor')){
        function odbc_cursor(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_cursor(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_data_source')){
        function odbc_data_source(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_data_source(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_exec')){
        function odbc_exec(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_exec(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_execute')){
        function odbc_execute(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_execute(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_fetch_into')){
        function odbc_fetch_into(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_fetch_into(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_field_len')){
        function odbc_field_len(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_field_len(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_field_name')){
        function odbc_field_name(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_field_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_field_num')){
        function odbc_field_num(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_field_num(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_field_scale')){
        function odbc_field_scale(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_field_scale(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_field_type')){
        function odbc_field_type(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_field_type(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_foreignkeys')){
        function odbc_foreignkeys(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_foreignkeys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_gettypeinfo')){
        function odbc_gettypeinfo(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_gettypeinfo(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_longreadlen')){
        function odbc_longreadlen(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_longreadlen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_num_fields')){
        function odbc_num_fields(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_num_fields(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_pconnect')){
        function odbc_pconnect(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_pconnect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_prepare')){
        function odbc_prepare(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_prepare(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_primarykeys')){
        function odbc_primarykeys(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_primarykeys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_procedurecolumns')){
        function odbc_procedurecolumns(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_procedurecolumns(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_procedures')){
        function odbc_procedures(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_procedures(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_result_all')){
        function odbc_result_all(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_result_all(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_result')){
        function odbc_result(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_result(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_rollback')){
        function odbc_rollback(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_rollback(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_setoption')){
        function odbc_setoption(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_setoption(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_specialcolumns')){
        function odbc_specialcolumns(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_specialcolumns(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_statistics')){
        function odbc_statistics(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_statistics(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_tableprivileges')){
        function odbc_tableprivileges(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_tableprivileges(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\odbc_tables')){
        function odbc_tables(...$args) {
            return \WPO\IPS\Vendor\Safe\odbc_tables(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\uopz_extend')){
        function uopz_extend(...$args) {
            return \WPO\IPS\Vendor\Safe\uopz_extend(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\uopz_implement')){
        function uopz_implement(...$args) {
            return \WPO\IPS\Vendor\Safe\uopz_implement(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\base64_decode')){
        function base64_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\base64_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\get_headers')){
        function get_headers(...$args) {
            return \WPO\IPS\Vendor\Safe\get_headers(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\get_meta_tags')){
        function get_meta_tags(...$args) {
            return \WPO\IPS\Vendor\Safe\get_meta_tags(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\parse_url')){
        function parse_url(...$args) {
            return \WPO\IPS\Vendor\Safe\parse_url(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\settype')){
        function settype(...$args) {
            return \WPO\IPS\Vendor\Safe\settype(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_bdiff')){
        function xdiff_file_bdiff(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_bdiff(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_bpatch')){
        function xdiff_file_bpatch(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_bpatch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_diff_binary')){
        function xdiff_file_diff_binary(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_diff_binary(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_diff')){
        function xdiff_file_diff(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_diff(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_patch_binary')){
        function xdiff_file_patch_binary(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_patch_binary(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_file_rabdiff')){
        function xdiff_file_rabdiff(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_file_rabdiff(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_string_bpatch')){
        function xdiff_string_bpatch(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_string_bpatch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_string_patch_binary')){
        function xdiff_string_patch_binary(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_string_patch_binary(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xdiff_string_patch')){
        function xdiff_string_patch(...$args) {
            return \WPO\IPS\Vendor\Safe\xdiff_string_patch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_parser_free')){
        function xml_parser_free(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_parser_free(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_character_data_handler')){
        function xml_set_character_data_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_character_data_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_default_handler')){
        function xml_set_default_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_default_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_element_handler')){
        function xml_set_element_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_element_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_end_namespace_decl_handler')){
        function xml_set_end_namespace_decl_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_end_namespace_decl_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_external_entity_ref_handler')){
        function xml_set_external_entity_ref_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_external_entity_ref_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_notation_decl_handler')){
        function xml_set_notation_decl_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_notation_decl_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_object')){
        function xml_set_object(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_object(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_processing_instruction_handler')){
        function xml_set_processing_instruction_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_processing_instruction_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_start_namespace_decl_handler')){
        function xml_set_start_namespace_decl_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_start_namespace_decl_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_set_unparsed_entity_decl_handler')){
        function xml_set_unparsed_entity_decl_handler(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_set_unparsed_entity_decl_handler(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xmlrpc_set_type')){
        function xmlrpc_set_type(...$args) {
            return \WPO\IPS\Vendor\Safe\xmlrpc_set_type(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaml_parse_file')){
        function yaml_parse_file(...$args) {
            return \WPO\IPS\Vendor\Safe\yaml_parse_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaml_parse_url')){
        function yaml_parse_url(...$args) {
            return \WPO\IPS\Vendor\Safe\yaml_parse_url(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaml_parse')){
        function yaml_parse(...$args) {
            return \WPO\IPS\Vendor\Safe\yaml_parse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_ccl_parse')){
        function yaz_ccl_parse(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_ccl_parse(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_close')){
        function yaz_close(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_connect')){
        function yaz_connect(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_connect(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_database')){
        function yaz_database(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_database(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_element')){
        function yaz_element(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_element(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_present')){
        function yaz_present(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_present(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_search')){
        function yaz_search(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_search(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\yaz_wait')){
        function yaz_wait(...$args) {
            return \WPO\IPS\Vendor\Safe\yaz_wait(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_close')){
        function zip_entry_close(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_close(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_compressedsize')){
        function zip_entry_compressedsize(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_compressedsize(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_compressionmethod')){
        function zip_entry_compressionmethod(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_compressionmethod(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_filesize')){
        function zip_entry_filesize(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_filesize(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_name')){
        function zip_entry_name(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_name(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_open')){
        function zip_entry_open(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_open(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zip_entry_read')){
        function zip_entry_read(...$args) {
            return \WPO\IPS\Vendor\Safe\zip_entry_read(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\deflate_add')){
        function deflate_add(...$args) {
            return \WPO\IPS\Vendor\Safe\deflate_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\deflate_init')){
        function deflate_init(...$args) {
            return \WPO\IPS\Vendor\Safe\deflate_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzclose')){
        function gzclose(...$args) {
            return \WPO\IPS\Vendor\Safe\gzclose(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzcompress')){
        function gzcompress(...$args) {
            return \WPO\IPS\Vendor\Safe\gzcompress(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzdecode')){
        function gzdecode(...$args) {
            return \WPO\IPS\Vendor\Safe\gzdecode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzdeflate')){
        function gzdeflate(...$args) {
            return \WPO\IPS\Vendor\Safe\gzdeflate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzencode')){
        function gzencode(...$args) {
            return \WPO\IPS\Vendor\Safe\gzencode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzfile')){
        function gzfile(...$args) {
            return \WPO\IPS\Vendor\Safe\gzfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzgets')){
        function gzgets(...$args) {
            return \WPO\IPS\Vendor\Safe\gzgets(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzinflate')){
        function gzinflate(...$args) {
            return \WPO\IPS\Vendor\Safe\gzinflate(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzopen')){
        function gzopen(...$args) {
            return \WPO\IPS\Vendor\Safe\gzopen(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzpassthru')){
        function gzpassthru(...$args) {
            return \WPO\IPS\Vendor\Safe\gzpassthru(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzread')){
        function gzread(...$args) {
            return \WPO\IPS\Vendor\Safe\gzread(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzrewind')){
        function gzrewind(...$args) {
            return \WPO\IPS\Vendor\Safe\gzrewind(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gztell')){
        function gztell(...$args) {
            return \WPO\IPS\Vendor\Safe\gztell(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzuncompress')){
        function gzuncompress(...$args) {
            return \WPO\IPS\Vendor\Safe\gzuncompress(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gzwrite')){
        function gzwrite(...$args) {
            return \WPO\IPS\Vendor\Safe\gzwrite(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inflate_get_read_len')){
        function inflate_get_read_len(...$args) {
            return \WPO\IPS\Vendor\Safe\inflate_get_read_len(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inflate_get_status')){
        function inflate_get_status(...$args) {
            return \WPO\IPS\Vendor\Safe\inflate_get_status(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inflate_add')){
        function inflate_add(...$args) {
            return \WPO\IPS\Vendor\Safe\inflate_add(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inflate_init')){
        function inflate_init(...$args) {
            return \WPO\IPS\Vendor\Safe\inflate_init(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\readgzfile')){
        function readgzfile(...$args) {
            return \WPO\IPS\Vendor\Safe\readgzfile(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\zlib_decode')){
        function zlib_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\zlib_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\curl_upkeep')){
        function curl_upkeep(...$args) {
            return \WPO\IPS\Vendor\Safe\curl_upkeep(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_create')){
        function date_create(...$args) {
            return \WPO\IPS\Vendor\Safe\date_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\passthru')){
        function passthru(...$args) {
            return \WPO\IPS\Vendor\Safe\passthru(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fpm_get_status')){
        function fpm_get_status(...$args) {
            return \WPO\IPS\Vendor\Safe\fpm_get_status(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_raw')){
        function ftp_raw(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_raw(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\gnupg_deletekey')){
        function gnupg_deletekey(...$args) {
            return \WPO\IPS\Vendor\Safe\gnupg_deletekey(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\net_get_interfaces')){
        function net_get_interfaces(...$args) {
            return \WPO\IPS\Vendor\Safe\net_get_interfaces(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\oci_set_prefetch_lob')){
        function oci_set_prefetch_lob(...$args) {
            return \WPO\IPS\Vendor\Safe\oci_set_prefetch_lob(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_cipher_key_length')){
        function openssl_cipher_key_length(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_cipher_key_length(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_decrypt')){
        function rnp_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_dump_packets_to_json')){
        function rnp_dump_packets_to_json(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_dump_packets_to_json(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_dump_packets')){
        function rnp_dump_packets(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_dump_packets(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_ffi_create')){
        function rnp_ffi_create(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_ffi_create(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_ffi_set_pass_provider')){
        function rnp_ffi_set_pass_provider(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_ffi_set_pass_provider(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_import_keys')){
        function rnp_import_keys(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_import_keys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_import_signatures')){
        function rnp_import_signatures(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_import_signatures(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_export_autocrypt')){
        function rnp_key_export_autocrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_export_autocrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_export_revocation')){
        function rnp_key_export_revocation(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_export_revocation(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_export')){
        function rnp_key_export(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_export(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_get_info')){
        function rnp_key_get_info(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_get_info(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_remove')){
        function rnp_key_remove(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_remove(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_key_revoke')){
        function rnp_key_revoke(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_key_revoke(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_list_keys')){
        function rnp_list_keys(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_list_keys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_load_keys_from_path')){
        function rnp_load_keys_from_path(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_load_keys_from_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_load_keys')){
        function rnp_load_keys(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_load_keys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_locate_key')){
        function rnp_locate_key(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_locate_key(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_encrypt')){
        function rnp_op_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_generate_key')){
        function rnp_op_generate_key(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_generate_key(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_sign_cleartext')){
        function rnp_op_sign_cleartext(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_sign_cleartext(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_sign_detached')){
        function rnp_op_sign_detached(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_sign_detached(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_sign')){
        function rnp_op_sign(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_sign(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_verify_detached')){
        function rnp_op_verify_detached(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_verify_detached(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_op_verify')){
        function rnp_op_verify(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_op_verify(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_save_keys_to_path')){
        function rnp_save_keys_to_path(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_save_keys_to_path(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_save_keys')){
        function rnp_save_keys(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_save_keys(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rnp_supported_features')){
        function rnp_supported_features(...$args) {
            return \WPO\IPS\Vendor\Safe\rnp_supported_features(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_stream_xchacha20_xor_ic')){
        function sodium_crypto_stream_xchacha20_xor_ic(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_stream_xchacha20_xor_ic(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_recvfrom')){
        function stream_socket_recvfrom(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_recvfrom(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_socket_sendto')){
        function stream_socket_sendto(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_socket_sendto(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\array_all')){
        function array_all(...$args) {
            return \WPO\IPS\Vendor\Safe\array_all(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\date_create_immutable')){
        function date_create_immutable(...$args) {
            return \WPO\IPS\Vendor\Safe\date_create_immutable(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ftp_nb_get')){
        function ftp_nb_get(...$args) {
            return \WPO\IPS\Vendor\Safe\ftp_nb_get(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\db2_num_rows')){
        function db2_num_rows(...$args) {
            return \WPO\IPS\Vendor\Safe\db2_num_rows(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\inotify_add_watch')){
        function inotify_add_watch(...$args) {
            return \WPO\IPS\Vendor\Safe\inotify_add_watch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\ob_get_flush')){
        function ob_get_flush(...$args) {
            return \WPO\IPS\Vendor\Safe\ob_get_flush(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pg_set_chunked_rows_size')){
        function pg_set_chunked_rows_size(...$args) {
            return \WPO\IPS\Vendor\Safe\pg_set_chunked_rows_size(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_eaccess')){
        function posix_eaccess(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_eaccess(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_atmark')){
        function socket_atmark(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_atmark(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_aegis128l_decrypt')){
        function sodium_crypto_aead_aegis128l_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_aegis128l_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sodium_crypto_aead_aegis256_decrypt')){
        function sodium_crypto_aead_aegis256_decrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\sodium_crypto_aead_aegis256_decrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\stream_context_set_options')){
        function stream_context_set_options(...$args) {
            return \WPO\IPS\Vendor\Safe\stream_context_set_options(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\xml_parser_set_option')){
        function xml_parser_set_option(...$args) {
            return \WPO\IPS\Vendor\Safe\xml_parser_set_option(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_getcpuaffinity')){
        function pcntl_getcpuaffinity(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_getcpuaffinity(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\pcntl_setcpuaffinity')){
        function pcntl_setcpuaffinity(...$args) {
            return \WPO\IPS\Vendor\Safe\pcntl_setcpuaffinity(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\rpmdefine')){
        function rpmdefine(...$args) {
            return \WPO\IPS\Vendor\Safe\rpmdefine(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\json_decode')){
        function json_decode(...$args) {
            return \WPO\IPS\Vendor\Safe\json_decode(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\apcu_fetch')){
        function apcu_fetch(...$args) {
            return \WPO\IPS\Vendor\Safe\apcu_fetch(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\preg_replace')){
        function preg_replace(...$args) {
            return \WPO\IPS\Vendor\Safe\preg_replace(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\openssl_encrypt')){
        function openssl_encrypt(...$args) {
            return \WPO\IPS\Vendor\Safe\openssl_encrypt(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\socket_write')){
        function socket_write(...$args) {
            return \WPO\IPS\Vendor\Safe\socket_write(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\simplexml_import_dom')){
        function simplexml_import_dom(...$args) {
            return \WPO\IPS\Vendor\Safe\simplexml_import_dom(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\simplexml_load_file')){
        function simplexml_load_file(...$args) {
            return \WPO\IPS\Vendor\Safe\simplexml_load_file(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\simplexml_load_string')){
        function simplexml_load_string(...$args) {
            return \WPO\IPS\Vendor\Safe\simplexml_load_string(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\sys_getloadavg')){
        function sys_getloadavg(...$args) {
            return \WPO\IPS\Vendor\Safe\sys_getloadavg(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\posix_getpgid')){
        function posix_getpgid(...$args) {
            return \WPO\IPS\Vendor\Safe\posix_getpgid(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fputcsv')){
        function fputcsv(...$args) {
            return \WPO\IPS\Vendor\Safe\fputcsv(...func_get_args());
        }
    }
    if(!function_exists('\\Safe\\fgetcsv')){
        function fgetcsv(...$args) {
            return \WPO\IPS\Vendor\Safe\fgetcsv(...func_get_args());
        }
    }
}


namespace WPO\IPS\Vendor {

    use BrianHenryIE\Strauss\Types\AutoloadAliasInterface;

    /**
     * @see AutoloadAliasInterface
     *
     * @phpstan-type ClassAliasArray array{'type':'class',isabstract:bool,classname:string,namespace?:string,extends:string,implements:array<string>}
     * @phpstan-type InterfaceAliasArray array{'type':'interface',interfacename:string,namespace?:string,extends:array<string>}
     * @phpstan-type TraitAliasArray array{'type':'trait',traitname:string,namespace?:string,use:array<string>}
     * @phpstan-type AutoloadAliasArray array<string,ClassAliasArray|InterfaceAliasArray|TraitAliasArray>
     */
    class AliasAutoloader
    {
        private string $includeFilePath;

        /**
         * @var AutoloadAliasArray
         */
        private array $autoloadAliases = array (
  'Dompdf\\Cpdf' => 
  array (
    'type' => 'class',
    'classname' => 'Cpdf',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Cpdf',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Adapter\\CPDF' => 
  array (
    'type' => 'class',
    'classname' => 'CPDF',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\CPDF',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\Adapter\\GD' => 
  array (
    'type' => 'class',
    'classname' => 'GD',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\GD',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\Adapter\\PDFLib' => 
  array (
    'type' => 'class',
    'classname' => 'PDFLib',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\PDFLib',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\CanvasFactory' => 
  array (
    'type' => 'class',
    'classname' => 'CanvasFactory',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\CanvasFactory',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Cellmap' => 
  array (
    'type' => 'class',
    'classname' => 'Cellmap',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Cellmap',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\AttributeTranslator' => 
  array (
    'type' => 'class',
    'classname' => 'AttributeTranslator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\AttributeTranslator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Color' => 
  array (
    'type' => 'class',
    'classname' => 'Color',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Color',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Attr' => 
  array (
    'type' => 'class',
    'classname' => 'Attr',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Attr',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\CloseQuote' => 
  array (
    'type' => 'class',
    'classname' => 'CloseQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\CloseQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\ContentPart' => 
  array (
    'type' => 'class',
    'classname' => 'ContentPart',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\ContentPart',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Counter' => 
  array (
    'type' => 'class',
    'classname' => 'Counter',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Counter',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Counters' => 
  array (
    'type' => 'class',
    'classname' => 'Counters',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Counters',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\NoCloseQuote' => 
  array (
    'type' => 'class',
    'classname' => 'NoCloseQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\NoCloseQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\NoOpenQuote' => 
  array (
    'type' => 'class',
    'classname' => 'NoOpenQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\NoOpenQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\OpenQuote' => 
  array (
    'type' => 'class',
    'classname' => 'OpenQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\OpenQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\StringPart' => 
  array (
    'type' => 'class',
    'classname' => 'StringPart',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\StringPart',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Url' => 
  array (
    'type' => 'class',
    'classname' => 'Url',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Url',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Style' => 
  array (
    'type' => 'class',
    'classname' => 'Style',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Style',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Stylesheet' => 
  array (
    'type' => 'class',
    'classname' => 'Stylesheet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Stylesheet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Dompdf' => 
  array (
    'type' => 'class',
    'classname' => 'Dompdf',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Dompdf',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Exception\\ImageException' => 
  array (
    'type' => 'class',
    'classname' => 'ImageException',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Exception',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Exception\\ImageException',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FontMetrics' => 
  array (
    'type' => 'class',
    'classname' => 'FontMetrics',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FontMetrics',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Frame\\Factory' => 
  array (
    'type' => 'class',
    'classname' => 'Factory',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\Factory',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Frame\\FrameListIterator' => 
  array (
    'type' => 'class',
    'classname' => 'FrameListIterator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameListIterator',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Dompdf\\Frame\\FrameTree' => 
  array (
    'type' => 'class',
    'classname' => 'FrameTree',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameTree',
    'implements' => 
    array (
      0 => 'IteratorAggregate',
    ),
  ),
  'Dompdf\\Frame\\FrameTreeIterator' => 
  array (
    'type' => 'class',
    'classname' => 'FrameTreeIterator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameTreeIterator',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Dompdf\\FrameDecorator\\AbstractFrameDecorator' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractFrameDecorator',
    'isabstract' => true,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\AbstractFrameDecorator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\ListBulletImage' => 
  array (
    'type' => 'class',
    'classname' => 'ListBulletImage',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\ListBulletImage',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\NullFrameDecorator' => 
  array (
    'type' => 'class',
    'classname' => 'NullFrameDecorator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\NullFrameDecorator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Page' => 
  array (
    'type' => 'class',
    'classname' => 'Page',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Page',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Table',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Text',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\AbstractFrameReflower' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractFrameReflower',
    'isabstract' => true,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\AbstractFrameReflower',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\NullFrameReflower' => 
  array (
    'type' => 'class',
    'classname' => 'NullFrameReflower',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\NullFrameReflower',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Page' => 
  array (
    'type' => 'class',
    'classname' => 'Page',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Page',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Table',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Text',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Helpers' => 
  array (
    'type' => 'class',
    'classname' => 'Helpers',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Helpers',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Image\\Cache' => 
  array (
    'type' => 'class',
    'classname' => 'Cache',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Image',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Image\\Cache',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\JavascriptEmbedder' => 
  array (
    'type' => 'class',
    'classname' => 'JavascriptEmbedder',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\JavascriptEmbedder',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\LineBox' => 
  array (
    'type' => 'class',
    'classname' => 'LineBox',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\LineBox',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Options' => 
  array (
    'type' => 'class',
    'classname' => 'Options',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Options',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\PhpEvaluator' => 
  array (
    'type' => 'class',
    'classname' => 'PhpEvaluator',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\PhpEvaluator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Absolute' => 
  array (
    'type' => 'class',
    'classname' => 'Absolute',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Absolute',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\AbstractPositioner' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractPositioner',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\AbstractPositioner',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Fixed' => 
  array (
    'type' => 'class',
    'classname' => 'Fixed',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Fixed',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\NullPositioner' => 
  array (
    'type' => 'class',
    'classname' => 'NullPositioner',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\NullPositioner',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\AbstractRenderer' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractRenderer',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\AbstractRenderer',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Text',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\AdobeFontMetrics' => 
  array (
    'type' => 'class',
    'classname' => 'AdobeFontMetrics',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\AdobeFontMetrics',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\BinaryStream' => 
  array (
    'type' => 'class',
    'classname' => 'BinaryStream',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\BinaryStream',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EOT\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\EOT',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EOT\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EOT\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\EOT',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EOT\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EncodingMap' => 
  array (
    'type' => 'class',
    'classname' => 'EncodingMap',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EncodingMap',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Exception\\FontNotFoundException' => 
  array (
    'type' => 'class',
    'classname' => 'FontNotFoundException',
    'isabstract' => false,
    'namespace' => 'FontLib\\Exception',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Exception\\FontNotFoundException',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Font' => 
  array (
    'type' => 'class',
    'classname' => 'Font',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Font',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\Outline' => 
  array (
    'type' => 'class',
    'classname' => 'Outline',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\Outline',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineComponent' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineComponent',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineComponent',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineComposite' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineComposite',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineComposite',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineSimple' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineSimple',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineSimple',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => true,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\OpenType\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\OpenType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\OpenType\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\OpenType\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\OpenType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\OpenType\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\DirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'DirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\DirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Table',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\cmap' => 
  array (
    'type' => 'class',
    'classname' => 'cmap',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\cmap',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\cvt' => 
  array (
    'type' => 'class',
    'classname' => 'cvt',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\cvt',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\fpgm' => 
  array (
    'type' => 'class',
    'classname' => 'fpgm',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\fpgm',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\glyf' => 
  array (
    'type' => 'class',
    'classname' => 'glyf',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\glyf',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\head' => 
  array (
    'type' => 'class',
    'classname' => 'head',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\head',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\hhea' => 
  array (
    'type' => 'class',
    'classname' => 'hhea',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\hhea',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\hmtx' => 
  array (
    'type' => 'class',
    'classname' => 'hmtx',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\hmtx',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\kern' => 
  array (
    'type' => 'class',
    'classname' => 'kern',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\kern',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\loca' => 
  array (
    'type' => 'class',
    'classname' => 'loca',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\loca',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\maxp' => 
  array (
    'type' => 'class',
    'classname' => 'maxp',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\maxp',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\name' => 
  array (
    'type' => 'class',
    'classname' => 'name',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\name',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\nameRecord' => 
  array (
    'type' => 'class',
    'classname' => 'nameRecord',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\nameRecord',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\os2' => 
  array (
    'type' => 'class',
    'classname' => 'os2',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\os2',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\post' => 
  array (
    'type' => 'class',
    'classname' => 'post',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\post',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\prep' => 
  array (
    'type' => 'class',
    'classname' => 'prep',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\prep',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\Collection' => 
  array (
    'type' => 'class',
    'classname' => 'Collection',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\Collection',
    'implements' => 
    array (
      0 => 'Iterator',
      1 => 'Countable',
    ),
  ),
  'FontLib\\TrueType\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'Svg\\CssLength' => 
  array (
    'type' => 'class',
    'classname' => 'CssLength',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\CssLength',
    'implements' => 
    array (
    ),
  ),
  'Svg\\DefaultStyle' => 
  array (
    'type' => 'class',
    'classname' => 'DefaultStyle',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\DefaultStyle',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Document' => 
  array (
    'type' => 'class',
    'classname' => 'Document',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Document',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Gradient\\Stop' => 
  array (
    'type' => 'class',
    'classname' => 'Stop',
    'isabstract' => false,
    'namespace' => 'Svg\\Gradient',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Gradient\\Stop',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Style' => 
  array (
    'type' => 'class',
    'classname' => 'Style',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Style',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Surface\\CPdf' => 
  array (
    'type' => 'class',
    'classname' => 'CPdf',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\CPdf',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Surface\\SurfaceCpdf' => 
  array (
    'type' => 'class',
    'classname' => 'SurfaceCpdf',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfaceCpdf',
    'implements' => 
    array (
      0 => 'Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Svg\\Surface\\SurfacePDFLib' => 
  array (
    'type' => 'class',
    'classname' => 'SurfacePDFLib',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfacePDFLib',
    'implements' => 
    array (
      0 => 'Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Svg\\Tag\\AbstractTag' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractTag',
    'isabstract' => true,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\AbstractTag',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Anchor' => 
  array (
    'type' => 'class',
    'classname' => 'Anchor',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Anchor',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Circle' => 
  array (
    'type' => 'class',
    'classname' => 'Circle',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Circle',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\ClipPath' => 
  array (
    'type' => 'class',
    'classname' => 'ClipPath',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\ClipPath',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Ellipse' => 
  array (
    'type' => 'class',
    'classname' => 'Ellipse',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Ellipse',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Group' => 
  array (
    'type' => 'class',
    'classname' => 'Group',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Group',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Image',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Line' => 
  array (
    'type' => 'class',
    'classname' => 'Line',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Line',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\LinearGradient' => 
  array (
    'type' => 'class',
    'classname' => 'LinearGradient',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\LinearGradient',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Path' => 
  array (
    'type' => 'class',
    'classname' => 'Path',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Path',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Polygon' => 
  array (
    'type' => 'class',
    'classname' => 'Polygon',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Polygon',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Polyline' => 
  array (
    'type' => 'class',
    'classname' => 'Polyline',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Polyline',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\RadialGradient' => 
  array (
    'type' => 'class',
    'classname' => 'RadialGradient',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\RadialGradient',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Rect' => 
  array (
    'type' => 'class',
    'classname' => 'Rect',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Rect',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Shape' => 
  array (
    'type' => 'class',
    'classname' => 'Shape',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Shape',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Stop' => 
  array (
    'type' => 'class',
    'classname' => 'Stop',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Stop',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\StyleTag' => 
  array (
    'type' => 'class',
    'classname' => 'StyleTag',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\StyleTag',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Symbol' => 
  array (
    'type' => 'class',
    'classname' => 'Symbol',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Symbol',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Text',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\UseTag' => 
  array (
    'type' => 'class',
    'classname' => 'UseTag',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\UseTag',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Elements' => 
  array (
    'type' => 'class',
    'classname' => 'Elements',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Elements',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Entities' => 
  array (
    'type' => 'class',
    'classname' => 'Entities',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Entities',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Exception' => 
  array (
    'type' => 'class',
    'classname' => 'Exception',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Exception',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\CharacterReference' => 
  array (
    'type' => 'class',
    'classname' => 'CharacterReference',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\CharacterReference',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\DOMTreeBuilder' => 
  array (
    'type' => 'class',
    'classname' => 'DOMTreeBuilder',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\DOMTreeBuilder',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\EventHandler',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\FileInputStream' => 
  array (
    'type' => 'class',
    'classname' => 'FileInputStream',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\FileInputStream',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\ParseError' => 
  array (
    'type' => 'class',
    'classname' => 'ParseError',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\ParseError',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\Scanner' => 
  array (
    'type' => 'class',
    'classname' => 'Scanner',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\Scanner',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\StringInputStream' => 
  array (
    'type' => 'class',
    'classname' => 'StringInputStream',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\StringInputStream',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\Tokenizer' => 
  array (
    'type' => 'class',
    'classname' => 'Tokenizer',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\Tokenizer',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\TreeBuildingRules' => 
  array (
    'type' => 'class',
    'classname' => 'TreeBuildingRules',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\TreeBuildingRules',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\UTF8Utils' => 
  array (
    'type' => 'class',
    'classname' => 'UTF8Utils',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\UTF8Utils',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\HTML5Entities' => 
  array (
    'type' => 'class',
    'classname' => 'HTML5Entities',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\HTML5Entities',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\OutputRules' => 
  array (
    'type' => 'class',
    'classname' => 'OutputRules',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\OutputRules',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Serializer\\RulesInterface',
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\Traverser' => 
  array (
    'type' => 'class',
    'classname' => 'Traverser',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\Traverser',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\AtRuleBlockList' => 
  array (
    'type' => 'class',
    'classname' => 'AtRuleBlockList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\AtRuleBlockList',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\CSSBlockList' => 
  array (
    'type' => 'class',
    'classname' => 'CSSBlockList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\CSSBlockList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\CSSList' => 
  array (
    'type' => 'class',
    'classname' => 'CSSList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\CSSList',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\CSSList\\CSSListItem',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\Document' => 
  array (
    'type' => 'class',
    'classname' => 'Document',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\Document',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\KeyFrame' => 
  array (
    'type' => 'class',
    'classname' => 'KeyFrame',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\KeyFrame',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\Comment\\Comment' => 
  array (
    'type' => 'class',
    'classname' => 'Comment',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Comment',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Comment\\Comment',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Position\\Positionable',
      1 => 'Sabberworm\\CSS\\Renderable',
    ),
  ),
  'Sabberworm\\CSS\\OutputFormat' => 
  array (
    'type' => 'class',
    'classname' => 'OutputFormat',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\OutputFormat',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\OutputFormatter' => 
  array (
    'type' => 'class',
    'classname' => 'OutputFormatter',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\OutputFormatter',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parser' => 
  array (
    'type' => 'class',
    'classname' => 'Parser',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parser',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\Anchor' => 
  array (
    'type' => 'class',
    'classname' => 'Anchor',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\Anchor',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\OutputException' => 
  array (
    'type' => 'class',
    'classname' => 'OutputException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\OutputException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\ParserState' => 
  array (
    'type' => 'class',
    'classname' => 'ParserState',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\ParserState',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\SourceException' => 
  array (
    'type' => 'class',
    'classname' => 'SourceException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\SourceException',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\UnexpectedEOFException' => 
  array (
    'type' => 'class',
    'classname' => 'UnexpectedEOFException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\UnexpectedEOFException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\UnexpectedTokenException' => 
  array (
    'type' => 'class',
    'classname' => 'UnexpectedTokenException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\UnexpectedTokenException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Property\\CSSNamespace' => 
  array (
    'type' => 'class',
    'classname' => 'CSSNamespace',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\CSSNamespace',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\Charset' => 
  array (
    'type' => 'class',
    'classname' => 'Charset',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Charset',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\Import' => 
  array (
    'type' => 'class',
    'classname' => 'Import',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Import',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\KeyframeSelector' => 
  array (
    'type' => 'class',
    'classname' => 'KeyframeSelector',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\KeyframeSelector',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Property\\Selector\\SpecificityCalculator' => 
  array (
    'type' => 'class',
    'classname' => 'SpecificityCalculator',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property\\Selector',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Selector\\SpecificityCalculator',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Rule\\Rule' => 
  array (
    'type' => 'class',
    'classname' => 'Rule',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Rule',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Rule\\Rule',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Comment\\Commentable',
      1 => 'Sabberworm\\CSS\\CSSElement',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\AtRuleSet' => 
  array (
    'type' => 'class',
    'classname' => 'AtRuleSet',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\AtRuleSet',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\DeclarationBlock' => 
  array (
    'type' => 'class',
    'classname' => 'DeclarationBlock',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\DeclarationBlock',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\CSSList\\CSSListItem',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
      3 => 'Sabberworm\\CSS\\RuleSet\\RuleContainer',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\RuleSet' => 
  array (
    'type' => 'class',
    'classname' => 'RuleSet',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\RuleSet',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\CSSList\\CSSListItem',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
      3 => 'Sabberworm\\CSS\\RuleSet\\RuleContainer',
    ),
  ),
  'Sabberworm\\CSS\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Settings',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CSSFunction' => 
  array (
    'type' => 'class',
    'classname' => 'CSSFunction',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CSSFunction',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CSSString' => 
  array (
    'type' => 'class',
    'classname' => 'CSSString',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CSSString',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CalcFunction' => 
  array (
    'type' => 'class',
    'classname' => 'CalcFunction',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CalcFunction',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CalcRuleValueList' => 
  array (
    'type' => 'class',
    'classname' => 'CalcRuleValueList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CalcRuleValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Color' => 
  array (
    'type' => 'class',
    'classname' => 'Color',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Color',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\LineName' => 
  array (
    'type' => 'class',
    'classname' => 'LineName',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\LineName',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\PrimitiveValue' => 
  array (
    'type' => 'class',
    'classname' => 'PrimitiveValue',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\PrimitiveValue',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\RuleValueList' => 
  array (
    'type' => 'class',
    'classname' => 'RuleValueList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\RuleValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Size' => 
  array (
    'type' => 'class',
    'classname' => 'Size',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Size',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\URL' => 
  array (
    'type' => 'class',
    'classname' => 'URL',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\URL',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Value' => 
  array (
    'type' => 'class',
    'classname' => 'Value',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Value',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Value\\ValueList' => 
  array (
    'type' => 'class',
    'classname' => 'ValueList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\ValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Uri\\InvalidUriException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidUriException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Uri',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Uri\\InvalidUriException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Uri\\Version' => 
  array (
    'type' => 'class',
    'classname' => 'Version',
    'isabstract' => false,
    'namespace' => 'Sabre\\Uri',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Uri\\Version',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Element\\Base' => 
  array (
    'type' => 'class',
    'classname' => 'Base',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Base',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\Cdata' => 
  array (
    'type' => 'class',
    'classname' => 'Cdata',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Cdata',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\XmlSerializable',
    ),
  ),
  'Sabre\\Xml\\Element\\Elements' => 
  array (
    'type' => 'class',
    'classname' => 'Elements',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Elements',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\KeyValue' => 
  array (
    'type' => 'class',
    'classname' => 'KeyValue',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\KeyValue',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\Uri' => 
  array (
    'type' => 'class',
    'classname' => 'Uri',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Uri',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\XmlFragment' => 
  array (
    'type' => 'class',
    'classname' => 'XmlFragment',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\XmlFragment',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\LibXMLException' => 
  array (
    'type' => 'class',
    'classname' => 'LibXMLException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\LibXMLException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\ParseException' => 
  array (
    'type' => 'class',
    'classname' => 'ParseException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\ParseException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Reader' => 
  array (
    'type' => 'class',
    'classname' => 'Reader',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Reader',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Service' => 
  array (
    'type' => 'class',
    'classname' => 'Service',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Service',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Version' => 
  array (
    'type' => 'class',
    'classname' => 'Version',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Version',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Writer' => 
  array (
    'type' => 'class',
    'classname' => 'Writer',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Writer',
    'implements' => 
    array (
    ),
  ),
  'Safe\\Exceptions\\ApacheException' => 
  array (
    'type' => 'class',
    'classname' => 'ApacheException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ApacheException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ApcuException' => 
  array (
    'type' => 'class',
    'classname' => 'ApcuException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ApcuException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ArrayException' => 
  array (
    'type' => 'class',
    'classname' => 'ArrayException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ArrayException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\Bzip2Exception' => 
  array (
    'type' => 'class',
    'classname' => 'Bzip2Exception',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\Bzip2Exception',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\CalendarException' => 
  array (
    'type' => 'class',
    'classname' => 'CalendarException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\CalendarException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ClassobjException' => 
  array (
    'type' => 'class',
    'classname' => 'ClassobjException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ClassobjException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ComException' => 
  array (
    'type' => 'class',
    'classname' => 'ComException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ComException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\CubridException' => 
  array (
    'type' => 'class',
    'classname' => 'CubridException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\CubridException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\DatetimeException' => 
  array (
    'type' => 'class',
    'classname' => 'DatetimeException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\DatetimeException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\DirException' => 
  array (
    'type' => 'class',
    'classname' => 'DirException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\DirException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\EioException' => 
  array (
    'type' => 'class',
    'classname' => 'EioException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\EioException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ErrorfuncException' => 
  array (
    'type' => 'class',
    'classname' => 'ErrorfuncException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ErrorfuncException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ExecException' => 
  array (
    'type' => 'class',
    'classname' => 'ExecException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ExecException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FileinfoException' => 
  array (
    'type' => 'class',
    'classname' => 'FileinfoException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FileinfoException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FilesystemException' => 
  array (
    'type' => 'class',
    'classname' => 'FilesystemException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FilesystemException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FilterException' => 
  array (
    'type' => 'class',
    'classname' => 'FilterException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FilterException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FpmException' => 
  array (
    'type' => 'class',
    'classname' => 'FpmException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FpmException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FtpException' => 
  array (
    'type' => 'class',
    'classname' => 'FtpException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FtpException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\FunchandException' => 
  array (
    'type' => 'class',
    'classname' => 'FunchandException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\FunchandException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\GettextException' => 
  array (
    'type' => 'class',
    'classname' => 'GettextException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\GettextException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\GmpException' => 
  array (
    'type' => 'class',
    'classname' => 'GmpException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\GmpException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\GnupgException' => 
  array (
    'type' => 'class',
    'classname' => 'GnupgException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\GnupgException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\HashException' => 
  array (
    'type' => 'class',
    'classname' => 'HashException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\HashException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\IbaseException' => 
  array (
    'type' => 'class',
    'classname' => 'IbaseException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\IbaseException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\IbmDb2Exception' => 
  array (
    'type' => 'class',
    'classname' => 'IbmDb2Exception',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\IbmDb2Exception',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\IconvException' => 
  array (
    'type' => 'class',
    'classname' => 'IconvException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\IconvException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ImageException' => 
  array (
    'type' => 'class',
    'classname' => 'ImageException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ImageException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ImapException' => 
  array (
    'type' => 'class',
    'classname' => 'ImapException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ImapException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\InfoException' => 
  array (
    'type' => 'class',
    'classname' => 'InfoException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\InfoException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\InotifyException' => 
  array (
    'type' => 'class',
    'classname' => 'InotifyException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\InotifyException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\LdapException' => 
  array (
    'type' => 'class',
    'classname' => 'LdapException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\LdapException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\LibxmlException' => 
  array (
    'type' => 'class',
    'classname' => 'LibxmlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\LibxmlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\LzfException' => 
  array (
    'type' => 'class',
    'classname' => 'LzfException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\LzfException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\MailparseException' => 
  array (
    'type' => 'class',
    'classname' => 'MailparseException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\MailparseException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\MbstringException' => 
  array (
    'type' => 'class',
    'classname' => 'MbstringException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\MbstringException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\MiscException' => 
  array (
    'type' => 'class',
    'classname' => 'MiscException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\MiscException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\MysqlException' => 
  array (
    'type' => 'class',
    'classname' => 'MysqlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\MysqlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\MysqliException' => 
  array (
    'type' => 'class',
    'classname' => 'MysqliException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\MysqliException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\NetworkException' => 
  array (
    'type' => 'class',
    'classname' => 'NetworkException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\NetworkException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\Oci8Exception' => 
  array (
    'type' => 'class',
    'classname' => 'Oci8Exception',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\Oci8Exception',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\OpcacheException' => 
  array (
    'type' => 'class',
    'classname' => 'OpcacheException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\OpcacheException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\OutcontrolException' => 
  array (
    'type' => 'class',
    'classname' => 'OutcontrolException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\OutcontrolException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PcntlException' => 
  array (
    'type' => 'class',
    'classname' => 'PcntlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PcntlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PgsqlException' => 
  array (
    'type' => 'class',
    'classname' => 'PgsqlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PgsqlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PosixException' => 
  array (
    'type' => 'class',
    'classname' => 'PosixException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PosixException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PsException' => 
  array (
    'type' => 'class',
    'classname' => 'PsException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PsException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PspellException' => 
  array (
    'type' => 'class',
    'classname' => 'PspellException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PspellException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ReadlineException' => 
  array (
    'type' => 'class',
    'classname' => 'ReadlineException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ReadlineException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\RnpException' => 
  array (
    'type' => 'class',
    'classname' => 'RnpException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\RnpException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\RpminfoException' => 
  array (
    'type' => 'class',
    'classname' => 'RpminfoException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\RpminfoException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\RrdException' => 
  array (
    'type' => 'class',
    'classname' => 'RrdException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\RrdException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SemException' => 
  array (
    'type' => 'class',
    'classname' => 'SemException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SemException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SessionException' => 
  array (
    'type' => 'class',
    'classname' => 'SessionException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SessionException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ShmopException' => 
  array (
    'type' => 'class',
    'classname' => 'ShmopException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ShmopException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SocketsException' => 
  array (
    'type' => 'class',
    'classname' => 'SocketsException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SocketsException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SodiumException' => 
  array (
    'type' => 'class',
    'classname' => 'SodiumException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SodiumException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SolrException' => 
  array (
    'type' => 'class',
    'classname' => 'SolrException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SolrException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SplException' => 
  array (
    'type' => 'class',
    'classname' => 'SplException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SplException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SqlsrvException' => 
  array (
    'type' => 'class',
    'classname' => 'SqlsrvException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SqlsrvException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SsdeepException' => 
  array (
    'type' => 'class',
    'classname' => 'SsdeepException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SsdeepException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\Ssh2Exception' => 
  array (
    'type' => 'class',
    'classname' => 'Ssh2Exception',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\Ssh2Exception',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\StreamException' => 
  array (
    'type' => 'class',
    'classname' => 'StreamException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\StreamException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\StringsException' => 
  array (
    'type' => 'class',
    'classname' => 'StringsException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\StringsException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SwooleException' => 
  array (
    'type' => 'class',
    'classname' => 'SwooleException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SwooleException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\UodbcException' => 
  array (
    'type' => 'class',
    'classname' => 'UodbcException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\UodbcException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\UopzException' => 
  array (
    'type' => 'class',
    'classname' => 'UopzException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\UopzException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\UrlException' => 
  array (
    'type' => 'class',
    'classname' => 'UrlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\UrlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\VarException' => 
  array (
    'type' => 'class',
    'classname' => 'VarException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\VarException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\XdiffException' => 
  array (
    'type' => 'class',
    'classname' => 'XdiffException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\XdiffException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\XmlException' => 
  array (
    'type' => 'class',
    'classname' => 'XmlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\XmlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\XmlrpcException' => 
  array (
    'type' => 'class',
    'classname' => 'XmlrpcException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\XmlrpcException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\YamlException' => 
  array (
    'type' => 'class',
    'classname' => 'YamlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\YamlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\YazException' => 
  array (
    'type' => 'class',
    'classname' => 'YazException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\YazException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ZipException' => 
  array (
    'type' => 'class',
    'classname' => 'ZipException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ZipException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\ZlibException' => 
  array (
    'type' => 'class',
    'classname' => 'ZlibException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\ZlibException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\DateTime' => 
  array (
    'type' => 'class',
    'classname' => 'DateTime',
    'isabstract' => false,
    'namespace' => 'Safe',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\DateTime',
    'implements' => 
    array (
    ),
  ),
  'Safe\\DateTimeImmutable' => 
  array (
    'type' => 'class',
    'classname' => 'DateTimeImmutable',
    'isabstract' => false,
    'namespace' => 'Safe',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\DateTimeImmutable',
    'implements' => 
    array (
    ),
  ),
  'Safe\\Exceptions\\CurlException' => 
  array (
    'type' => 'class',
    'classname' => 'CurlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\CurlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\JsonException' => 
  array (
    'type' => 'class',
    'classname' => 'JsonException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\JsonException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\OpensslException' => 
  array (
    'type' => 'class',
    'classname' => 'OpensslException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\OpensslException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\PcreException' => 
  array (
    'type' => 'class',
    'classname' => 'PcreException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\PcreException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Safe\\Exceptions\\SimplexmlException' => 
  array (
    'type' => 'class',
    'classname' => 'SimplexmlException',
    'isabstract' => false,
    'namespace' => 'Safe\\Exceptions',
    'extends' => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SimplexmlException',
    'implements' => 
    array (
      0 => 'Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
  'Sabre\\Xml\\Element' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Element',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element',
    ),
  ),
  'Sabberworm\\CSS\\Comment\\CommentContainer' => 
  array (
    'type' => 'trait',
    'traitname' => 'CommentContainer',
    'namespace' => 'Sabberworm\\CSS\\Comment',
    'use' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Comment\\CommentContainer',
    ),
  ),
  'Sabberworm\\CSS\\Position\\Position' => 
  array (
    'type' => 'trait',
    'traitname' => 'Position',
    'namespace' => 'Sabberworm\\CSS\\Position',
    'use' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Position\\Position',
    ),
  ),
  'Sabre\\Xml\\ContextStackTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'ContextStackTrait',
    'namespace' => 'Sabre\\Xml',
    'use' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\ContextStackTrait',
    ),
  ),
  'Dompdf\\Canvas' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Canvas',
    'namespace' => 'Dompdf',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Dompdf\\Canvas',
    ),
  ),
  'Svg\\Surface\\SurfaceInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'SurfaceInterface',
    'namespace' => 'Svg\\Surface',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Masterminds\\HTML5\\InstructionProcessor' => 
  array (
    'type' => 'interface',
    'interfacename' => 'InstructionProcessor',
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\InstructionProcessor',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\EventHandler' => 
  array (
    'type' => 'interface',
    'interfacename' => 'EventHandler',
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\EventHandler',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\InputStream' => 
  array (
    'type' => 'interface',
    'interfacename' => 'InputStream',
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\RulesInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'RulesInterface',
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\RulesInterface',
    ),
  ),
  'Sabberworm\\CSS\\CSSElement' => 
  array (
    'type' => 'interface',
    'interfacename' => 'CSSElement',
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSElement',
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\CSSListItem' => 
  array (
    'type' => 'interface',
    'interfacename' => 'CSSListItem',
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\CSSListItem',
    ),
  ),
  'Sabberworm\\CSS\\Comment\\Commentable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Commentable',
    'namespace' => 'Sabberworm\\CSS\\Comment',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Comment\\Commentable',
    ),
  ),
  'Sabberworm\\CSS\\Position\\Positionable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Positionable',
    'namespace' => 'Sabberworm\\CSS\\Position',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\AtRule' => 
  array (
    'type' => 'interface',
    'interfacename' => 'AtRule',
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\Renderable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Renderable',
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Renderable',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\RuleContainer' => 
  array (
    'type' => 'interface',
    'interfacename' => 'RuleContainer',
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\RuleContainer',
    ),
  ),
  'Sabre\\Xml\\XmlDeserializable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'XmlDeserializable',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\XmlDeserializable',
    ),
  ),
  'Sabre\\Xml\\XmlSerializable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'XmlSerializable',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\XmlSerializable',
    ),
  ),
  'Safe\\Exceptions\\SafeExceptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'SafeExceptionInterface',
    'namespace' => 'Safe\\Exceptions',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Safe\\Exceptions\\SafeExceptionInterface',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        /**
         * @param string $class
         */
        public function autoload($class): void
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile): void
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        /**
         * @param ClassAliasArray $class
         */
        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        /**
         * @param InterfaceAliasArray $interface
         */
        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }

        /**
         * @param TraitAliasArray $trait
         */
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
