<?php
/**
 * Admin Interface Class
 * Handles the WordPress dashboard integration, nonces, and rendering.
 * @package CleanSweep
 */
class CleanSweep_Admin {

	/**
	 * @var CleanSweep_Engine Reference to the data layer.
	 */
	private $engine;

	/**
	 * Constructor: Hooks into WordPress admin.
	 * @param CleanSweep_Engine $engine Injected database engine.
	 */
	public function __construct( $engine ) {
		$this->engine = $engine;
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'process_actions' ] );
	}

	/**
	 * Static activation method to flush internal caches.
	 */
	public static function activate() {
		wp_clean_plugins_cache();
	}

	/**
	 * Adds the Clean-Sweep page to the Tools menu.
	 */
	public function add_menu() {
		add_management_page(
			'Clean-Sweep Audit',
			'Clean-Sweep',
			'manage_options',
			'clean-sweep',
			[ $this, 'render_ui' ]
		);
	}

	/**
	 * Logic Controller: Routes and verifies POST requests.
	 */
	public function process_actions() {
		// Verify Nonce for all POST actions in this plugin
		if ( ! isset( $_POST['cs_nonce'] ) || ! wp_verify_nonce( $_POST['cs_nonce'], 'cs_secure_action' ) ) {
			return;
		}

		// Handle Purge
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'purge' ) {
			$count = $this->engine->purge_orphans();
			wp_redirect( admin_url( 'tools.php?page=clean-sweep&purged=' . $count ) );
			exit;
		}

		// Handle Autoload Offload
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'offload' ) {
			$opt_name = sanitize_text_field( $_POST['opt_name'] );
			$this->engine->toggle_autoload( $opt_name );
			wp_redirect( admin_url( 'tools.php?page=clean-sweep&offloaded=1' ) );
			exit;
		}
	}

	/**
	 * Renders the Plugin Dashboard UI.
	 */
	public function render_ui() {
		$orphans  = $this->engine->get_orphaned_meta();
		$autoload = $this->engine->get_autoload_data();
		$purged   = isset( $_GET['purged'] ) ? intval( $_GET['purged'] ) : -1;

		?>
		<div class="wrap">
			<h1>Clean-Sweep <small>v<?php echo CS_VERSION; ?></small></h1>
			<p>Database architect tool by <a href="https://afashah.com" target="_blank">Ashar Fazail</a></p>
			
			<?php if ( $purged >= 0 ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong>Success:</strong> <?php echo $purged; ?> ghost rows purged.</p></div>
			<?php endif; ?>

			<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
				
				<div class="card" style="flex: 1; min-width: 400px; border-left: 4px solid #d63638;">
					<h2>Orphaned Meta (Ghosts)</h2>
					<?php if ( empty( $orphans ) ) : ?>
						<p style="color: #00a32a;">✓ Database Integrity High.</p>
					<?php else : ?>
						<table class="widefat striped">
							<thead><tr><th>ID</th><th>Key</th></tr></thead>
							<?php foreach ( $orphans as $row ) : ?>
								<tr><td><?php echo $row->meta_id; ?></td><td><code><?php echo esc_html($row->meta_key); ?></code></td></tr>
							<?php endforeach; ?>
						</table>
						<form method="post" style="margin-top:15px;">
							<?php wp_nonce_field( 'cs_secure_action', 'cs_nonce' ); ?>
							<input type="hidden" name="action" value="purge">
							<button type="submit" class="button button-link-delete" onclick="return confirm('Purge all orphaned rows?');">Purge Ghosts</button>
						</form>
					<?php endif; ?>
				</div>

				<div class="card" style="flex: 1; min-width: 400px; border-left: 4px solid #000;">
					<h2>Autoload Audit (<?php echo $autoload['total_size']; ?>)</h2>
					<table class="widefat striped">
						<thead><tr><th>Option</th><th>Size</th><th>Action</th></tr></thead>
						<?php foreach ( $autoload['top_ten'] as $opt ) : ?>
							<tr>
								<td><code><?php echo esc_html($opt->option_name); ?></code></td>
								<td><?php echo number_format($opt->option_size); ?> B</td>
								<td>
									<form method="post" style="display:inline;">
										<?php wp_nonce_field( 'cs_secure_action', 'cs_nonce' ); ?>
										<input type="hidden" name="action" value="offload">
										<input type="hidden" name="opt_name" value="<?php echo esc_attr($opt->option_name); ?>">
										<button type="submit" class="button button-small">Offload</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>

			</div>
		</div>
		<?php
	}
}