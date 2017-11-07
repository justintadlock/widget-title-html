<?php
/*
 * Plugin Name: Widget Title HTML
 * Plugin URI:  http://themehybrid.com/board/topics/how-to-have-widgets-have-linkable-titles
 * Description: Allows a limited subset of HTML within widget titles such as <code>&lt;a></code>, <code>&lt;abbr></code>, <code>&lt;acronym></code>, <code>&lt;code></code>, <code>&lt;em></code>, <code>&lt;strong></code>, <code>&lt;i></code>, and <code>&lt;b></code>.
 * Version:     1.0.0-alpha-1
 * Author:      Justin Tadlock
 * Author URI:  http://justintadlock.com
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package   WidgetTitleHTML
 * @version   1.0.0
 * @author    Justin Tadlock <justin@justintadlock.com>
 * @copyright Copyright (c) 2017, Justin Tadlock
 * @link      http://themehybrid.com/board/topics/how-to-have-widgets-have-linkable-titles
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Widget_Title_HTML;

/**
 * Singleton class for setting up the plugin.
 *
 * @since  1.0.0
 * @access public
 */
final class Plugin {

	/**
	 * Allowed HTML elements.  Used in `wp_kses()`.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array
	 */
	private $allowed_html = array(
		'a'       => array( 'class' => true, 'title' => true, 'href' => true ),
		'abbr'    => array( 'class' => true, 'title' => true ),
		'acronym' => array( 'class' => true, 'title' => true ),
		'code'    => array( 'class' => true ),
		'em'      => array( 'class' => true ),
		'strong'  => array( 'class' => true ),
		'i'       => array( 'class' => true ),
		'b'       => array( 'class' => true ),
		'span'    => array( 'class' => true )
	);

	/**
	 * Widget ID bases to not allow HTML on.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array
	 */
	private $exclude_widgets = array( 'rss' );

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Magic method to output a string if trying to use the object as a string.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __toString() {
		return __CLASS__;
	}

	/**
	 * Sets up main plugin actions and filters.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Internationalize the text strings used.
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		// Remove update notifications for this plugin.
		add_filter( 'site_transient_update_plugins', array( $this, 'update_notifications' )        );
		add_filter( 'http_request_args',             array( $this, 'http_request_args'    ), 10, 2 );

		// Remove WordPress' default filter that escapes all HTML on widget titles.
		remove_filter( 'widget_title', 'esc_html' );

		// Add custom widget title filter.
		add_filter( 'widget_title', array( $this, 'widget_title' ), 10, 3 );

		// Overwrite widget update callback so that we can sanitize the widget title ourself.
		add_filter( 'widget_update_callback', array( $this, 'widget_update_callback' ), 95, 4 );
	}

	/**
	 * Runs `wp_kses()` over widget title output, allowing only links.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $title
	 * @param  array   $instance
	 * @param  mixed   $id_base
	 * @return string
	 */
	public function widget_title( $title = '', $instance = array(), $id_base = '' ) {

		// If this is an excluded widget, use `esc_html()`, which is what WP uses by default.
		if ( $this->is_excluded_widget( $id_base ) )
			return esc_html( $title );

		return $this->sanitize_title( $title );
	}

	/**
	 * Runs `wp_kses()` over widget title update/save, only allowing approved HTML.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $instance      Widget instance after being run through `WP_Widget::update()`.
	 * @param  array  $new_instance  Original, unsanitized instance.
	 * @param  array  $old_instance  Previous instance of the widget.
	 * @param  object $wp_widget     `WP_Widget` object.
	 * @return array
	 */
	public function widget_update_callback( $instance, $new_instance, $old_instance, $wp_widget ) {

		// Bail out if widget should be ignored.
		if ( $this->is_excluded_widget( $wp_widget->id_base ) )
			return $instance;

		// Make sure there's a title for this widget.
		if ( isset( $new_instance['title'] ) )
			$instance['title'] = $this->sanitize_title( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Sanitizes the widget title.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $title
	 * @return string
	 */
	public function sanitize_title( $title ) {

		return wp_kses( $title, $this->allowed_html );
	}

	/**
	 * Returns an array of allowed HTML and attributes to be passed to `wp_kses()`.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_allowed_html() {

		return apply_filters( 'widget_title_html/allowed_html', $this->allowed_html );
	}

	/**
	 * Conditional check to see if a widget should not allow HTML for whatever reason.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return bool
	 */
	public function is_excluded_widget( $id_base ) {

		return in_array(
			$id_base,
			apply_filters( 'widget_title_html/exclude_widgets', $this->exclude_widgets )
		);
	}

	/**
	 * Loads the translation files.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function i18n() {

		load_plugin_textdomain( 'widget-title-html', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'widget-title-html' );
	}

	/**
	 * Overwrites the plugin update notifications to remove this plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $notifications
	 * @return array
	 */
	public function update_notifications( $notifications ) {

		$basename = plugin_basename( __FILE__ );

		if ( isset( $notifications->response[ $basename ] ) )
			unset( $notifications->response[ $basename ] );

		return $notifications;
	}

	/**
	 * Blocks plugin from getting updated via WordPress.org if there's one with the same
	 * name hosted there.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $request_args
	 * @param  array  $url
	 * @return array
	 */
	public function http_request_args( $request_args, $url ) {

		if ( 0 === strpos( $url, 'https://api.wordpress.org/plugins/update-check' ) ) {

			$basename = plugin_basename( __FILE__ );
			$plugins  = json_decode( $request_args['body']['plugins'], true );

			if ( isset( $plugins['plugins'][ $basename ] ) ) {
				unset( $plugins['plugins'][ $basename ] );

				$request_args['body']['plugins'] = json_encode( $plugins );
			}
		}

		return $request_args;
	}
}

Plugin::get_instance();
