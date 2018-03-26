<?php
/*
Title:        Blackhole for Bad Bots
Description:  Automatically trap and block bots that don't obey robots.txt rules
Project URL:  http://perishablepress.com/blackhole-bad-bots/
Author:       Jeff Starr (aka Perishable)
Author:       Petr Hucik (petr@getdatakick.com)
Version:      4.0
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
*/
namespace Blackhole;

class IPAddress {
  private $type = null;
  private $address = null;
  private $source = null;

  public function __construct($address, $type, $source=null) {
    $this->type = $type;
    $this->address = $address;
    $this->source = $source;
  }

  public function isValid() {
    return $this->type !== 'invalid';
  }

  public function getType() {
    return $this->type;
  }

  public function getAddress() {
    return $this->address;
  }

  public static function get() {
    $addr = self::findIPAddress();
    return $addr ? $addr : new IPAddress('0:0:0:0', 'invalid');
  }

  private static function findIPAddress() {
    $keys = [
      'HTTP_CF_CONNECTING_IP',
      'HTTP_CLIENT_IP',
      'HTTP_X_FORWARDED_FOR',
      'HTTP_X_FORWARDED',
      'HTTP_X_CLUSTER_CLIENT_IP',
      'HTTP_X_REAL_IP',
      'HTTP_X_COMING_FROM',
      'HTTP_PROXY_CONNECTION',
      'HTTP_FORWARDED_FOR',
      'HTTP_FORWARDED',
      'HTTP_COMING_FROM',
      'HTTP_VIA',
      'REMOTE_ADDR'
    ];
    foreach ($keys as $key) {
      if (array_key_exists($key, $_SERVER) === true) {
        foreach (explode(',', $_SERVER[$key]) as $ip) {
          $addr = self::normalize(trim($ip), $key);
          if ($addr) {
            return $addr;
          }
        }
      }
    }
    return null;
  }

  private static function normalize($ip, $source) {
    if (strpos($ip, ':') !== false && substr_count($ip, '.') == 3 && strpos($ip, '[') === false) {
      // IPv4 with port (e.g., 123.123.123:80)
      $ip = explode(':', $ip);
      $ip = $ip[0];
      if (self::validate($ip)) {
        return new IPAddress($ip, 'ipv4', $source);
      }
    } else {
      // IPv6 with port (e.g., [::1]:80)
      $ip = explode(']', $ip);
      $ip = ltrim($ip[0], '[');
      if (self::validate($ip)) {
        return new IPAddress($ip, 'ipv6', $source);
      }
    }
  }

  private static function validate($ip) {
    $options  = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
    $filtered = filter_var($ip, FILTER_VALIDATE_IP, $options);
    if (!$filtered || empty($filtered)) {
      if (preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $ip)) {
        return $ip; // IPv4
      } elseif (preg_match("/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/", $ip)) {
        return $ip; // IPv6
      }
      return false;
    }
    return $filtered;
  }
}
