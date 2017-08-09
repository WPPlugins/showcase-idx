<?php

function showcase_render_search_page( WP $wp, $widget_url_path, $widget_url_query ) {
  $response = showcase_retrieve_app( $widget_url_path, $widget_url_query );

  $existing_page = get_page_by_path( get_option( 'showcaseidx_search_page' ) );
  $widget = (object) array( 'metaData' => NULL );

  if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
    header( 'Set-Cookie: ' . wp_remote_retrieve_header( $response, 'set-cookie' ) );

    $widget = json_decode( wp_remote_retrieve_body( $response ) );

    if ( $existing_page ) {
      $page = $existing_page;

      $GLOBALS['post_content'] = $widget->widget;

      if ( !preg_match( '/\[showcaseidx\]/', $existing_page->post_content, $matches ) ) {
        $page->post_content = $page->post_content . $widget->widget;
      }
    } else {
      $page = showcaseidx_create_page( $widget->metaData->title, $widget->widget );
    }
  } else {
    $page = showcaseidx_create_page( 'Error', 'Error communicating with Showcase IDX' );
  }

  add_filter( 'posts_pre_query', function ( $posts, WP_Query $query ) use ( $page ) {
    if ( $query->is_main_query() ) return showcaseidx_setup_query( $query, $page );
  }, 10, 2 );

  add_shortcode( 'showcaseidx', function() { return $GLOBALS['post_content']; });

  showcaseidx_setup_seo( $widget->metaData );
  showcaseidx_apply_workarounds();
}

function showcase_retrieve_app( $path, $query ) {
  $cookies = array();
  foreach ( $_COOKIE as $name => $value ) {
    $cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
  }

  parse_str( $query, $query_vars );

  $query_vars['website_uuid'] = get_option( 'showcaseidx_website_uuid' );

  return wp_remote_post(
    SHOWCASEIDX_SEARCH_HOST . '/app/render' . $path . '?' . http_build_query( $query_vars ),
    array(
      'timeout' => 10,
      'httpversion' => '1.1',
      'cookies' => $cookies,
      'body' => array_map( 'stripslashes', $_POST )
    )
  );
}

function showcaseidx_create_page( $title, $content ) {
  $post = array(
    'ID'             => PHP_INT_MAX,
    'post_title'     => $title,
    'post_name'      => sanitize_title( $title ),
    'post_content'   => $content,
    'post_excerpt'   => '',
    'post_parent'    => 0,
    'menu_order'     => 0,
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'comment_status' => 'closed',
    'ping_status'    => 'closed',
    'comment_count'  => 0,
    'post_password'  => '',
    'to_ping'        => '',
    'pinged'         => '',
    'guid'           => get_home_url() . '/' . get_option( 'showcaseidx_search_page' ) . '/',
    'post_date'      => current_time( 'mysql' ),
    'post_date_gmt'  => current_time( 'mysql', 1 ),
    'post_author'    => is_user_logged_in() ? get_current_user_id() : 0,
    'filter'         => 'raw',
    'ancestors'      => array(),
    'is_virtual'     => TRUE
  );

  return new WP_Post( (object) $post );
}

function showcaseidx_setup_query( $query, $page ) {
  $query->is_page       = TRUE;
  $query->is_singular   = TRUE;
  $query->is_single     = FALSE;
  $query->is_search     = FALSE;
  $query->is_404        = FALSE;
  $query->is_home       = FALSE;
  $query->found_posts   = 1;
  $query->post_count    = 1;
  $query->max_num_pages = 1;

  $posts = array( $page );
  $post = $page;

  $GLOBALS['post'] = $post;

  $query->posts          = $posts;
  $query->post           = $post;
  $query->queried_object = $post;
  $query->virtual_page   = $post;

  $query->queried_object_id = $post->ID;

  return $posts;
}
