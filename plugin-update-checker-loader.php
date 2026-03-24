<?php
/**
 * Lightweight GitHub updater for this plugin.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub updater class.
 */
class WPA_GitHub_Updater {

	/**
	 * Plugin file basename.
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * Current version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * GitHub owner.
	 *
	 * @var string
	 */
	private $owner = 'lestudio404';

	/**
	 * GitHub repository.
	 *
	 * @var string
	 */
	private $repo = 'wc-profit-analyzer';

	/**
	 * Constructor.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @param string $version Current version.
	 */
	public function __construct( $plugin_basename, $version ) {
		$this->plugin_basename = $plugin_basename;
		$this->version         = $version;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 20, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Build API headers.
	 *
	 * @return array
	 */
	private function get_headers() {
		$headers = array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'WPA-Updater',
		);

		$token = defined( 'WPA_GITHUB_TOKEN' ) ? WPA_GITHUB_TOKEN : getenv( 'GITHUB_TOKEN' );
		if ( ! empty( $token ) ) {
			$headers['Authorization'] = 'token ' . $token;
		}
		return $headers;
	}

	/**
	 * Read latest release from GitHub.
	 *
	 * @return array|null
	 */
	private function get_latest_release() {
		$cache_key = 'wpa_github_release_' . md5( $this->owner . '/' . $this->repo );
		$cached    = get_site_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url      = sprintf( 'https://api.github.com/repos/%1$s/%2$s/releases/latest', $this->owner, $this->repo );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 20,
				'headers' => $this->get_headers(),
			)
		);
		if ( is_wp_error( $response ) ) {
			return null;
		}
		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			return null;
		}

		set_site_transient( $cache_key, $data, 15 * MINUTE_IN_SECONDS );
		return $data;
	}

	/**
	 * Check updates.
	 *
	 * @param object $transient Plugins update transient.
	 * @return object
	 */
	public function check_for_updates( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$latest_version = ltrim( (string) $release['tag_name'], 'v' );
		if ( version_compare( $latest_version, $this->version, '<=' ) ) {
			return $transient;
		}

		$download_url = isset( $release['zipball_url'] ) ? (string) $release['zipball_url'] : '';
		if ( isset( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				if ( ! empty( $asset['browser_download_url'] ) && ! empty( $asset['name'] ) && false !== strpos( (string) $asset['name'], 'wc-profit-analyzer' ) ) {
					$download_url = (string) $asset['browser_download_url'];
					break;
				}
			}
		}

		$plugin              = new stdClass();
		$plugin->slug        = 'wc-profit-analyzer';
		$plugin->plugin      = $this->plugin_basename;
		$plugin->new_version = $latest_version;
		$plugin->url         = 'https://github.com/' . $this->owner . '/' . $this->repo;
		$plugin->package     = $download_url;

		$transient->response[ $this->plugin_basename ] = $plugin;
		return $transient;
	}

	/**
	 * Plugin info popup.
	 *
	 * @param false|object|array $result Existing result.
	 * @param string             $action Action.
	 * @param object             $args API args.
	 * @return false|object|array
	 */
	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'wc-profit-analyzer' !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		$latest_version = ltrim( (string) $release['tag_name'], 'v' );
		$body           = ! empty( $release['body'] ) ? wp_kses_post( wpautop( (string) $release['body'] ) ) : '';

		$info                = new stdClass();
		$info->name          = 'WooCommerce Profit Analyzer';
		$info->slug          = 'wc-profit-analyzer';
		$info->version       = $latest_version;
		$info->author        = '<a href="https://github.com/lestudio404">ST404</a>';
		$info->homepage      = 'https://github.com/' . $this->owner . '/' . $this->repo;
		$info->requires      = '6.4';
		$info->tested        = '6.8';
		$info->requires_php  = '7.4';
		$info->last_updated  = ! empty( $release['published_at'] ) ? gmdate( 'Y-m-d', strtotime( (string) $release['published_at'] ) ) : gmdate( 'Y-m-d' );
		$info->sections      = array(
			'description' => esc_html__( 'Analyse de rentabilite WooCommerce (commandes, produits, marges).', 'wc-profit-analyzer' ),
			'changelog'   => $body,
		);
		$info->download_link = isset( $release['zipball_url'] ) ? (string) $release['zipball_url'] : '';

		return $info;
	}

	/**
	 * Keep plugin folder name stable after update.
	 *
	 * @param bool  $response Install response.
	 * @param array $hook_extra Hook extra args.
	 * @param array $result Result.
	 * @return bool
	 */
	public function after_install( $response, $hook_extra, $result ) {
		if ( empty( $hook_extra['plugin'] ) || $this->plugin_basename !== $hook_extra['plugin'] ) {
			return $response;
		}
		if ( empty( $result['destination'] ) || empty( $result['local_destination'] ) ) {
			return $response;
		}

		global $wp_filesystem;
		$proper_destination = trailingslashit( WP_PLUGIN_DIR ) . 'wc-profit-analyzer';
		$wp_filesystem->move( $result['destination'], $proper_destination );
		$result['destination'] = $proper_destination;

		if ( is_plugin_active( $this->plugin_basename ) ) {
			activate_plugin( $this->plugin_basename );
		}

		return $response;
	}
}

add_action(
	'plugins_loaded',
	static function() {
		if ( ! defined( 'WPA_PLUGIN_BASENAME' ) || ! defined( 'WPA_VERSION' ) ) {
			return;
		}
		$updater = new WPA_GitHub_Updater( WPA_PLUGIN_BASENAME, WPA_VERSION );
		$updater->init();
	},
	30
);
