<?php
/**
 * Database Engine Class
 * Responsible for all raw SQL interactions. Separating this ensures
 * the UI can change without breaking the data logic.
 * @package CleanSweep
 */
class CleanSweep_Engine {

	/**
	 * Fetches the first 10 orphaned postmeta rows.
	 * @return array|object Results from the database.
	 */
	public function get_orphaned_meta() {
		global $wpdb;
		$query = "SELECT pm.meta_id, pm.meta_key 
				  FROM {$wpdb->postmeta} pm 
				  LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
				  WHERE p.ID IS NULL 
				  LIMIT 10";
		return $wpdb->get_results( $query );
	}

	/**
	 * Executes the deletion of all orphaned meta rows.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public function purge_orphans() {
		global $wpdb;
		return $wpdb->query(
			"DELETE pm FROM {$wpdb->postmeta} pm 
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
			 WHERE p.ID IS NULL"
		);
	}

	/**
	 * Gathers data regarding autoloaded options.
	 * @return array Total size in bytes and top 10 heavy options.
	 */
	public function get_autoload_data() {
		global $wpdb;
		
		$size_query = "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'";
		$top_query  = "SELECT option_name, LENGTH(option_value) AS option_size 
					   FROM {$wpdb->options} 
					   WHERE autoload = 'yes' 
					   ORDER BY option_size DESC 
					   LIMIT 10";

		$bytes = $wpdb->get_var( $size_query );
		
		return [
			'total_size' => $bytes ? size_format( $bytes, 2 ) : '0 B',
			'top_ten'    => $wpdb->get_results( $top_query )
		];
	}

	/**
	 * Switches an option's autoload status from 'yes' to 'no'.
	 * @param string $option_name The key of the option to offload.
	 * @return int|false Number of rows affected.
	 */
	public function toggle_autoload( $option_name ) {
		global $wpdb;
		return $wpdb->update( 
			$wpdb->options, 
			[ 'autoload' => 'no' ], 
			[ 'option_name' => $option_name ] 
		);
	}
}