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

require_once BLACKHOLE_BOTS_ROOT . '/classes/utils.php';
require_once BLACKHOLE_BOTS_ROOT . '/classes/ip-address.php';
require_once BLACKHOLE_BOTS_ROOT . '/classes/whois.php';
require_once BLACKHOLE_BOTS_ROOT . '/model/blacklist.php';

use \Db;
use \Mail;
use \Configuration;
use \BlackholeBlacklist;

class BlackholeBotsCore extends \Module {

  public function __construct() {
    $this->name = 'blackholebots';
    $this->tab = 'export';
    $this->version = '1.0.2';
    $this->author = 'DataKick';
    $this->need_instance = 0;
    $this->bootstrap = true;

    parent::__construct();
    $this->displayName = $this->l('Blackhole for Bad Bots');
    $this->description = $this->l("Automagically ban bad bots who don't follow robots.txt guidelines");
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
  }

  public function install($createTables=true) {
    return (
      parent::install() &&
      $this->installDb($createTables) &&
      $this->registerHook('displayHeader') &&
      $this->registerHook('moduleRoutes') &&
      $this->registerHook('displayFooter')
    );
  }

  public function uninstall($dropTables=true) {
    return (
      $this->uninstallDb($dropTables) &&
      $this->unregisterHook('displayHeader') &&
      $this->unregisterHook('displayFooter') &&
      $this->unregisterHook('moduleRoutes') &&
      parent::uninstall()
    );
  }

  public function reset() {
    return (
      $this->unregisterHook('displayHeader') &&
      $this->unregisterHook('displayFooter') &&
      $this->registerHook('displayHeader') &&
      $this->registerHook('displayFooter')
    );
  }


  private function installDb($create) {
    if (! $create) {
      return true;
    }
    return $this->executeSqlScript('install');
  }

  private function uninstallDb($drop) {
    if (! $drop) {
      return true;
    }
    return $this->executeSqlScript('uninstall');
  }

  public function executeSqlScript($script) {
    $file = BLACKHOLE_BOTS_ROOT . '/sql/' . $script . '.sql';
    if (! file_exists($file)) {
      return false;
    }
    $sql = file_get_contents($file);
    if (! $sql) {
      return false;
    }
    $sql = str_replace(['PREFIX_', 'ENGINE_TYPE', 'CHARSET_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_, 'utf8'], $sql);
    $sql = preg_split("/;\s*[\r\n]+/", $sql);
    foreach ($sql as $statement) {
      $stmt = trim($statement);
      if ($stmt) {
        if (!Db::getInstance()->execute($stmt)) {
          return false;
        }
      }
    }
    return true;
  }

  private function getTrapUrl() {
    $prefix = (int)Configuration::get('PS_REWRITING_SETTINGS') ? '' : rtrim($this->_path, '/');
    return $prefix . '/blackhole/';
  }

  public function trap() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
    if (self::whitelisted($ua)) {
      return;
    }

    $ip = IPAddress::get();
    if ($ip->isValid()) {
      $whois = Whois::getInfo($ip);
      if (! BlackholeBlacklist::inBlacklist($ip)) {
        BlackholeBlacklist::trap($ip);
        $this->sendEmail($ip, $whois);
      }
      $this->forbidden($ip, $whois);
    }
  }

  private static function whitelisted($ua) {
    return preg_match("/(aolbuild|baidu|bingbot|bingpreview|msnbot|duckduckgo|adsbot-google|googlebot|mediapartners-google|teoma|slurp|yandex)/i", $ua);
  }

  private function sendEmail(IPAddress $ip, $whois) {
    $lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $email = Configuration::get('PS_SHOP_EMAIL');
    $data = [
      '{ip}' => $ip->getAddress(),
      '{whois}' => $whois
    ];
    $dir =  BLACKHOLE_BOTS_ROOT . DIRECTORY_SEPARATOR . 'mails' . DIRECTORY_SEPARATOR;
    Mail::Send($lang, 'blackhole', Mail::l('Bad Bot Alert!', $lang), $data, $email, null, null, null, null, null, $dir, false);
  }

  // business logic
  public function hookDisplayHeader($params) {
    $ip = IPAddress::get();
    if ($ip->isValid() && BlackholeBlacklist::inBlacklist($ip)) {
      $this->forbidden($ip, Whois::getInfo($ip));
    }
  }

  public function hookDisplayFooter() {
    return '<div style="display:none"><a rel="nofollow" href="'.$this->getTrapUrl().'">Do NOT follow this link or you will be banned from the site!</a></div>';
  }


  public function hookModuleRoutes($params) {
    return [
      'blackholebots' => [
        'controller' => 'blackhole',
        'rule' => 'blackhole{whatever}',
        'keywords' => [
          'whatever' => ['regexp' => '.*', 'param' => 'whatever'],
        ],
        'params' => [
          'fc' => 'module',
          'module' => 'blackholebots',
          'controller' => 'blackhole'
        ]
      ]
    ];
  }

  public function forbidden(IPAddress $ip, $whois) {
    header('HTTP/1.0 403 Forbidden');
    $this->context->smarty->assign([
      'css' => $this->_path . '/views/css/blackhole.css',
      'ip' => $ip->getAddress(),
      'whois' => $whois
    ]);
    echo $this->display(BLACKHOLE_BOTS_ROOT, 'blackhole.tpl');
    exit();
  }

}
