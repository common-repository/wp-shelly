<?php
namespace SOSIDEE_SHELLY\SRC;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

/**
 * Class to manage a Shelly device
 */
class Device
{
    use \SOSIDEE_SHELLY\SOS\WP\DATA\Encryption;

    public static $KEY_AUTH_USER_REGISTERED = '*';
    public static $KEY_AUTH_USER = 'U:';
    public static $KEY_AUTH_ROLE = 'R:';


    //private $plugin;

    private $id;
    public $key;
    public $server;
    public $sid;
    public $channel; // device channel (0-3)
    public $user;

    public function __construct($plugin = null, $id = 0) {
        $this->id = null;
        $this->key = null;
        $this->server = null;
        $this->sid = null;
        $this->channel = null;
        $this->user = null;

        if ( !is_null($plugin) ) {

            if ( $id > 0 ) {
                $data = $plugin->database->devices->load( $id );
                if ( $data !== false ) {
                    $this->id = $data->id;
                    $this->key = $data->authkey;
                    $this->server = $data->serverurl;
                    $this->sid = $data->sid;
                    $this->channel = $data->channel;
                    $this->user = $data->user;

                    if ( $this->key == '' ) {
                        $plugin->config->key->load();
                        $this->key = $plugin->config->key->value;
                    }
                    if ( $this->server == '' ) {
                        $plugin->config->server->load();
                        $this->server = $plugin->config->server->value;
                    }
                    $this->server = rtrim( $this->server,  '/');
                }
            }
        }


    }

    public function isAuthorized( $user ) {
        $ret = false;
        if ( $user->id > 0 ) {
            if ( $this->user != self::$KEY_AUTH_USER_REGISTERED ) {
                if ( sosidee_str_starts_with( $this->user, self::$KEY_AUTH_ROLE ) ) {
                    $auth_role = sosidee_str_remove( self::$KEY_AUTH_ROLE, $this->user );
                    foreach ( $user->roles as $role ) {
                        if ( $role == $auth_role ) {
                            $ret = true;
                            break;
                        }
                    }
                } else if ( sosidee_str_starts_with( $this->user, self::$KEY_AUTH_USER ) ) {
                    $auth_id = intval( sosidee_str_remove( self::$KEY_AUTH_USER, $this->user ) );
                    $ret = ( $user->id == $auth_id );
                }
            } else {
                $ret = true;
            }
        }
        return $ret;
    }

    public function getEncryptedKey() {
        $id = $this->id;
        $key = strlen($id) . '=' . $id . microtime();
        return $this->encrypt($key);
    }

    public static function getIdByEncKey( $enc_key ) {
        $ret = false;
        $me = new self();
        $key = $me->decrypt($enc_key);
        if ( $key !== false ) {
            $sep = '=';
            $index = strpos($key, $sep);
            if ( $index > 0 ) {
                $length = intval( substr($key, 0, $index) );
                $index += strlen($sep);
                $ret = intval( substr($key, $index, $length) );
            }
        }
        return $ret;
    }

}