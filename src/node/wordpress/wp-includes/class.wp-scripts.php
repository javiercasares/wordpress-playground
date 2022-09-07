<?php
 class WP_Scripts extends WP_Dependencies { public $base_url; public $content_url; public $default_version; public $in_footer = array(); public $concat = ''; public $concat_version = ''; public $do_concat = false; public $print_html = ''; public $print_code = ''; public $ext_handles = ''; public $ext_version = ''; public $default_dirs; private $type_attr = ''; public function __construct() { $this->init(); add_action( 'init', array( $this, 'init' ), 0 ); } public function init() { if ( function_exists( 'is_admin' ) && ! is_admin() && function_exists( 'current_theme_supports' ) && ! current_theme_supports( 'html5', 'script' ) ) { $this->type_attr = " type='text/javascript'"; } do_action_ref_array( 'wp_default_scripts', array( &$this ) ); } public function print_scripts( $handles = false, $group = false ) { return $this->do_items( $handles, $group ); } public function print_scripts_l10n( $handle, $display = true ) { _deprecated_function( __FUNCTION__, '3.3.0', 'WP_Scripts::print_extra_script()' ); return $this->print_extra_script( $handle, $display ); } public function print_extra_script( $handle, $display = true ) { $output = $this->get_data( $handle, 'data' ); if ( ! $output ) { return; } if ( ! $display ) { return $output; } printf( "<script%s id='%s-js-extra'>\n", $this->type_attr, esc_attr( $handle ) ); if ( $this->type_attr ) { echo "/* <![CDATA[ */\n"; } echo "$output\n"; if ( $this->type_attr ) { echo "/* ]]> */\n"; } echo "</script>\n"; return true; } public function do_item( $handle, $group = false ) { if ( ! parent::do_item( $handle ) ) { return false; } if ( 0 === $group && $this->groups[ $handle ] > 0 ) { $this->in_footer[] = $handle; return false; } if ( false === $group && in_array( $handle, $this->in_footer, true ) ) { $this->in_footer = array_diff( $this->in_footer, (array) $handle ); } $obj = $this->registered[ $handle ]; if ( null === $obj->ver ) { $ver = ''; } else { $ver = $obj->ver ? $obj->ver : $this->default_version; } if ( isset( $this->args[ $handle ] ) ) { $ver = $ver ? $ver . '&amp;' . $this->args[ $handle ] : $this->args[ $handle ]; } $src = $obj->src; $cond_before = ''; $cond_after = ''; $conditional = isset( $obj->extra['conditional'] ) ? $obj->extra['conditional'] : ''; if ( $conditional ) { $cond_before = "<!--[if {$conditional}]>\n"; $cond_after = "<![endif]-->\n"; } $before_handle = $this->print_inline_script( $handle, 'before', false ); $after_handle = $this->print_inline_script( $handle, 'after', false ); if ( $before_handle ) { $before_handle = sprintf( "<script%s id='%s-js-before'>\n%s\n</script>\n", $this->type_attr, esc_attr( $handle ), $before_handle ); } if ( $after_handle ) { $after_handle = sprintf( "<script%s id='%s-js-after'>\n%s\n</script>\n", $this->type_attr, esc_attr( $handle ), $after_handle ); } if ( $before_handle || $after_handle ) { $inline_script_tag = $cond_before . $before_handle . $after_handle . $cond_after; } else { $inline_script_tag = ''; } $translations_stop_concat = ! empty( $obj->textdomain ); $translations = $this->print_translations( $handle, false ); if ( $translations ) { $translations = sprintf( "<script%s id='%s-js-translations'>\n%s\n</script>\n", $this->type_attr, esc_attr( $handle ), $translations ); } if ( $this->do_concat ) { $srce = apply_filters( 'script_loader_src', $src, $handle ); if ( $this->in_default_dir( $srce ) && ( $before_handle || $after_handle || $translations_stop_concat ) ) { $this->do_concat = false; _print_scripts(); $this->reset(); } elseif ( $this->in_default_dir( $srce ) && ! $conditional ) { $this->print_code .= $this->print_extra_script( $handle, false ); $this->concat .= "$handle,"; $this->concat_version .= "$handle$ver"; return true; } else { $this->ext_handles .= "$handle,"; $this->ext_version .= "$handle$ver"; } } $has_conditional_data = $conditional && $this->get_data( $handle, 'data' ); if ( $has_conditional_data ) { echo $cond_before; } $this->print_extra_script( $handle ); if ( $has_conditional_data ) { echo $cond_after; } if ( ! $src ) { if ( $inline_script_tag ) { if ( $this->do_concat ) { $this->print_html .= $inline_script_tag; } else { echo $inline_script_tag; } } return true; } if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $this->content_url && 0 === strpos( $src, $this->content_url ) ) ) { $src = $this->base_url . $src; } if ( ! empty( $ver ) ) { $src = add_query_arg( 'ver', $ver, $src ); } $src = esc_url( apply_filters( 'script_loader_src', $src, $handle ) ); if ( ! $src ) { return true; } $tag = $translations . $cond_before . $before_handle; $tag .= sprintf( "<script%s src='%s' id='%s-js'></script>\n", $this->type_attr, $src, esc_attr( $handle ) ); $tag .= $after_handle . $cond_after; $tag = apply_filters( 'script_loader_tag', $tag, $handle, $src ); if ( $this->do_concat ) { $this->print_html .= $tag; } else { echo $tag; } return true; } public function add_inline_script( $handle, $data, $position = 'after' ) { if ( ! $data ) { return false; } if ( 'after' !== $position ) { $position = 'before'; } $script = (array) $this->get_data( $handle, $position ); $script[] = $data; return $this->add_data( $handle, $position, $script ); } public function print_inline_script( $handle, $position = 'after', $display = true ) { $output = $this->get_data( $handle, $position ); if ( empty( $output ) ) { return false; } $output = trim( implode( "\n", $output ), "\n" ); if ( $display ) { printf( "<script%s id='%s-js-%s'>\n%s\n</script>\n", $this->type_attr, esc_attr( $handle ), esc_attr( $position ), $output ); } return $output; } public function localize( $handle, $object_name, $l10n ) { if ( 'jquery' === $handle ) { $handle = 'jquery-core'; } if ( is_array( $l10n ) && isset( $l10n['l10n_print_after'] ) ) { $after = $l10n['l10n_print_after']; unset( $l10n['l10n_print_after'] ); } if ( ! is_array( $l10n ) ) { _doing_it_wrong( __METHOD__, sprintf( __( 'The %1$s parameter must be an array. To pass arbitrary data to scripts, use the %2$s function instead.' ), '<code>$l10n</code>', '<code>wp_add_inline_script()</code>' ), '5.7.0' ); } if ( is_string( $l10n ) ) { $l10n = html_entity_decode( $l10n, ENT_QUOTES, 'UTF-8' ); } else { foreach ( (array) $l10n as $key => $value ) { if ( ! is_scalar( $value ) ) { continue; } $l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' ); } } $script = "var $object_name = " . wp_json_encode( $l10n ) . ';'; if ( ! empty( $after ) ) { $script .= "\n$after;"; } $data = $this->get_data( $handle, 'data' ); if ( ! empty( $data ) ) { $script = "$data\n$script"; } return $this->add_data( $handle, 'data', $script ); } public function set_group( $handle, $recursion, $group = false ) { if ( isset( $this->registered[ $handle ]->args ) && 1 === $this->registered[ $handle ]->args ) { $grp = 1; } else { $grp = (int) $this->get_data( $handle, 'group' ); } if ( false !== $group && $grp > $group ) { $grp = $group; } return parent::set_group( $handle, $recursion, $grp ); } public function set_translations( $handle, $domain = 'default', $path = null ) { if ( ! isset( $this->registered[ $handle ] ) ) { return false; } $obj = $this->registered[ $handle ]; if ( ! in_array( 'wp-i18n', $obj->deps, true ) ) { $obj->deps[] = 'wp-i18n'; } return $obj->set_translations( $domain, $path ); } public function print_translations( $handle, $display = true ) { if ( ! isset( $this->registered[ $handle ] ) || empty( $this->registered[ $handle ]->textdomain ) ) { return false; } $domain = $this->registered[ $handle ]->textdomain; $path = $this->registered[ $handle ]->translations_path; $json_translations = load_script_textdomain( $handle, $domain, $path ); if ( ! $json_translations ) { return false; } $output = <<<JS
( function( domain, translations ) {
	var localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
	localeData[""].domain = domain;
	wp.i18n.setLocaleData( localeData, domain );
} )( "{$domain}", {$json_translations} );
JS;
if ( $display ) { printf( "<script%s id='%s-js-translations'>\n%s\n</script>\n", $this->type_attr, esc_attr( $handle ), $output ); } return $output; } public function all_deps( $handles, $recursion = false, $group = false ) { $r = parent::all_deps( $handles, $recursion, $group ); if ( ! $recursion ) { $this->to_do = apply_filters( 'print_scripts_array', $this->to_do ); } return $r; } public function do_head_items() { $this->do_items( false, 0 ); return $this->done; } public function do_footer_items() { $this->do_items( false, 1 ); return $this->done; } public function in_default_dir( $src ) { if ( ! $this->default_dirs ) { return true; } if ( 0 === strpos( $src, '/' . WPINC . '/js/l10n' ) ) { return false; } foreach ( (array) $this->default_dirs as $test ) { if ( 0 === strpos( $src, $test ) ) { return true; } } return false; } public function reset() { $this->do_concat = false; $this->print_code = ''; $this->concat = ''; $this->concat_version = ''; $this->print_html = ''; $this->ext_version = ''; $this->ext_handles = ''; } } 