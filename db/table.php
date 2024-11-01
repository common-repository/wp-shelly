<?php

namespace SOSIDEE_SHELLY\DB;

class Table
{
    protected $table;

    public $id;
    public $cancelled;
    public $creation;

    public function __construct( $db, $name ) {
        $this->table = $db->addTable( $name );

        $this->id = $this->table->addID();
        $this->creation = $this->table->addDateTime('creation')->setDefaultValueAsCurrentDateTime();
        $this->cancelled = $this->table->addBoolean('cancelled')->setDefaultValue(false);
    }

    protected function saveRecord($data, $id) {
        if ( $id > 0 ) {
            return $this->table->update( $data, [ $this->table->id->name => $id ] );
        } else {
            return $this->table->insert( $data );
        }
    }

    public function cancelRecord( $id ) {
        $table = $this->table;
        return $table->saveRecord( [ $table->cancelled->name => true ], [ $table->id->name => $id ] );
    }

}