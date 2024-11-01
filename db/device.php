<?php
namespace SOSIDEE_SHELLY\DB;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

class Device extends Table
{
    public const MAX_LENGTH = 255;
    public const MAX_LENGTH_SID = 64;


    public $description;
    public $authkey;
    public $serverurl;
    public $sid;
    public $channel;
    public $user;

    public function __construct( $db ) {
        parent::__construct( $db, 'devices' );

        $this->description = $this->table->addVarChar('description', self::MAX_LENGTH);
        $this->authkey = $this->table->addVarChar('authkey', self::MAX_LENGTH);
        $this->serverurl = $this->table->addVarChar('serverurl', self::MAX_LENGTH);
        $this->sid = $this->table->addVarChar('sid', self::MAX_LENGTH_SID);
        $this->channel = $this->table->addTinyInteger('channel');
        $this->user = $this->table->addVarChar('user', self::MAX_LENGTH);

    }

    public function save( $data, $id = 0 ) {
        return $this->saveRecord( $data, $id );
    }

    public function cancel( $id ) {
        return $this->cancelRecord( $id );
    }

    public function load( $id ) {
        $table = $this->table;

        $results = $table->select( [
            $table->id->name => $id
        ] );

        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                sosidee_log("DB\Device.load($id) :: WpTable.select() returned a wrong array length: " . count($results) . " (requested: 1)" );
                return false;
            }
        } else {
            return false;
        }
    }

    public function list( $filters = [] ) {
        $table = $this->table;

        $where = [ $table->cancelled->name => false ];
        foreach ($filters as $key => $value) {
            $where[$key] = $value;
        }
        $orders = [ $table->description->name ];

        return $table->select( $where, $orders );
    }

    /*
    public function codeExists( $code, $id ) {
        $ret = false;
        $table = $this->table;
        $items = $this->list( [ $table->code->name => $code ] );
        if ( is_array($items) ) {
            for ( $n=0; $n<count($items); $n++ ) {
                if ( sosidee_strcasecmp( $items[$n]->code, $code) == 0 && $items[$n]->id != $id ) {
                    $ret = true;
                    break;
                }
            }
        } else {
            self::msgErr( "A problem occurred while reading the database." );
            sosidee_log("DB\Item.codeExists($code,$id): this.list() returned false.");
        }
        return $ret;
    }
    */

    /*
    public function clear() {
        $table = $this->table;
        $field = $table->cancelled->name;
        return $table->update( [ $field => true ], [ $field => false ] );
    }
    */

}