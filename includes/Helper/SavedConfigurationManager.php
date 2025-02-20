<?php

namespace WPAppsDev\PCBU\Helper;

use WP_Error;

/**
 * The donor manager helper class.
 */
class SavedConfigurationManager {
	/**
	 * Create saved pc item.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function create( $args ) {
		global $wpdb;

		$defaults = [
			'user_id'        => '',
			'configurations' => [],
			'share_key'      => '',
			'created_at'     => current_time( 'mysql' ),
		];

		$data = wp_parse_args( $args, $defaults );

		if ( is_array( $data['configurations'] ) ) {
			$data['configurations'] = json_encode( $data['configurations'] );
		}

		$inserted = $wpdb->insert(
			$wpdb->wpadpcbu_saved_pc,
			$data,
			[
				'%d',
				'%s',
				'%s',
				'%s',
			]
		);

		if ( ! $inserted ) {
			return new WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'wpappsdev-donation-manager' ) );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update saved configuration data.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function update( $args ) {
		global $wpdb;

		if ( ! isset( $args['id'] ) ) {
			return new WP_Error( 'failed-to-update', __( 'Configuration ID missing', 'wpappsdev-donation-manager' ) );
		}

		$id = $args['id'];

		unset( $args['id'] );

		$updated = $wpdb->update(
			$wpdb->wpadpcbu_saved_pc,
			$args,
			[
				'id' => $id,
			]
		);

		if ( ! $updated ) {
			return new WP_Error( 'failed-to-update', __( 'Failed to update data', 'wpappsdev-donation-manager' ) );
		}

		return $updated;
	}

	/**
	 * Delete saved pc item.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete( $id ) {
		global $wpdb;

		return $wpdb->delete(
			$wpdb->wpadpcbu_saved_pc,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Get user saved pcs.
	 *
	 * @param int $user_id
	 * @param int $offset
	 * @param int $per_page
	 *
	 * @return array
	 */
	public function user_pcs( $user_id, $offset, $per_page ) {
		global $wpdb;
		$table = $wpdb->wpadpcbu_saved_pc;
		$sql   = "SELECT * FROM {$table} WHERE user_id = %d LIMIT %d, %d";
		$data  = $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $offset, $per_page ) );

		return $data;
	}

	/**
	 * Get saved pc item.
	 *
	 * @param int    $id
	 * @param string $column
	 *
	 * @return void
	 */
	public static function single_pc( $id, $column = '' ) {
		global $wpdb;
		$table = $wpdb->wpadpcbu_saved_pc;
		$sql   = "SELECT * FROM {$table} WHERE id = %d";
		$data  = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

		if ( '' == $column ) {
			return $data;
		}

		return $data;
	}

	/**
	 * Count total saved pc items.
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function count( $user_id = 0 ) {
		global $wpdb;
		$table = $wpdb->wpadpcbu_saved_pc;

		if ( $user_id ) {
			$sql   = "SELECT COUNT(id) FROM {$table} WHERE user_id = %d";
			$total = $wpdb->get_var( $wpdb->prepare( $sql, $user_id ) );
		} else {
			$sql   = "SELECT COUNT(id) FROM {$table}";
			$total = $wpdb->get_var( $sql );
		}

		return $total;
	}

	/**
	 * Get total amount from pc configuration data.
	 *
	 * @param string $configurations
	 *
	 * @return float
	 */
	public function pc_total( $configurations ) {
		$configurations = json_decode( $configurations, true );

		if ( isset( $configurations['total'] ) ) {
			return $configurations['total'];
		}

		return 0;
	}

	/**
	 * Get saved configuration item by share key.
	 *
	 * @param int    $id
	 * @param string $column
	 *
	 * @return void
	 */
	public static function get_config_by_share_key( $share_kay ) {
		global $wpdb;
		$table = $wpdb->wpadpcbu_saved_pc;
		$sql   = "SELECT * FROM {$table} WHERE `share_key` = %s";
		$data  = $wpdb->get_row( $wpdb->prepare( $sql, $share_kay ) );

		return $data;
	}
}
