<?php
namespace SOSIDEE_SHELLY\DB;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SOS\WP\DATA as DATA;

class Database
{
    private $native;

    public $devices;

    public function __construct() {


        $this->native = new DATA\WpDatabase('sos_shy_');
        //Table::setDb( $this->native );

        // TABLE DEVICES
        $this->devices = new Device( $this->native );

        $this->native->create();
    }

    /*
    private function saveRecord($table, $data, $id) {
        if ( $id > 0 ) {
            return $table->update( $data, [ 'id' => $id ] );
        } else {
            return $table->insert( $data );
        }
    }

    public function loadDevice( $id ) {
        $table = $this->native->devices;

        $results = $table->select( [
            $table->id->name => $id
        ] );

        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                sosidee_log("Database.loadDevices($id) :: WpTable.select() returned a wrong array length: " . count($results) . " (requested: 1)" );
                return false;
            }
        } else {
            return false;
        }
    }

    public function loadDevices() {
        $table = $this->native->devices;

        $filters = [ $table->cancelled->name => false ];
        $orders = [ $table->code->name ];

        return $table->select( $filters, $orders );
    }

    public function checkItemCode( $code, $id ) {
        $table = $this->native->items;

    }

    */

}