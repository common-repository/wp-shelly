<?php
namespace SOSIDEE_SHELLY\FORM;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SRC as SRC;
use \SOSIDEE_SHELLY\DB as DB;
use \SOSIDEE_SHELLY\SOS\WP\DATA as DATA;

class DeviceEdit extends Base
{
    const QS_ID = 'shy-id';

    private $id;
    private $description;
    private $key;
    private $server;
    private $sid;
    private $channel;
    private $user;

    public function __get($name) {
        switch($name) {
            case 'page':
                return $this->_plugin->pageDeviceEdit;
            case 'list':
                return $this->_plugin->formDeviceList;
        }
    }

    public function __construct() {
        parent::__construct( 'itemEdit', [$this, 'onSubmit'] );

        $this->table = $this->_database->devices;

        $this->id = $this->addHidden('id', 0);
        $this->description = $this->addTextBox('description', '');
        $this->key = $this->addTextBox('key', '');
        $this->server = $this->addTextBox('server', '');
        $this->sid = $this->addTextBox('sid', '');
        $this->channel = $this->addNumericBox('channel', 0);
        $this->user = $this->addSelect('user', '');

        $this->reset();
    }

    private function reset() {
        $this->id->value = 0;
        $this->description->value = 'device-' . time();
        $this->key->value = '';
        $this->server->value = '';
        $this->sid->value = '';
        $this->channel->value = 0;
        $this->user->value = SRC\Device::$KEY_AUTH_USER_REGISTERED;
    }

    public function getId() {
        return intval($this->id->value);
    }
    public function htmlId() {
        $this->id->html();
    }
    public function htmlKey() {
        $this->key->html( ['maxlength' => DB\Device::MAX_LENGTH] );
    }
    public function htmlDescription() {
        $this->description->html( ['maxlength' => DB\Device::MAX_LENGTH, 'class' => 'regular-text'] );
    }
    public function htmlServer() {
        $this->server->html( ['maxlength' => DB\Device::MAX_LENGTH] );
    }
    public function htmlSid() {
        $this->sid->html( ['maxlength' => DB\Device::MAX_LENGTH_SID] );
    }
    public function htmlChannel() {
        $this->channel->html([
             'min' => 0
            ,'max' => 3
            ,'class' => 'small-text'
        ]);
    }
    public function htmlUser() {
        $this->user->html( [ 'options' => self::$userList ] );
    }

    protected function initialize() {
        if ( !$this->_posted ) {
            $id = sosidee_get_query_var(self::QS_ID, 0);
            if ( $id > 0 ) {
                $this->load( $id );
            }
        }
    }

    public function load( $id ) {
        if ( $id > 0 ) {
            $data = $this->table->load( $id );
            if ( $data !== false ) {
                $this->id->value = $data->id;
                $this->description->value = $data->description;
                $this->key->value = $data->authkey;
                $this->server->value = $data->serverurl;
                $this->sid->value = $data->sid;
                $this->channel->value = $data->channel;
                $this->user->value = $data->user;
            } else {
                self::msgErr( "A problem occurred while reading the database." );
                sosidee_log("DeviceEdit.load({$id}): table.load() returned false.");
            }
        } else {
            self::msgErr( "A problem occurred: record id is invalid." );
            sosidee_log("DeviceEdit.load({$id}): invalid device id.");
        }
    }

    public function onSubmit() {
        $table = $this->table;
        $id = $this->getId();

        if ( $this->_action == 'save' ) {
            $save = true;

            $use_conf_msg = '';
            $this->key->value = trim( $this->key->value );
            if ( $this->key->value == '' ) {
                $use_conf_msg = 'Authorization key is empty.';
            }
            $this->server->value = sanitize_url( $this->server->value );
            if ( $this->server->value == '' ) {
                if ( $use_conf_msg != '') {
                    $use_conf_msg .= '<br>';
                }
                $use_conf_msg .= 'Server URL is empty.';
            }
            if ( $use_conf_msg != '') {
                $use_conf_msg .= '<br>(general settings value(s) will be used)';
            }
            $this->description->value = trim($this->description->value);
            if ( empty($this->description->value) || strcasecmp($this->description->value, SRC\Widget::SELECT_CAPTION) == 0 ) {
                $save = false;
                self::msgErr( 'Description is not valid.' );
            }
            $this->sid->value = trim( $this->sid->value );
            if ( $this->sid->value == '' ) {
                $save = false;
                self::msgErr( 'Device ID is empty.' );
            }
            $this->channel->value = intval( $this->channel->value );

            if ( $save ) {

                $data = [
                     $table->description->name => $this->description->value
                    ,$table->authkey->name => $this->key->value
                    ,$table->serverurl->name => $this->server->value
                    ,$table->sid->name => $this->sid->value
                    ,$table->channel->name => $this->channel->value
                    ,$table->user->name => $this->user->value
                ];

                $result = $table->save( $data, $id );
                if ( $result !== false ) {
                    if ( $result === true ) { //update
                        self::msgOk( 'Data have been saved.' );
                        if ( $use_conf_msg != '') {
                            self::msgInfo( $use_conf_msg );
                        }
                        $this->load( $id );
                    } else { //insert
                        $id = intval( $result );
                        if ( $id > 0 ) {
                            $this->load( $id );
                            self::msgOk( 'Data have been added.' );
                            if ( $use_conf_msg != '') {
                                self::msgInfo( $use_conf_msg );
                            }
                        } else {
                            self::msgErr( 'A problem occurred while adding the data.' );
                        }
                    }
                } else {
                    self::msgErr( 'A problem occurred while saving the data.' );
                }

            }

        } else if ( $this->_action == 'delete' ) {

            if ( $id > 0 ) {

                if ( $table->cancel( $id ) !== false ) {
                    $this->reset();
                    self::msgOk( 'Data have been deleted.' );
                } else {
                    self::msgErr( 'A problem occurred while deleting the item.' );
                }

            } else {
                self::msgWarn( 'Cannot delete data not saved yet.' );
            }

        } else {
            self::msgErr( "Invalid form action: {$this->_action}." );
        }
    }

    /*
    private function loadItem( $id ) {
        if ( $id > 0 ) {
            $item= $this->table->load( $id );
            if ( $item !== false ) {
                $this->id->value = $item->id;
                $this->code->value = $item->code;
                $this->description->value = $item->description;
                $this->price->value = $item->price;
                $this->unit->value = $item->unit;

            } else {
                self::msgErr( "A problem occurred while reading the database." );
            }

        } else {
            self::msgErr( "A problem occurred: record id is zero." );
        }
    }

    public function getUrl( $id = 0 ) {
        return $this->_plugin->pageItemEdit->getUrl( [self::QS_ID => $id] );
    }
    */

    public function htmlButtonLink( $id ) {
        $url = $this->page->getUrl( [self::QS_ID => $id] );
        if ( $id == 0 ) {
            parent::htmlLinkButton( $url, 'create new' );
        } else {
            parent::htmlLinkButton( $url, 'edit', DATA\FormButton::STYLE_SUCCESS );
        }
    }

    public function htmlButtonBack() {
        $this->list->htmlButtonLink('back to item list');
    }

}