<?php
namespace SOSIDEE_SHELLY\SRC;
use \Elementor as NativeElementor;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

class Widget extends \SOSIDEE_SHELLY\SOS\WP\Elementor\Widget
{
    const SELECT_CAPTION = '- select -';

    protected $ctrSelect;

    public function setKey() {
        $this->key = self::plugin()->key . '_ew' ;
    }

    private $sc_tag;
    private $sc_id;

    public function initialize() {
        parent::initialize();

        //$this->getDeviceId();
        $plugin = self::plugin();
        $this->sc_tag = $plugin::$SC_TAG;
        $this->sc_id = $plugin::$SC_ID;

        $this->title = 'WP Shelly';
        $this->section->title = $this->title;
        $this->icon = 'eicon-cloud-check';

        $options = [ '0' => self::SELECT_CAPTION];
        $devices = $plugin->database->devices->list();
        if ( is_array($devices) ) {
            for ( $n=0; $n<count($devices); $n++ ) {
                $options[$devices[$n]->id] = $devices[$n]->description;
            }
        }

        $this->ctrSelect = $this->addControl('Device:', NativeElementor\Controls_Manager::SELECT );
        $this->ctrSelect->options = $options;
        $this->ctrSelect->default = '0';

    }

    private function getShortcode($id) {
        $ret = '<em>select a device</em>';
        if ($id > 0) {
            $ret = "[{$this->sc_tag} {$this->sc_id}=$id]";
        }
        return $ret;
    }

    protected function render() {
        parent::render();

        $this->add_inline_editing_attributes( $this->ctrSelect->key, 'none' );
        $settings = $this->get_settings_for_display();
        $id = $settings[ $this->ctrSelect->key ];
        echo '<shelly ' . $this->get_render_attribute_string( $this->ctrSelect->key ) . '>' . $this->getShortcode($id) . '</shelly>';
    }

}