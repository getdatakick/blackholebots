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

class Whois {

  public static function getInfo(IPAddress $addr) {
    $msg = '';
    $extra = '';
    $server = 'whois.arin.net';
    $ip = $addr->getAddress();

    if (!$ip = gethostbyname($ip)) {
      return null;
    }

    if (! $sock = fsockopen($server, 43, $num, $error, 20)) {
      unset($sock);
      return "Timed-out connecting to $server (port 43).\n";
    }

    fputs($sock, "n $ip\n");
    $buffer = '';
    while (!feof($sock)) {
      $buffer .= fgets($sock, 10240);
    }
    fclose($sock);

    if (stripos($buffer, 'ripe.net')) {
      $nextServer = 'whois.ripe.net';
    } elseif (stripos($buffer, 'nic.ad.jp')) {
      $nextServer = 'whois.nic.ad.jp';
      $extra = '/e'; // suppress JaPaNIC characters
    } elseif (stripos($buffer, 'registro.br')) {
      $nextServer = 'whois.registro.br';
    }

    if (isset($nextServer)) {
      $buffer = '';
      if (!$sock = fsockopen($nextServer, 43, $num, $error, 10)) {
        unset($sock);
        return 'Timed-out connecting to '. $nextServer .' (port 43)'. "\n\n";
      } else {
        fputs($sock, $ip . $extra . "\n");
        while (!feof($sock)) $buffer .= fgets($sock, 10240);
        fclose($sock);
      }
    }

    $replacements = array("\n", "\n\n", "");
    $patterns = array("/\\n\\n\\n\\n/i", "/\\n\\n\\n/i", "/#(\s)?/i");
    $buffer = preg_replace($patterns, $replacements, $buffer);
    $buffer = htmlentities(trim($buffer), ENT_QUOTES, 'UTF-8');
    return $buffer;
  }
}
