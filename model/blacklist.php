<?php
/**
* Copyright (C) 2017 Petr Hucik <petr@getdatakick.com>
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@getdatakick.com so we can send you a copy immediately.
*
* @author    Petr Hucik <petr@getdatakick.com>
* @copyright 2018 Petr Hucik
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

use \Blackhole\IPAddress;

class BlackholeBlacklist extends ObjectModel {

  public static $definition = [
    'table'   => 'blackholebots_blacklist',
    'primary' => 'id_address',
    'fields'  => [
      'address'        => [ 'type' => self::TYPE_STRING, 'required' => true ],
      'cnt'            => [ 'type' => self::TYPE_INT, 'required' => true ],
      'date_add'       => [ 'type' => self::TYPE_DATE ],
      'date_upd'       => [ 'type' => self::TYPE_DATE ],
    ],
  ];

  public $id_address;
  public $address;
  public $cnt;
  public $date_add;
  public $date_upd;

  public static function inBlacklist(IPAddress $ip) {
    $addr = pSQL($ip->getAddress());
    $query = "SELECT id_address FROM "._DB_PREFIX_."blackholebots_blacklist WHERE address = '$addr'";
    $row = DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
    $id = $row ? (int)$row['id_address'] : null;
    if ($id) {
      $query = "UPDATE "._DB_PREFIX_."blackholebots_blacklist SET cnt = cnt + 1, date_upd=NOW() WHERE id_address=$id";
      DB::getInstance()->execute($query);
    }
    return $id;
  }

  public static function trap(IPAddress $ip) {
    if (! self::inBlacklist($ip)) {
      $entry = new BlackholeBlacklist();
      $entry->address = $ip->getAddress();
      $entry->cnt = 1;
      $entry->save();
    }
  }

}
