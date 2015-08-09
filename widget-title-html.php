<?php
/*
 * Plugin Name: Widget Title HTML
 * Plugin URI:  http://themehybrid.com/board/topics/how-to-have-widgets-have-linkable-titles
 * Description: Allow limited, inline HTML in widget titles. The allowed HTML tags include <code>&lt;a></code>, <code>&lt;abbr></code>, <code>&lt;acronym></code>, <code>&lt;code></code>, <code>&lt;em></code>, <code>&lt;strong></code>, <code>&lt;i></code>, and <code>&lt;b></code>.
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
 * @copyright Copyright (c) 2015, Justin Tadlock
 * @link      http://themehybrid.com/board/topics/how-to-have-widgets-have-linkable-titles
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

# Remove WordPress' default filter that escapes all HTML on widget titles.
remove_filter( 'widget_title', 'esc_html' );

# Add custom widget title filter.
add_filter( 'widget_title', 'wt_html_widget_title' );

# Overwrite widget update callback so that we can sanitize the widget title ourself.
add_filter( 'widget_update_callback', 'wt_html_widget_update_callback', 95, 2 );

/**
 * Allowed HTML elements.  Used in `wp_kses()`.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function wt_html_allowed() {

	$allowed = array(
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

	return apply_filters( 'wt_html_allowed', $allowed );
}

/**
 * Sanitizes the widget title.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @return string
 */
function wt_html_sanitize_title( $title ) {
	return wp_kses( $title, wt_html_allowed() );
}

/**
 * Runs <code>wp_kses()</code> over widget title output, allowing only links.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $title
 * @return string
 */
function wt_html_widget_title( $title ) {
	return wt_html_sanitize_title( $title );
}

/**
 * Runs <code>wp_kses()</code> over widget title update/save, allowing only links.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $instance      Widget instance after being run through `WP_Widget::update()`.
 * @param  array  $new_instance  Original, unsanitized instance.
 * @return array
 */
function wt_html_widget_update_callback( $instance, $new_instance ) {

	// Make sure there's a title for this widget.
	if ( isset( $new_instance['title'] ) )
		$instance['title'] = wt_html_sanitize_title( $new_instance['title'] );

	return $instance;
}
