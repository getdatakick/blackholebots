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

class Utils {
  public static function sanitize($string) {
    if ($string) {
      $string = trim($string);
      $string = strip_tags($string);
      $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
      $string = str_replace("\n", "", $string);
      $string = trim($string);
    }
    return $string;
  }
}
