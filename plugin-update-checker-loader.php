<?php
/**
 * Lightweight GitHub updater for this plugin.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ST404_WPA_GitHub_Updater' ) ) {
	/**
	 * GitHub updater class.
	 */
	class ST404_WPA_GitHub_Updater {

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
		add_action( 'init', array( $this, 'maybe_invalidate_cached_release' ), 0 );
		// WordPress ne reconstruit le transient que périodique~12 h : sans ce filtre,
		// la mise à jour GitHub n'apparaît pas à la lecture du cache (comportement PUC).
		add_filter( 'site_transient_update_plugins', array( $this, 'filter_update_transient' ), 10, 1 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'filter_update_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 20, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Invalide le cache GitHub sur Plugins / Mises a jour / Tableau de bord (comportement proche de PUC / BOGO).
	 *
	 * @return void
	 */
	public function maybe_invalidate_cached_release() {
		if ( ! is_admin() ) {
			return;
		}
		global $pagenow;
		if ( empty( $pagenow ) || ! is_string( $pagenow ) ) {
			return;
		}
		if ( ! in_array( $pagenow, array( 'plugins.php', 'update-core.php', 'index.php' ), true ) ) {
			return;
		}
		$this->clear_release_transient();
	}

	/**
	 * @return void
	 */
	private function clear_release_transient() {
		delete_site_transient( 'wpa_github_release_' . md5( $this->owner . '/' . $this->repo ) );
	}

	/**
	 * Build API headers.
	 *
	 * @return array
	 */
	private function get_headers() {
		$headers = array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
		);

		$token = '';
		if ( defined( 'ST404_WPA_GITHUB_TOKEN' ) && ST404_WPA_GITHUB_TOKEN ) {
			$token = (string) ST404_WPA_GITHUB_TOKEN;
		} elseif ( defined( 'WPA_GITHUB_TOKEN' ) && WPA_GITHUB_TOKEN ) {
			$token = (string) WPA_GITHUB_TOKEN;
		}
		if ( ! $token && function_exists( 'getenv' ) ) {
			$env = getenv( 'GITHUB_TOKEN' );
			if ( is_string( $env ) && '' !== $env ) {
				$token = $env;
			}
		}

		$token = apply_filters( 'wpa_github_api_token', $token, $this->owner, $this->repo );

		if ( '' !== $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
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

		// TTL court : sans auth, GitHub limite vite ; avec invalidation sur plugins/mises a jour, on reste reactif.
		set_site_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
		return $data;
	}

	/**
	 * Merge GitHub release info whenever WordPress lit ou enregistre update_plugins.
	 *
	 * @param object|false $transient Plugins update transient.
	 * @return object|false
	 */
	public function filter_update_transient( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}
		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = array();
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$latest_version = ltrim( (string) $release['tag_name'], 'v' );
		if ( version_compare( $latest_version, $this->version, '<=' ) ) {
			unset( $transient->response[ $this->plugin_basename ] );
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
			if ( ! class_exists( 'ST404_WPA_GitHub_Updater' ) ) {
				return;
			}
			$updater = new ST404_WPA_GitHub_Updater( WPA_PLUGIN_BASENAME, WPA_VERSION );
			$updater->init();
		},
		10
	);
}
