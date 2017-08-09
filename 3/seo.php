<?php

function showcaseidx_setup_seo( $metadata ) {
  // Don't bother if we don't have metadata
  if ( !$metadata ) return;

  add_filter('wpseo_canonical', '__return_false');

  // Page Title
  $title_filter = function ( $title, $sep = "-" ) use ( $metadata ) {
    return $metadata->title ? "{$metadata->title} {$sep} {$title}" : $title;
  };

  add_filter( 'wp_title', $title_filter, 10, 2 );

  // Meta Tags
  add_action( 'wp_head', function () use ( $metadata ) {
    echo $metadata->meta;
  }, 1);

  // Jetpack
  add_filter( 'jetpack_enable_open_graph', '__return_false' );

  // Yoast
  add_filter( 'wpseo_title', $title_filter );

  add_filter( 'wpseo_metadesc',      '__return_false' );
  add_filter( 'wpseo_metakey',       '__return_false' );
  add_filter( 'wpseo_prev_rel_link', '__return_false' );
  add_filter( 'wpseo_next_rel_link', '__return_false' );

  add_filter( 'wpseo_opengraph_title',     '__return_false' );
  add_filter( 'wpseo_opengraph_type',      '__return_false' );
  add_filter( 'wpseo_opengraph_site_name', '__return_false' );
  add_filter( 'wpseo_opengraph_desc',      '__return_false' );
  add_filter( 'wpseo_opengraph_image',     '__return_false' );

  add_filter( 'wpseo_twitter_metatag_key', function() { return 'disabled'; } );

  add_filter( 'wpseo_twitter_card_type',   '__return_false' );
  add_filter( 'wpseo_twitter_title',       '__return_false' );
  add_filter( 'wpseo_twitter_description', '__return_false' );
  add_filter( 'wpseo_twitter_image',       '__return_false' );
}
