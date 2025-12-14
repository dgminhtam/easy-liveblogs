<?php

namespace EasyLiveblogs\API;

class Entry {
	public $id;
	public $title;
	public $content;
	public $time;
	public $datetime;

	public function __construct() {
	}

	public static function fromPost( $_post ) {
		global $post;

		$post = new \WP_Post( (object) $_post );

		setup_postdata($post);

		$instance           = new self();
		$instance->id       = $post->ID;
		$instance->title    = $post->post_title;
		$instance->content  = apply_filters( 'the_content', $post->post_content );
		$instance->time     = get_the_date( 'H:i', $post );
		$instance->datetime = get_the_date( 'c', $post );
		$instance->date     = get_the_date( 'Y-m-d', $post );
		$instance->modified = get_the_modified_date( 'c' );
		$instance->author   = get_the_author();
		$instance->permalink = elb_get_entry_url( $post );
		$instance->timestamp = get_post_time( 'U' );

		wp_reset_postdata();

		return apply_filters( 'elb_api_entry', $instance );
	}
}
