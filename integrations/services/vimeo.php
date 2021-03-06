<?php
defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );

add_filter( 'cmplz_known_iframe_tags', 'cmplz_vimeo_iframetags' );
function cmplz_vimeo_iframetags( $tags ) {
	$tags[] = 'player.vimeo.com';
	$tags[] = 'i.vimeocdn.com';
	return $tags;
}

add_filter( 'cmplz_iframe_tags_not_including', 'cmplz_vimeo_not_including_iframetags' );
function cmplz_vimeo_not_including_iframetags( $tags ) {
	$tags[] = 'dnt=1';
	$tags[] = 'dnt=true';
	return $tags;
}

function cmplz_vimeo_placeholder( $placeholder_src, $src ) {
	$vimeo_pattern
		= '/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i';

	if ( preg_match( $vimeo_pattern, $src, $matches ) ) {
		$vimeo_id = $matches[1];
		$new_src  = get_transient( "cmplz_vimeo_image_$vimeo_id" );
		if ( ! $new_src || ! file_exists( $new_src ) ) {
			$xml
				= @file_get_contents( "https://vimeo.com/api/v2/video/$vimeo_id.xml" );
			if ( ! empty( $xml ) ) {//-> maybe a not public video
				$vimeo_images = simplexml_load_string( $xml );
				$placeholder_src = $vimeo_images->video->thumbnail_large;
				$placeholder_src = cmplz_download_to_site( $placeholder_src,
					'vimeo' . $vimeo_id );
			}
			set_transient( "cmplz_vimeo_image_$vimeo_id", $placeholder_src,
				WEEK_IN_SECONDS );
		} else {
			$placeholder_src = $new_src;
		}
	}

	return $placeholder_src;
}

add_filter( 'cmplz_placeholder_vimeo', 'cmplz_vimeo_placeholder', 10, 2 );
