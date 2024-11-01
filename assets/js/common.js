if(window.jQuery){
    (function($){
        'use strict';
        $(document).ready(function() {
            if ($.ajax) {
                $(document).bind("contextmenu", function (e) {
                    return false;
                });
            }
        });
    })(jQuery);
} else {
    jsShellyAlert( { message:'jQuery not found.', icon:'error'} );
}

function jsShellyLoadReturn(ret, res) {
    let props = Object.getOwnPropertyNames(ret);
    for (let n=0; n<props.length; n++) {
        let p = props[n];
        if ( res.hasOwnProperty(p) ) {
            ret[p] = res[p];
        }
    }
    return ret;
}

function jsShellyRequest(url, headers, callback) {
    if (typeof url != 'undefined' && typeof headers == 'object' && typeof callback === 'function' ) {
        let ret = { status:0, error: true, message: 'Unhandled Rest API problem.', title: null, icon: 'error' };
        jQuery.ajax({
            url: url
            ,type: "GET"
            ,headers: headers
        }).done(function( response, textStatus, xhr ) {
            try {
                ret = jsShellyLoadReturn(ret, response);
            }
            catch (ex) {
                ret.title = 'browser exception';
                ret.message = '(' + ex.name + ') ' + ex.message;
            }
        }).fail(function( xhr, status, errorThrown ) {
            ret.message = xhr.status + ': ' + errorThrown;
        }).always( function( resp, status ) {
            callback( ret );
        });
    } else {
        let msg = 'Invalid javascript parameter(s):';
        if (typeof url == 'undefined') {
            msg += '\nurl';
        }
        if (typeof headers != 'object') {
            msg += '\nheaders';
        }
        if (typeof callback !== 'function') {
            msg += '\ncallback';
        }
        jsShellyAlert( { message:msg, icon:'error' } );
    }
}
function jsShellyCheck(pars, callback) {
    let url = pars.url;
    let headers = { 'X-Shelly-Key': pars.key, 'X-WP-Nonce': pars.nonce };
    jsShellyRequest(url, headers, callback);
}

function jsShellySwitch(pars, callback) {
    let url = pars.url;
    let headers = { 'X-Shelly-Key': pars.key, 'X-WP-Nonce': pars.nonce, 'X-Shelly-Action': pars.action };
    jsShellyRequest(url, headers, callback);
}

function jsShellyAlert(alert) {
    if (typeof Swal !== 'undefined') {
        if ( !alert.message.includes('\n') ) {
            Swal.fire({
                 text: alert.message
                ,icon: alert.icon
                ,title: alert.title
            });
        } else {
            Swal.fire({
                 html: alert.message.replace(/\n/g, '<br>')
                ,icon: alert.icon
                ,title: alert.title
            });
        }
    } else {
        alert(alert.message);
    }
}
