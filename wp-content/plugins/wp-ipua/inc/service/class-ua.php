<?php

namespace WePublish\IPUA\Inc\Service;

/**
 * Class UA
 * 该类用于实现插件的UA分析功能
 * @package WePublish\IPUA\Inc\Service\UA
 */
class UA {

	public string $platform = 'platform';
	public string $browser = 'browser';
	public string $browser_version = 'version';
	private string $ua;

	public function __construct( $ua = '' ) {
		$this->ua = $ua;
	}

	public function get_ua_info(): array {

		if ( empty( $this->ua ) || ! is_string( $this->ua ) ) {
			return [];
		}
		return $this->parse_user_agent( $this->ua );
	}

	/**
	 * Parses a user agent string into its important parts
	 *
	 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
	 *
	 * @return string[] an array with 'browser', 'version' and 'platform' keys
	 * @throws \InvalidArgumentException on not having a proper user agent to parse.
	 */
	function parse_user_agent( $u_agent = null ): array {
		if ( $u_agent === null && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$u_agent = (string) $_SERVER['HTTP_USER_AGENT'];
		}

		if ( $u_agent === null ) {
			throw new \InvalidArgumentException( 'parse_user_agent requires a user agent' );
		}

		$platform = null;
		$browser  = null;
		$version  = null;

		$return = [ $this->platform => &$platform, $this->browser => &$browser, $this->browser_version => &$version ];

		if ( ! $u_agent ) {
			return $return;
		}

		if ( preg_match( '/\((.*?)\)/m', $u_agent, $parent_matches ) ) {
			preg_match_all( <<<'REGEX'
/(?P<platform>BB\d+;|Android|Adr|Symbian|Sailfish|CrOS|Tizen|iPhone|iPad|iPod|Linux|(?:Open|Net|Free)BSD|Macintosh|
Windows(?:\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(?:New\ )?Nintendo\ (?:WiiU?|3?DS|Switch)|Xbox(?:\ One)?)
(?:\ [^;]*)?
(?:;|$)/imx
REGEX
				, $parent_matches[1], $result );

			$priority = [
				'Xbox One',
				'Xbox',
				'Windows Phone',
				'Tizen',
				'Android',
				'FreeBSD',
				'NetBSD',
				'OpenBSD',
				'CrOS',
				'X11',
				'Sailfish'
			];

			$result[ $this->platform ] = array_unique( $result[ $this->platform ] );
			if ( count( $result[ $this->platform ] ) > 1 ) {
				if ( $keys = array_intersect( $priority, $result[ $this->platform ] ) ) {
					$platform = reset( $keys );
				} else {
					$platform = $result[ $this->platform ][0];
				}
			} elseif ( isset( $result[ $this->platform ][0] ) ) {
				$platform = $result[ $this->platform ][0];
			}
		}

		if ( $platform == 'linux-gnu' || $platform == 'X11' ) {
			$platform = 'Linux';
		} elseif ( $platform == 'CrOS' ) {
			$platform = 'Chrome OS';
		} elseif ( $platform == 'Adr' ) {
			$platform = 'Android';
		} elseif ( $platform === null ) {
			if ( preg_match_all( '%(?P<platform>Android)[:/ ]%ix', $u_agent, $result ) ) {
				$platform = $result[ $this->platform ][0];
			}
		}

		preg_match_all( <<<'REGEX'
%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
TizenBrowser|(?:Headless)?Chrome|YaBrowser|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|(?-i:Edge)|EdgA?|CriOS|UCBrowser|Puffin|
OculusBrowser|SamsungBrowser|SailfishBrowser|XiaoMi/MiuiBrowser|
Baiduspider|Applebot|Facebot|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
Valve\ Steam\ Tenfoot|
NintendoBrowser|PLAYSTATION\ (?:\d|Vita)+)
\)?;?
(?:[:/ ](?P<version>[0-9A-Z.]+)|/[A-Z]*)%ix
REGEX
			, $u_agent, $result );

		// If nothing matched, return null (to avoid undefined index errors)
		if ( ! isset( $result[ $this->browser ][0], $result[ $this->browser_version ][0] ) ) {
			if ( preg_match( '%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result ) ) {
				return [
					$this->platform        => $platform ?: null,
					$this->browser         => $result[ $this->browser ],
					$this->browser_version => empty( $result[ $this->browser_version ] ) ? null : $result[ $this->browser_version ]
				];
			}

			return $return;
		}

		if ( preg_match( '/rv:(?P<version>[0-9A-Z.]+)/i', $u_agent, $rv_result ) ) {
			$rv_result = $rv_result[ $this->browser_version ];
		}

		$browser = $result[ $this->browser ][0];
		$version = $result[ $this->browser_version ][0];

		$lowerBrowser = array_map( 'strtolower', $result[ $this->browser ] );

		$find = function ( $search, &$key = null, &$value = null ) use ( $lowerBrowser ) {
			$search = (array) $search;

			foreach ( $search as $val ) {
				$xkey = array_search( strtolower( $val ), $lowerBrowser );
				if ( $xkey !== false ) {
					$value = $val;
					$key   = $xkey;

					return true;
				}
			}

			return false;
		};

		$findT = function ( array $search, &$key = null, &$value = null ) use ( $find ) {
			$value2 = null;
			if ( $find( array_keys( $search ), $key, $value2 ) ) {
				$value = $search[ $value2 ];

				return true;
			}

			return false;
		};

		$key = 0;
		$val = '';
		if ( $findT( [
			'OPR'                => 'Opera',
			'Facebot'            => 'iMessageBot',
			'UCBrowser'          => 'UC Browser',
			'YaBrowser'          => 'Yandex',
			'Iceweasel'          => 'Firefox',
			'Icecat'             => 'Firefox',
			'CriOS'              => 'Chrome',
			'Edg'                => 'Edge',
			'EdgA'               => 'Edge',
			'XiaoMi/MiuiBrowser' => 'MiuiBrowser'
		], $key, $browser ) ) {
			$version = is_numeric( substr( $result[ $this->browser_version ][ $key ], 0, 1 ) ) ? $result[ $this->browser_version ][ $key ] : null;
		} elseif ( $find( 'Playstation Vita', $key, $platform ) ) {
			$platform = 'PlayStation Vita';
			$browser  = 'Browser';
		} elseif ( $find( [ 'Kindle Fire', 'Silk' ], $key, $val ) ) {
			$browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
			$platform = 'Kindle Fire';
			if ( ! ( $version = $result[ $this->browser_version ][ $key ] ) || ! is_numeric( $version[0] ) ) {
				$version = $result[ $this->browser_version ][ array_search( 'Version', $result[ $this->browser ] ) ];
			}
		} elseif ( $find( 'NintendoBrowser', $key ) || $platform == 'Nintendo 3DS' ) {
			$browser = 'NintendoBrowser';
			$version = $result[ $this->browser_version ][ $key ];
		} elseif ( $find( 'Kindle', $key, $platform ) ) {
			$browser = $result[ $this->browser ][ $key ];
			$version = $result[ $this->browser_version ][ $key ];
		} elseif ( $find( 'Opera', $key, $browser ) ) {
			$find( 'Version', $key );
			$version = $result[ $this->browser_version ][ $key ];
		} elseif ( $find( 'Puffin', $key, $browser ) ) {
			$version = $result[ $this->browser_version ][ $key ];
			if ( strlen( $version ) > 3 ) {
				$part = substr( $version, - 2 );
				if ( ctype_upper( $part ) ) {
					$version = substr( $version, 0, - 2 );

					$flags = [
						'IP' => 'iPhone',
						'IT' => 'iPad',
						'AP' => 'Android',
						'AT' => 'Android',
						'WP' => 'Windows Phone',
						'WT' => 'Windows'
					];
					if ( isset( $flags[ $part ] ) ) {
						$platform = $flags[ $part ];
					}
				}
			}
		} elseif ( $find( [
			'Applebot',
			'IEMobile',
			'Edge',
			'Midori',
			'Vivaldi',
			'OculusBrowser',
			'SamsungBrowser',
			'Valve Steam Tenfoot',
			'Chrome',
			'HeadlessChrome',
			'SailfishBrowser'
		], $key, $browser ) ) {
			$version = $result[ $this->browser_version ][ $key ];
		} elseif ( $rv_result && $find( 'Trident' ) ) {
			$browser = 'MSIE';
			$version = $rv_result;
		} elseif ( $browser == 'AppleWebKit' ) {
			if ( $platform == 'Android' ) {
				$browser = 'Android Browser';
			} elseif ( str_starts_with( (string) $platform, 'BB' ) ) {
				$browser  = 'BlackBerry Browser';
				$platform = 'BlackBerry';
			} elseif ( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
				$browser = 'BlackBerry Browser';
			} else {
				$find( 'Safari', $key, $browser ) || $find( 'TizenBrowser', $key, $browser );
			}

			$find( 'Version', $key );
			$version = $result[ $this->browser_version ][ $key ];
		} elseif ( $pKey = preg_grep( '/playstation \d/i', $result[ $this->browser ] ) ) {
			$pKey = reset( $pKey );

			$platform = 'PlayStation ' . preg_replace( '/\D/', '', $pKey );
			$browser  = 'NetFront';
		}

		return $return;
	}
}
