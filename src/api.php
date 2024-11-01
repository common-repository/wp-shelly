<?php
namespace SOSIDEE_SHELLY\SRC;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_SHELLY\SOS\WP\User as WpUser;

class Api
{
    public static $plugin = null;

    private static function getDevice( &$http_status, &$result, $request ) {
        $ret = false;

        $result = (object) [
            'status' => 0
            ,'error' => true
            ,'message' => 'Unhandled problem.'
            ,'title' => null
            ,'icon' => 'error'
        ];

        $key_enc = $request->get_header('X-Shelly-Key');
        if ( !is_null($key_enc) ) {
            $id = Device::getIdByEncKey($key_enc);
            if ($id !== false) {
                $device = new Device(self::$plugin, $id);
                if ($device !== false) {
                    $user = WpUser::get();
                    if ( $device->isAuthorized($user) ) {
                        $ret = $device;
                    } else {
                        $http_status = 401;
                        $result->status = $http_status;
                        $result->message = 'You are not authorized.';
                        $result->title = 'WP Rest API response';
                        sosidee_log("SRC\Api.getDevice(): user (id={$user->id}) not authorized.");
                    }
                } else {
                    $http_status = 500;
                    $result->status = $http_status;
                    $result->message = 'Device not found in the Wordpress database: please contact the administrator.';
                    $result->title = 'WP Rest API response';
                    sosidee_log("SRC\Api.getDevice(): device not found for id={$id}.");
                }
            } else {
                $http_status = 500;
                $result->status = $http_status;
                $result->message = 'Device not found in the Wordpress database: please contact the administrator.';
                $result->title = 'WP Rest API response';
                sosidee_log("SRC\Api.getDevice(): user id not found in encrypted key ({$key_enc}).");
            }
        } else {
            $http_status = 400;
            $result->status = $http_status;
            $result->message = 'Device key not found: please contact the administrator.';
            $result->title = 'WP Rest API response';
            sosidee_log("SRC\Api.getDevice(): encrypted key not found in http header.");
        }

        return $ret;
    }

    private static function getBody( &$http_status, &$result, $url, $args ) {
        $ret = false;

        $response = wp_remote_post($url, $args);
        if ( !is_wp_error( $response ) ) {
            $http_status = wp_remote_retrieve_response_code($response);
            $body_json = wp_remote_retrieve_body($response);
            $body = Json::decode($body_json);
            if ( $body !== false ) {
                if ($http_status == 200) {
                    $ret = $body;
                } else {
                    $result->status = $http_status;
                    $result->title = 'Shelly server response';
                    $msg = '';
                    if ( property_exists($body, 'errors') ) {
                        $properties = get_object_vars( $body->errors );
                        foreach($properties as $_ => $value) {
                            if ($msg != '') {
                                $msg.= '\n';
                            }
                            $msg.= $value;
                        }
                    } else {
                        $msg = "The response body from the Shelly's server was invalid.";
                    }
                    if ($msg != '') {
                        $result->message = $msg;
                    }
                }
            } else {
                $result->status = $http_status;
                $result->message = "The response body from the Shelly's server was invalid.";
            }
        } else {
            $http_status = 500;
            $result->status = $http_status;
            $result->message = "The response from the Shelly's server was invalid.";
        }

        return $ret;
    }

    private static function send( $http_status, $response ) {
        $response->title = esc_js( $response->title );
        $response->message = esc_js( $response->message );

        return new \WP_REST_Response( $response, $http_status);
    }

    public static function check( \WP_REST_Request $request ) {
        /*
         * ret.status:
         *   1: on
         *   2: off
         *   6: offline
         */

        $ret = null;
        $http_status = 200;

        $device = self::getDevice($http_status, $ret, $request);
        if ( $device !== false ) {

            $url = "{$device->server}/device/status/";
            $args = [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
                ,'body' => [
                    'id' => $device->sid
                    ,'auth_key' =>$device->key
                ]
            ];

            $body = self::getBody($http_status, $ret, $url, $args);
            if ( $body !== false ) {
                if (property_exists($body, 'data') && property_exists($body->data, 'online')) {
                    if ($body->data->online == true) {
                        if (property_exists($body->data, 'device_status') && property_exists($body->data->device_status, 'relays')) {
                            $relays = $body->data->device_status->relays;
                            if (array_key_exists($device->channel, $relays)) {
                                $relay = $relays[$device->channel];
                                if (property_exists($relay, 'ison')) {
                                    $ret->status = $relay->ison ? 1 : 2;
                                    $ret->message = $relay->ison ? 'The device is on.' : 'The device is off.';
                                    $ret->error = false;
                                    $ret->icon = 'info';
                                    $ret->title = 'Shelly server response';
                                } else {
                                    $ret->message = "The response body from the Shelly's server was invalid.";
                                    sosidee_log("SRC\Api.check(): property 'ison' does not exists in object 'relay'.");
                                }
                            } else {
                                $ret->message = 'Invalid device channel: please contact the administrator.';
                                sosidee_log("SRC\Api.check(): channel value {$device->channel} not found in array 'relays'.");
                            }
                        } else {
                            $ret->message = "The response body from the Shelly's server was invalid.";
                            sosidee_log("SRC\Api.check(): property(ies) 'device_status' a/o 'relays' do(es) not exists in object 'body.data'.");
                        }
                    } else {
                        $ret->status = 6;
                        $ret->message = 'The device is offline.';
                        $ret->error = false;
                        $ret->icon = 'warning';
                        $ret->title = 'Shelly server response';
                        sosidee_log("SRC\Api.check(): property 'online' of object 'body.data' is false.");
                    }
                } else {
                    $ret->message = "The response body from the Shelly's server was invalid.";
                    sosidee_log("SRC\Api.check(): property 'data' does not exists in object 'body' or property 'online' does not exists in object 'body.data'.");
                }
            } else {
                sosidee_log("SRC\Api.check(): method apiGetBody($url) returned false.");
            }
        } else {
            sosidee_log("SRC\Api.check(): method apiGetDevice() returned false.");
        }

        return self::send($http_status, $ret);
    }

    public static function switch(\WP_REST_Request $request) {
        $ret = null;
        $http_status = 200;
        $device = self::getDevice($http_status, $ret, $request);
        if ( $device !== false ) {
            $action = $request->get_header('X-Shelly-Action');
            if ( !is_null($action) && in_array( strtolower($action), ['on', 'off']) ) {
                $url = "{$device->server}/device/relay/control/";
                $args = [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ]
                    ,'body' => [
                        'id' => $device->sid
                        ,'auth_key' => $device->key
                        ,'channel' => $device->channel
                        ,'turn' => strtolower($action)
                    ]
                ];

                $body = self::getBody($http_status, $ret, $url, $args);
                if ( $body !== false ) {
                    if ( property_exists($body, 'isok') && property_exists($body, 'data') ) {
                        if ( $body->isok == true ) {
                            if ( property_exists($body->data, 'device_id') ) {
                                if ( strcasecmp( $body->data->device_id, $device->sid ) == 0 ) {
                                    $ret->error = false;
                                    $ret->message = 'OK';
                                    $ret->icon = 'info';
                                } else {
                                    $ret->message = "The response body from the Shelly's server was invalid.";
                                    sosidee_log("SRC\Api.switch(): body.data.device_id ({$body->data->device_id}) != device.sid.value ({$device->sid->value}).");
                                }
                            } else {
                                $ret->message = "The response body from the Shelly's server was invalid.";
                                sosidee_log("SRC\Api.switch(): property 'device_id' does not exists in object 'body.data'.");
                            }
                        } else {
                            $ret->message = "The response body from the Shelly's server was invalid.";
                            sosidee_log("SRC\Api.switch(): body.isok is false.");
                        }
                    } else {
                        $ret->message = "The response body from the Shelly's server was invalid.";
                        sosidee_log("SRC\Api.switch(): property(ies) 'isok' a/o 'data' do(es) not exists in object 'body'.");
                    }
                } else {
                    sosidee_log("SRC\Api.switch(): method apiGetBody($url) returned false.");
                }
            } else {
                $http_status = 400;
                $ret->status = $http_status;
                $ret->message = 'Invalid device action: please contact the administrator.';
                $ret->title = 'WP Rest API response';
                sosidee_log("SRC\Api.switch(): invalid action: $action.");
            }
        }

        return self::send($http_status, $ret);
    }

}