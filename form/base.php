<?php
namespace SOSIDEE_SHELLY\FORM;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SOS\WP as SOS_WP;
use \SOSIDEE_SHELLY\SRC as SRC;

class Base extends \SOSIDEE_SHELLY\SOS\WP\DATA\Form
{
    protected $_database;
    protected $table;

    private static $userLoaded = false;
    protected static $userList = [];

    public function __construct($name, $callback = null) {
        parent::__construct( $name, $callback );

        $this->_database = $this->_plugin->database;
        $this->table = null;

        add_action('plugins_loaded', [$this, 'loadUserList']);

    }

    public function loadUserList() {
        if ( self::$userLoaded ) {
            return;
        }

        self::$userList = [
            SRC\Device::$KEY_AUTH_USER_REGISTERED => 'any registered user'
        ];

        $roles = SOS_WP\User::getRoles();

        $opt_roles = [ ];
        foreach ( $roles as $role ) {
            $opt_roles[ SRC\Device::$KEY_AUTH_ROLE . $role->name ] = $role->description;
        }
        self::$userList['Roles:'] = $opt_roles;

        $opt_users = [ ];
        $users = SOS_WP\User::getList();
        foreach ( $users as $user ) {
            $opt_users[ SRC\Device::$KEY_AUTH_USER . $user->id ] = "{$user->login} <{$user->email}>";
        }
        self::$userList['Users:'] = $opt_users;

        self::$userLoaded = true;
    }

    public function htmlRowCount( $count ) {
        if ( is_int($count) ) {
            echo '<div style="text-align:right;margin-right:2em;">' . wp_kses_post($count) . ' row(s)</div>';
        }
    }

    public static function getIcon( $label, $color = "", $title = "" ) {
        $color = $color != "" ? " color:$color;" : "";
        return '<i title="' . esc_attr($title) .'" class="material-icons" style="vertical-align: bottom; max-width: 1em; font-size: inherit; line-height: inherit;' . esc_attr($color) . '">' . esc_textarea($label) .'</i>';
    }

}