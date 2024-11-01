<?php
namespace SOSIDEE_SHELLY\FORM;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SRC as SRC;
use \SOSIDEE_SHELLY\SOS\WP\DATA as DATA;

class DeviceList extends Base
{

    public $items;

    public function __get($name) {
        switch($name) {
            case 'page':
                return $this->_plugin->pageDeviceList;
            case 'edit':
                return $this->_plugin->formDeviceEdit;
        }
    }

    public function __construct() {
        parent::__construct( 'itemList', [$this, 'onSubmit'] );

        $this->table = $this->_database->devices;

        $this->items = [];
    }

    protected function initialize() {
        if ( !$this->_posted ) {
            $this->loadItems();
        }
    }

    public function onSubmit() {

        if ( $this->_action == 'delete' ) {
            $table = $this->_database->devices;
            if ( $table->clear() ) {
                self::msgOk( 'All data have been removed.' );
            } else {
                self::msgErr( 'A problem occurred while removing the data.' );
            }

        }


    }

    public function loadItems() {
        $this->items = [];

        $results = $this->table->list();

        if ( is_array($results) ) {
            if ( count($results) > 0 ) {
                for ( $n=0; $n<count($results); $n++ ) {
                    /*
                    $results[$n]->creation_string = $results[$n]->creation->format( "Y/m/d H:i:s" );
                    $results[$n]->url_api = $this->_plugin->getApiUrl( $results[$n]->code );
                    $results[$n]->status_icon = SRC\QrCodeSearchStatus::getStatusIcon( !$results[$n]->disabled );
                    */
                }
            } else {
                self::msgInfo( "There's no devices in the database." );
            }
            $this->items = $results;
        } else {
            self::msgErr( 'A problem occurred.' );
        }
    }

    public function htmlButtonLink( $label ) {
        $url = $this->page->getUrl();
        parent::htmlLinkButton2( $url, $label, 'min-width:120px;' );
    }

    public function htmlButtonNew() {
        $this->edit->htmlButtonLink( 0 );
    }
    public function htmlButtonEdit( $id ) {
        $this->edit->htmlButtonLink( $id );
    }


}