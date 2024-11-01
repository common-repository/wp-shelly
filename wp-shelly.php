<?php
/*
Plugin Name: WP Shelly
Version: 2.0.0
Description: Turns on/off your IoT devices via Shelly cloud API. Compatible with Elementor.
Author: SOSidee.com srl
Author URI: https://sosidee.com
Text Domain: wp-shelly
*/
namespace SOSIDEE_SHELLY;

( defined( 'ABSPATH' ) and defined( 'WPINC' ) ) or die( 'you were not supposed to be here' );
defined('SOSIDEE_SHELLY') || define('SOSIDEE_SHELLY', true);

require_once "loader.php";

\SOSIDEE_CLASS_LOADER::instance()->add( __NAMESPACE__, __DIR__ );

/**
 *  
 * Class of This Plugin *
 *
**/
class SosPlugin extends SOS\WP\Plugin
{

    public static $SC_TAG = 'shelly';
    public static $SC_ID = 'id';

    //database
    public $database;
    public $config;

    //forms
    public $formDeviceList;
    public $formDeviceEdit;

    //pages
    private $pageConfig;
    public $pageDeviceList;
    public $pageDeviceEdit;

    //api endpoints
    private $apiCheck;
    private $apiSwitch;

    protected function __construct() {
        parent::__construct();
        
        //PLUGIN KEY & NAME
        $this->key = 'sos-shelly';
        $this->name = 'WP Shelly';

    }

    protected function initialize() {
        parent::initialize();

        $this->addWidget( 'SRC\Widget' );

        $cluster = $this->addGroup('config', 'General settings');
        $this->config = new FORM\Config( $cluster );
        $this->database = new DB\Database();

    }

    protected function initializeBackend() {

        $this->pageDeviceList = $this->addPage('list' );
        $this->pageDeviceEdit = $this->addPage('edit' );
        $this->pageConfig = $this->addPage('config' );

        $this->formDeviceList = new FORM\DeviceList();
        $this->formDeviceList->addToPage( $this->pageDeviceList );

        $this->formDeviceEdit = new FORM\DeviceEdit();
        $this->formDeviceEdit->addToPage( $this->pageDeviceEdit );

        //add menu item
        $this->menu->icon = '-cloud';
        $this->menu->add( $this->pageDeviceList, 'Devices' );
        $this->menu->add( $this->pageConfig, 'Settings' );
        $this->menu->addHidden( $this->pageDeviceEdit );

        $this->addScript('admin', false);
        $this->addStyle('admin');
        $this->addGoogleIcons();

    }

    protected function initializeFrontend() {

        //create the shortcode
        $this->addShortCode( self::$SC_TAG, array($this, 'handleShortcode') );

        $this->initializeApi();

    }

    protected function initializeApi() {
        SRC\Api::$plugin = $this;
        //create the rest api endpoints
        $this->apiCheck = $this->addApiGet('shelly/chk', '\SOSIDEE_SHELLY\SRC\Api::check' );
        $this->apiSwitch = $this->addApiGet('shelly/swt', '\SOSIDEE_SHELLY\SRC\Api::switch' );

    }

    protected function hasShortcode( $tag, $attributes ) {
        //add scripts and styles
        $this->addGoogleIcons();
        $this->addSweetAlert();
        $this->addScript('common')->html();

        $this->addStyle('button')->html();
    }

    private function localizeScripts() {
        $data = [
             'URL_CHK' => $this->apiCheck->getUrl()
            ,'URL_SWT' => $this->apiSwitch->getUrl()
            ,'API_NONCE' => SOS\WP\API\EndPoint::getNonce()
        ];
        $this->addLocalizedScript( 'shelly_localize', $data);
    }

    private function getOutput( $html, $js , $tmp_key ) {
        static $htmlCounter = 0;
        $htmlCounter++;

        $substitutions = [
             '§DEV-TMP-KEY§' => $tmp_key
            ,'§BTN-SWT-ID§' => $this->key . '_btn-swt-' . $htmlCounter
            ,'§BTN-CHK-ID§' => $this->key . '_btn-chk-' . $htmlCounter
        ];

        $searches = array_keys($substitutions);
        $replaces = array_values($substitutions);

        $html = str_replace($searches, $replaces, $html);
        $js = str_replace($searches, $replaces, $js);

        $this->addInlineScript($js);

        return wp_kses_post( $html );
    }

    public function handleShortcode( $args, $content, $tag ) {
        static $localized = false;

       $ret = '';

        $device = null;
        $user_is_authorized = false;
        $user = SOS\WP\User::get();
        if ( $user->id > 0 ) {
            //check input
            $id = isset( $args[self::$SC_ID] ) ? intval($args[self::$SC_ID]) : 0;
            if ($id > 0) {
                $device = new SRC\Device($this, $id);
                if ($device !== false) {
                    $user_is_authorized = $device->isAuthorized($user);
                } else {
                    //device not loaded
                    $msg = "<!-- {$this->name} -->";
                    $msg .= "<pre><em>we've had a problem here</em><br>[$tag";
                    foreach ($args as $key => $value) {
                        $msg .= " {$key}=\"{$value}\"";
                    }
                    $msg .= "]<br><em>invalid device id</em></pre>";
                    $ret = wp_kses_post( $msg );
                }
            } else {
                //problem with the input data from the admin dashboard
                $msg = "<pre><em>we've had a problem here</em><br>[$tag";
                foreach ($args as $key => $value) {
                    $msg .= " {$key}=\"{$value}\"";
                }
                $msg .= "]<br><em>invalid attribute a/o value</em></pre>";
                $ret = wp_kses_post( $msg );
            }
        }

        if ( $user_is_authorized ) {
            if ( $localized == false ) {
                $this->localizeScripts();
                $localized = true;
            }

            //prepare output
            $html = self::loadAsset('device.html');
            $js = self::loadAsset('device.js');
            if( $html !== false && $js !== false ) {
                $key_enc = $device->getEncryptedKey();
                $ret = $this->getOutput( $html, $js, $key_enc ); //, $channel
            } else {
                //files not loaded (plugin problem)
                $msg = "<!-- {$this->name} -->";
                $msg .= "<pre><em>one or more files have not been found in this server...</em></pre>";
                $ret = wp_kses_post( $msg );
            }
        } else {
            if ($ret == '') {
                //user not authorized (and no errors): display nothing
                $msg = "<!-- {$this->name} : user not authorized -->";
                $ret =  wp_kses_post( $msg );
            }
        }

        return apply_filters( 'handleShortcode', $ret );
   }

    public function onDatabaseUpdate() {
        $key = $this->key . '_checked-old';
        if ( get_option( $key ) === false ) {
            $device = get_option('sos-shelly_dev1');
            if ( $device !== false ) {
                $table = $this->database->devices;
                $recs = $table->list();
                if ( is_array($recs) && count($recs) == 0) {
                    $this->config->insertOldData( $device['authkey'], $device['serverurl'] );
                    $old_data = [
                         $table->description->name => 'device #1'
                        ,$table->authkey->name => ''
                        ,$table->serverurl->name => ''
                        ,$table->sid->name => $device['sid']
                        ,$table->channel->name => $device['channel']
                        ,$table->user->name => $device['user']
                    ];
                    if ( $table->save( $old_data) ) {
                        $msg = 'Old data have been saved, but you need to modify the shortcode with the new value: ';
                        $msg .= '[' . self::$SC_TAG . ' ' . self::$SC_ID  . '=1]';
                        self::msgWarn($msg);
                        self::msgHtml();
                    }
                }
            }
            update_option($key, true, false);
        }
    }


}


/**
 * DO NOT CHANGE ANYTHING BELOW THIS LINE UNLESS YOU KNOW WHAT YOU'RE DOING *
**/
$plugin = SosPlugin::instance(); //the class must be the one defined in this file
$plugin->run();
