<?php
/**
 * Plugin Name: Clean-Sweep
 * Plugin URI:  https://afashah.com
 * Description: A stubborn database utility to audit orphaned meta and autoloaded options. No bloat, just SQL.
 * Version:     0.1
 * Author:      Ashar Fazail
 * Author URI:  https://afashah.com
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CleanSweep {

	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_audit_page' ] );
		}
	}

	public function register_audit_page() {
		add_management_page(
			'Clean-Sweep Audit',
			'Clean-Sweep',
			'manage_options',
			'clean-sweep',
			[ $this, 'display_dashboard' ]
		);
	}

	/**
	 * Logic: Get the EXACT list of orphaned meta rows.
	 */
	private function get_orphaned_meta_list() {
		global $wpdb;
		$sql = "SELECT pm.meta_id, pm.post_id, pm.meta_key 
				FROM {$wpdb->postmeta} pm 
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
				WHERE p.ID IS NULL 
				LIMIT 50"; // Limit to 50 so we don't crash the page on massive DBs
		return $wpdb->get_results( $sql );
	}

	private function audit_autoload_size() {
		global $wpdb;
		$sql  = "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'";
		$bytes = $wpdb->get_var( $sql );
		return $bytes ? size_format( $bytes, 2 ) : '0 B';
	}

	private function get_top_autoloaded_options() {
		global $wpdb;
		$sql = "SELECT option_name, LENGTH(option_value) AS option_size 
				FROM {$wpdb->options} 
				WHERE autoload = 'yes' 
				ORDER BY option_size DESC 
				LIMIT 10";
		return $wpdb->get_results( $sql );
	}

	public function display_dashboard() {
		$orphaned_list = $this->get_orphaned_meta_list();
		$load_size     = $this->audit_autoload_size();
		$top_bloat     = $this->get_top_autoloaded_options();
		
		?>
		<div class="wrap">
			<h1>Clean-Sweep <small>v0.1</small></h1>
			<p>Database architect tool by <a href="https://afashah.com" target="_blank">Ashar Fazail</a></p>
			
			<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
				
				<div class="card" style="flex: 1; min-width: 400px; border-left: 4px solid #d63638;">
					<h2>Orphaned Meta (Ghost Rows)</h2>
					<p class="description">These rows belong to Post IDs that no longer exist.</p>
					
					<?php if ( empty( $orphaned_list ) ) : ?>
						<p style="color: #00a32a; font-weight: bold;">✓ Your database is clean. No orphaned meta found.</p>
					<?php else : ?>
						<table class="widefat striped" style="margin-top:10px;">
							<thead>
								<tr>
									<th>Meta ID</th>
									<th>Post ID</th>
									<th>Meta Key</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $orphaned_list as $meta ) : ?>
									<tr>
										<td><?php echo $meta->meta_id; ?></td>
										<td><?php echo $meta->post_id; ?></td>
										<td><code><?php echo esc_html( $meta->meta_key ); ?></code></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>

				<div class="card" style="flex: 1; min-width: 400px; border-left: 4px solid #000;">
					<h2>Autoload Audit (<?php echo $load_size; ?>)</h2>
					<p class="description">Top 10 options loading on every page request.</p>
					<table class="widefat striped" style="margin-top:10px;">
						<thead>
							<tr>
								<th>Option Name</th>
								<th>Size (Bytes)</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_bloat as $option ) : ?>
								<tr>
									<td><code><?php echo esc_html( $option->option_name ); ?></code></td>
									<td><?php echo number_format( $option->option_size ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

			</div>

			<div style="margin-top: 20px;">
				<button class="button button-secondary" disabled>Execute Clean-Sweep (v0.2 Only)</button>
				<p class="description">This is a <strong>Read-Only</strong> report. No data was deleted.</p>
			</div>
		</div>
		<?php
	}
}

new CleanSweep();
