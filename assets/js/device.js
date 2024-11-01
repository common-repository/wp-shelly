(function($){
    'use strict';
    $(document).ready(function()
    {
        if (!$.ajax) { return null; }

        let action = '';
        let chk_pars = {
             url: shelly_localize.URL_CHK
            ,key: '§DEV-TMP-KEY§'
            ,nonce: shelly_localize.API_NONCE
        };
        let swt_pars = {
             url: shelly_localize.URL_SWT
            ,key: chk_pars.key
            ,nonce: chk_pars.nonce
            ,action: action
        };

        let first_check = true; //note: the first check happens automatically when the page is loaded

        $('#§BTN-CHK-ID§').click( function() {
            $('#§BTN-SWT-ID§').text('rotate_right').addClass('off').removeClass('on').addClass('loader');
            jsShellyCheck(chk_pars, function(res){
                if (res.error == false) {
                    if (res.status == 1) {
                        action = 'off';
                        $('#§BTN-SWT-ID§').text('toggle_on').removeClass('loader').addClass('on').removeClass('off').attr('title', 'turn off');
                        if (!first_check) {
                            jsShellyAlert(res);
                        }
                    } else if (res.status == 2) {
                        action = 'on';
                        $('#§BTN-SWT-ID§').text('toggle_off').removeClass('loader').addClass('off').removeClass('on').attr('title', 'turn on');
                        if (!first_check) {
                            jsShellyAlert(res);
                        }
                    } else {
                        action = '';
                        $('#§BTN-SWT-ID§').text('link_off').removeClass('loader').removeClass('off').removeClass('on').attr('title', 'Check the device status before switching.');
                        if (!first_check) {
                            jsShellyAlert(res);
                        }
                    }
                } else {
                    jsShellyAlert(res);
                }
                first_check = false;
            });
            $('#§BTN-CHK-ID§').blur();
        });

        $('#§BTN-SWT-ID§').click( function() {

            if (action != 'on' && action != 'off') {
                jsShellyAlert( { message:'Check the device status before switching.', icon:'warning' } );
                return null;
            }

            $('#§BTN-SWT-ID§').text('rotate_right').addClass('off').removeClass('on').addClass('loader');
            swt_pars.action = action;
            jsShellySwitch(swt_pars, function(res){
                if (res.error == false)
                {
                    if (action == 'on')
                    {
                        action = 'off';
                        $('#§BTN-SWT-ID§').text('toggle_on').removeClass('loader').addClass('on').removeClass('off').attr('title', 'turn off');
                    }
                    else
                    {
                        action = 'on';
                        $('#§BTN-SWT-ID§').text('toggle_off').removeClass('loader').addClass('off').removeClass('on').attr('title', 'turn on');
                    }
                }
                else
                {
                    action = '';
                    $('#§BTN-SWT-ID§').text('link_off').removeClass('loader').removeClass('off').removeClass('on').attr('title', 'Check the device status before switching.');
                }
                jsShellyAlert(res);
            });
            $('#§BTN-SWT-ID§').blur();
        });

        $( '#§BTN-CHK-ID§' ).text('check').attr('title', 'check the device status').trigger( 'click' );
    });
})(jQuery);