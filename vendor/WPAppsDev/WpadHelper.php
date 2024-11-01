<?php

namespace WPAppsDev;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPAppsDev helper Class
 *
 * @author  Saiful Islam Ananda
 * @link    http://siananda.me/
 * @version 1.0.0
 */
abstract class WpadHelper {
	/**
	 * @param string $action Name of the action.
	 * @param string $function Function to hook that will run on action.
	 * @param integet $priority Order in which to execute the function, relation to other functions hooked to this action.
	 * @param integer $accepted_args The number of arguments the function accepts.
	 */
	public static function add_action( $action, $function, $priority = 10, $accepted_args = 1 ) {
		// Pass variables into WordPress add_action function
		add_action( $action, $function, $priority, $accepted_args );
	}

	/**
	 * @param  string  $action           Name of the action to hook to, e.g 'init'.
	 * @param  string  $function         Function to hook that will run on @action.
	 * @param  int     $priority         Order in which to execute the function, relation to other function hooked to this action.
	 * @param  int     $accepted_args    The number of arguements the function accepts.
	 */
	public static function add_filter( $action, $function, $priority = 10, $accepted_args = 1 ) {
		// Pass variables into Wordpress add_action function
		add_filter( $action, $function, $priority, $accepted_args );
	}

	/**
	 * Helper method
	 * That passed a function to the 'init' WP action
	 * @param function $cb Passed callback function.
	 */
	public static function init( $cb ) {
		add_action( 'init', $cb );
	}

	/**
	 * Helper method
	 * That passed a function to the 'admin_init' WP action
	 * @param function $cb Passed callback function.
	 */
	public static function admin_init( $cb ) {
		add_action( 'admin_init', $cb );
	}

	/**
	 * Human friendly a string.
	 *
	 * Returns the human friendly name.
	 *
	 *    ucwords      capitalize words
	 *    strtolower   makes string lowercase before capitalizing
	 *    str_replace  replace all instances of hyphens and underscores to spaces
	 *
	 * @param string $string The name you want to make friendly.
	 * @return string The human friendly name.
	 */
	public static function beautify( $string ) {
		// Return human friendly name.
		$search  = [ 'tj-', 'tj_', '-', '_' ];
		$replace = [ '', '', ' ', ' ' ];

		return apply_filters( 'wpappsdev_beautify', ucwords( strtolower( str_replace( $search, $replace, $string ) ) ) );
	}

	/**
	 * Uglifies a string.
	 *
	 * Returns an url friendly slug.
	 *
	 *    strtolower   makes string lowercase.
	 *    str_replace  replace all instances of spaces and underscores to hyphens.
	 *
	 * @param  string $string Name to slugify.
	 * @return string Returns the slug.
	 */
	public static function uglify( $string ) {
		// Return an url friendly slug.
		return apply_filters( 'wpappsdev_uglify', str_replace( '_', '-', str_replace( ' ', '-', strtolower( $string ) ) ) );
	}

	/**
	 * Makes a word plural
	 *
	 * @param 	string 			$string
	 * @return 	string
	 */
	public static function pluralize( $string ) {
		$plural = [
			[ '/(quiz)$/i', '$1zes'   ],
			[ '/^(ox)$/i', '$1en'    ],
			[ '/([m|l])ouse$/i', '$1ice'   ],
			[ '/(matr|vert|ind)ix|ex$/i', '$1ices'  ],
			[ '/(x|ch|ss|sh)$/i', '$1es'    ],
			[ '/([^aeiouy]|qu)y$/i', '$1ies'   ],
			[ '/([^aeiouy]|qu)ies$/i', '$1y'     ],
			[ '/(hive)$/i', '$1s'     ],
			[ '/(?:([^f])fe|([lr])f)$/i', '$1$2ves' ],
			[ '/sis$/i', 'ses'     ],
			[ '/([ti])um$/i', '$1a'     ],
			[ '/(buffal|tomat)o$/i', '$1oes'   ],
			[ '/(bu)s$/i', '$1ses'   ],
			[ '/(alias|status)$/i', '$1es'    ],
			[ '/(octop|vir)us$/i', '$1i'     ],
			[ '/(ax|test)is$/i', '$1es'    ],
			[ '/s$/i', 's'       ],
			[ '/$/', 's'       ],
		];

		$irregular = [
			[ 'move', 'moves'    ],
			[ 'sex', 'sexes'    ],
			[ 'child', 'children' ],
			[ 'man', 'men'      ],
			[ 'person', 'people'   ],
		];

		$uncountable = [
			'sheep',
			'fish',
			'series',
			'species',
			'money',
			'rice',
			'information',
			'equipment',
		];

		// Save time if string in uncountable
		if ( in_array( strtolower( $string ), $uncountable ) ) {
			return apply_filters( 'wpappsdev_pluralize', $string );
		}

		// Check for irregular words
		foreach ( $irregular as $noun ) {
			if ( strtolower( $string ) == $noun[0] ) {
				return apply_filters( 'wpappsdev_pluralize', $noun[1] );
			}
		}

		// Check for plural forms
		foreach ( $plural as $pattern ) {
			if ( preg_match( $pattern[0], $string ) ) {
				return apply_filters( 'wpappsdev_pluralize', preg_replace( $pattern[0], $pattern[1], $string ) );
			}
		}

		// Return if noting found
		return apply_filters( 'wpappsdev_pluralize', $string );
	}
}
