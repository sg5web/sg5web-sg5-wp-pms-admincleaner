<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*====================================================
=                   Admin login class                =
====================================================*/

class DP_AC_adminLogin {
    private $wp_login_php;
	public function __construct(){
		add_action( 'plugins_loaded', array( $this, 'this_plugins_loaded' ), 999 );
        add_action( 'wp_loaded', array( $this, 'this_wp_loaded' ) );
        add_filter( 'site_url', array( $this, 'this_site_url' ), 10, 4 );
        add_filter( 'wp_redirect', array( $this, 'this_wp_redirect' ), 10, 2 );
		add_filter( 'site_option_welcome_email', array( $this, 'this_welcome_email' ) );
		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        add_filter( 'login_url', array( $this, 'this_login_url' ), 10, 3 );
        add_filter( 'user_request_action_email_content', array( $this, 'user_request_action_email_content' ), 999, 2 );
		add_filter( 'site_status_tests', array( $this, 'this_site_status_tests' ) );
    }

    public function this_plugins_loaded(){
        global $pagenow;

		if ( ! is_multisite()
		     && ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
		          || strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false ) && get_option('DP_AC_al_option') != "yes" ) {

			wp_die( __( 'This feature is not enabled.', 'wps-hide-login' ) );

		}

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		       || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
		     && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';

		} elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->changed_login_slug(), 'relative' ) )
		           || ( ! get_option( 'permalink_structure' )
		                && isset( $_GET[ $this->changed_login_slug() ] )
		                && empty( $_GET[ $this->changed_login_slug() ] ) ) ) {

			$_SERVER['SCRIPT_NAME'] = $this->changed_login_slug();

			$pagenow = 'wp-login.php';

		} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
		             || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
		           && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';
		}
    }

    public function trailingslashit( $string ) {
		return $this->trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}

    public function trailing_slashes() {
		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );
	}

    public function changed_login_slug() {
        if ( $slug = get_option( 'DP_AC_login' ) ) {
            return $slug;
        } else if ( $slug = 'login' ) {
            return $slug;
        }
	}

	public function changed_redirect_slug() {
		if ( $slug = get_option( 'DP_AC_redirect' ) ) {
			return $slug;
		} else if ( $slug = '404' ) {
			return $slug;
		}
	}

	public function changed_login_url( $scheme = null ) {
		$url = home_url( '/', $scheme );
		if ( get_option( 'permalink_structure' ) ) {
			return $this->trailingslashit( $url . $this->changed_login_slug() );
		} else {
			return $url . '?' . $this->changed_login_slug();
		}
	}

	public function changed_redirect_url( $scheme = null ) {
		if ( get_option( 'permalink_structure' ) ) {
			return $this->trailingslashit( home_url( '/', $scheme ) . $this->changed_redirect_slug() );
		} else {
			return home_url( '/', $scheme ) . '?' . $this->changed_redirect_slug();
		}
	}

    public function this_wp_loaded() {
		global $pagenow;
		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );
		if ( ! ( isset( $_GET['action'] ) && $_GET['action'] === 'postpass' && isset( $_POST['post_password'] ) ) ) {
			if ( is_admin() && ! is_user_logged_in() && ! defined( 'WP_CLI' ) && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' ) && $pagenow !== 'admin-post.php' && $request['path'] !== '/wp-admin/options.php' ) {
				wp_safe_redirect( $this->changed_redirect_url() );
				die();
			}
			if ( ! is_user_logged_in() && isset( $_GET['wc-ajax'] ) && $pagenow === 'profile.php' ) {
				wp_safe_redirect( $this->changed_redirect_url() );
				die();
			}
			if ( ! is_user_logged_in() && isset( $request['path'] ) && $request['path'] === '/wp-admin/options.php' ) {
				header('Location: ' . $this->changed_redirect_url() );
				die;
			}
			if ( $pagenow === 'wp-login.php' && isset( $request['path'] ) && $request['path'] !== $this->trailingslashit( $request['path'] ) && get_option( 'permalink_structure' ) ) {
				wp_safe_redirect( $this->trailingslashit( $this->changed_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
				die;
			} elseif ( $this->wp_login_php ) {
				if ( ( $referer = wp_get_referer() )
				     && strpos( $referer, 'wp-activate.php' ) !== false
				     && ( $referer = parse_url( $referer ) )
				     && ! empty( $referer['query'] ) ) {
					parse_str( $referer['query'], $referer );
					@require_once WPINC . '/ms-functions.php';
					if ( ! empty( $referer['key'] )
					     && ( $result = wpmu_activate_signup( $referer['key'] ) )
					     && is_wp_error( $result )
					     && ( $result->get_error_code() === 'already_active' || $result->get_error_code() === 'blog_taken' ) ) {

						wp_safe_redirect( $this->changed_login_url(). ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

						die;
					}
				}
				$this->wp_template_loader();
			} elseif ( $pagenow === 'wp-login.php' ) {
				global $error, $interim_login, $action, $user_login;
				$redirect_to = admin_url();
				$requested_redirect_to = '';
				if ( isset( $_REQUEST['redirect_to'] ) ) {
					$requested_redirect_to = $_REQUEST['redirect_to'];
				}
				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					if ( ! isset( $_REQUEST['action'] ) ) {
						$logged_in_redirect = apply_filters( 'whl_logged_in_redirect', $redirect_to, $requested_redirect_to, $user );
						wp_safe_redirect( $logged_in_redirect );
						die();
					}
				}
				@require_once ABSPATH . 'wp-login.php';
				die;
			}
		}
	}

    public function wp_template_loader() {
		global $pagenow;
		$pagenow = 'index.php';
		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}
		wp();
		require_once( ABSPATH . WPINC . '/template-loader.php' );
		die;
	}

	public function this_site_url( $url, $path, $scheme, $blog_id ) {
		return $this->clean_wp_login_php( $url, $scheme );
	}

	public function network_site_url( $url, $path, $scheme ) {
		return $this->clean_wp_login_php( $url, $scheme );
	}

	public function this_wp_redirect( $location, $status ) {
		if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
			return $location;
		}
		return $this->clean_wp_login_php( $location );
	}

	public function clean_wp_login_php( $url, $scheme = null ) {
		if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
			return $url;
		}
		if ( strpos( $url, 'wp-login.php' ) !== false && strpos( wp_get_referer(), 'wp-login.php' ) === false ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}
			$args = explode( '?', $url );
			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}
				$url = add_query_arg( $args, $this->changed_login_url( $scheme ) );
			} else {
				$url = $this->changed_login_url( $scheme );
			}
		}
		return $url;
	}

	public function this_welcome_email( $value ) {
		return $value = str_replace( 'wp-login.php', trailingslashit( get_site_option( 'DP_AC_login', 'login' ) ), $value );
	}

    public function template_redirect() {
		if ( ! empty( $_GET ) && isset( $_GET['action'] ) && 'confirmaction' === $_GET['action'] && isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
			$request_id = (int) $_GET['request_id'];
			$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
			$result     = wp_validate_user_request_key( $request_id, $key );
			if ( ! is_wp_error( $result ) ) {
				wp_redirect( add_query_arg( array(
					'action'      => 'confirmaction',
					'request_id'  => $_GET['request_id'],
					'confirm_key' => $_GET['confirm_key']
				), $this->changed_login_url()
				) );
				exit();
			}
		}
	}

    public function this_login_url( $login_url, $redirect, $force_reauth ) {
		if ( is_404() ) {
			return '#';
		}
		if ( $force_reauth === false ) {
			return $login_url;
		}
		if ( empty( $redirect ) ) {
			return $login_url;
		}
		$redirect = explode( '?', $redirect );
		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}
		return $login_url;
	}
    
    public function user_request_action_email_content( $email_text, $email_data ) {
		$email_text = str_replace( '###CONFIRM_URL###', esc_url_raw( str_replace( $this->changed_login_slug() . '/', 'wp-login.php', $email_data['confirm_url'] ) ), $email_text );

		return $email_text;
	}

    public function this_site_status_tests( $tests ) {
		unset( $tests['async']['loopback_requests'] );

		return $tests;
	}
}

new DP_AC_adminLogin();