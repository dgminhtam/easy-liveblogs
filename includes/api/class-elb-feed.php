<?php

namespace EasyLiveblogs\API;

class Feed {
	/**
	 * Setup.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/**
	 * Register route.
	 *
	 * @return void
	 */
	public function register() {
		register_rest_route(
			'easy-liveblogs/v1',
			'/liveblog/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'feed' ),
			)
		);

		register_rest_route(
			'easy-liveblogs/v1',
			'/liveblog/(?P<id>\d+)/check',
			array(
				'methods'  => 'GET',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'check' ),
			)
		);
	}

	/**
	 * Construct the feed.
	 *
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function feed( \WP_REST_Request $request ) {
		if ( $feed = apply_filters( 'elb_feed_from_cache', false, $request->get_param( 'id' ) ) ) {
			return $feed;
		}

		$feed = FeedFactory::make( $request->get_param( 'id' ) );

		do_action( 'elb_cache_feed', $request->get_param( 'id' ), $feed );

		return $feed;
	}

	/**
	 * Check for updates.
	 *
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function check( \WP_REST_Request $request ) {
		$liveblog_id = $request->get_param( 'id' );

		$args = array(
			'post_type'      => 'elb_entry',
			'posts_per_page' => 1,
			'meta_key'       => '_elb_liveblog',
			'meta_value'     => $liveblog_id,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
			'no_found_rows'  => true, // Optimization: Don't count all rows
		);

		$latest_posts = get_posts( $args );
		$timestamp    = 0;

		if ( ! empty( $latest_posts ) ) {
			$timestamp = get_post_time( 'U', false, $latest_posts[0] );
		} else {
			// If no entries, fallback to liveblog modified time
			$timestamp = get_post_modified_time( 'U', false, $liveblog_id );
		}
		
		return array(
			'timestamp' => $timestamp,
			'status'    => get_post_meta( $liveblog_id, '_elb_status', true ),
		);
	}
}
