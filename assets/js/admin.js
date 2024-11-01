function jsShellyCopy2CB()
{
    let msg = 'Text copied into clipboard:\n\n';
    let txt = document.getElementById("shelly-sc");
    if (txt) {
        txt.select();
        txt.setSelectionRange(0, 99999); /* For mobile devices */
        if ( document.execCommand("copy") ) {
            msg += txt.value;
        } else {
            msg = "Your browser didn't copy the shortcode";
        }
        txt.blur();
    } else {
        msg = "Your browser couldn't copy the shortcode";
    }
    alert(msg);
}