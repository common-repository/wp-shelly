<?php
namespace SOSIDEE_SHELLY\FORM;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SOS\WP\DATA as DATA;
use \SOSIDEE_SHELLY\SRC as SRC;

class Config
{
    private $_plugin;

    private $native;

    public $key;
    public $server;

    public function __construct($cluster) {

        $this->_plugin = \SOSIDEE_SHELLY\SosPlugin::instance();

        $this->native = $cluster;
        $cluster->validate = array($this, 'validate');

        $this->key = $this->native->addField( 'auth-key', 'Shelly authorization key', '', DATA\FieldType::TEXT );

        $this->server = $this->native->addField( 'server-url', 'Shelly server URL', '', DATA\FieldType::TEXT );

    }

    private function initialize() {
        $this->key->class = 'large-text';
    }

    public function html() {
        $this->initialize();
        $this->native->html();
    }

    public function setPage($page) {
        $this->native->setPage($page);
    }

    public function getField($key) {
        return $this->native->getField($key);
    }

    public function load() {
        $this->native->load();
    }

    /***
     * @param string $cluster_key key of the data cluster
     * @param array $inputs values sent by the user ( associative array [field key => input value] )
     * @return array $outputs values to be saved ( associative array [field key => output value] )
     */
    public function validate( $cluster_key, $inputs ) {
        $outputs = array();

        foreach ( $inputs as $field_key => $field_value ) {
            $field = $this->getField($field_key);
            if ( !is_null($field) ) {
                if ( $field->type == DATA\FieldType::SELECT ) {
                    $value = trim( sanitize_text_field( $field_value ) );
                    $outputs[$field_key] = $value;
                } else if ( $field->type == DATA\FieldType::NUMBER ) {
                    $value = intval( $field_value );
                    if ( $value < 0) {
                        $value = $field->getValue(); //previous value
                        $this->_plugin::msgErr( "{$field->title}: value is smaller than zero." );
                    }
                    $outputs[$field_key] = $value;
                } else if ( $field->type == DATA\FieldType::TEXTAREA ) {
                    $outputs[$field_key] = sanitize_textarea_field($field_value);
                } else {
                    $outputs[$field_key] = sanitize_text_field($field_value);
                }
            } else {
                $this->_plugin::msgErr( "Field '{$field_key}': not found!" );
            }
        }

        return $outputs;
    }

    public function insertOldData($key, $server) {
        $data = [
             $this->key->name => $key
            ,$this->server->name => $server
        ];
        update_option($this->native->key, $data);
    }

}