<?php

add_shortcode( 'showcaseidx_signin',   showcaseidx_build_shortcode( 'authform' ) );
add_shortcode( 'showcaseidx_cma',      showcaseidx_build_shortcode( 'cmaform' ) );
add_shortcode( 'showcaseidx_contact',  showcaseidx_build_shortcode( 'contactform' ) );
add_shortcode( 'showcaseidx_hotsheet', showcaseidx_build_shortcode( 'hotsheet',   array( 'name' => '',
                                                                                         'hide_map' => '',
                                                                                         'hide' => '') ) );
add_shortcode( 'showcaseidx_search',   showcaseidx_build_shortcode( 'searchform', array( 'show' => '',
                                                                                         'hide' => '',
                                                                                         'background' => '',
                                                                                         'radius' => '',
                                                                                         'padding' => '',
                                                                                         'margin' => '' ) ) );
add_shortcode( 'showcaseidx_map',      showcaseidx_build_shortcode( 'searchmap',  array( 'show' => '',
                                                                                         'hide' => '',
                                                                                         'height' => '',
                                                                                         'search_template_id' => '' ) ) );

function showcaseidx_build_shortcode( $type, $allowed = array() ) {
  return function ( $attrs ) use ( $type, $allowed ) {
    $attrs = shortcode_atts( $allowed, $attrs, 'showcaseidx_' . $type );

    $response = showcase_retrieve_widget( $type, $attrs );

    if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
      $widget = json_decode( wp_remote_retrieve_body( $response ) );

      return $widget->widget;
    } else {
      return '';
    }
  };
}

function showcaseidx_build_query( $query ) {

  if ( $query["hide_map"] ) {
    if ( $query["hide"] ) {
      $query["hide"] = $query["hide"] . ",map";
    } else {
      $query["hide"] = "map";
    }
  }
  unset( $query["hide_map"] );

  $split_arrays = function( &$val, $key ) {
    if ( strpos( $val, ',' ) !== false ) {
      $val = explode( ',', $val );
    }
  };

  array_walk( $query, $split_arrays );

  return http_build_query( $query );
}

function showcase_retrieve_widget( $widget, $attrs ) {
  $cookies = array();
  foreach ( $_COOKIE as $name => $value ) {
    $cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
  }

  $query = $attrs;
  $query['website_uuid'] = get_option( 'showcaseidx_website_uuid' );

  return wp_remote_post(
    SHOWCASEIDX_SEARCH_HOST . '/app/renderWidget/' . $widget . '?' . urldecode( showcaseidx_build_query( $query ) ),
    array(
      'timeout' => 10,
      'httpversion' => '1.1',
      'cookies' => $cookies
    )
  );
}
