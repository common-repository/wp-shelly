<?php
namespace SOSIDEE_SHELLY\SOS\WP\Elementor;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

class Handler
{
    public $class;
    public $native;

    public function __construct( $class ) {
        $this->class = $class;
        $this->native = null;
    }
}