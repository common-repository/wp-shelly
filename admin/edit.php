<?php
use \SOSIDEE_SHELLY\DB as DB;

$plugin = \SOSIDEE_SHELLY\SosPlugin::instance();
$form = $plugin->formDeviceEdit;
$dev_id = $form->getId();

$title = 'Device Edit';
if ( $dev_id == 0 ) {
    $title = 'New ' . $title;
}
echo '<h1>'.  esc_html( $title ) . '</h1>';

?>

<div class="wrap">

<?php $plugin::msgHtml(); ?>

<?php $form->htmlOpen(); ?>
    <table class="form-table wso" role="presentation">
        <tbody>
        <tr>
        <tr>
            <th scope="row" class="">Description</th>
            <td class="middled">
                <?php $form->htmlDescription(); ?>
                <span class="note">(max. <?php echo DB\Device::MAX_LENGTH; ?> chars.)</span>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">ID</th>
            <td class="middled">
                <?php $form->htmlSid(); ?>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Channel</th>
            <td class="middled">
                <?php $form->htmlChannel(); ?>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Authorized role/user</th>
            <td class="middled">
                <?php $form->htmlUser(); ?>
                <span class="note">users role or user authorized to control the device</span>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Shelly authorization key</th>
            <td class="middled">
                <?php $form->htmlKey(); ?>
                <span class="note">leave blank to use the general settings</span>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Shelly server URL</th>
            <td class="middled">
                <?php $form->htmlServer(); ?>
                <span class="note">leave blank to use the general settings</span>
            </td>
        </tr>
        </tbody>
    </table>

    <table role="presentation" style="margin-top: 1em;">
        <tbody>
        <tr>
            <td style="width: 120px;">
                <?php $form->htmlDelete( 'delete', 'Are you sure to delete it?' ); ?>
            </td>
            <td style="width: 120px;">
                <?php $form->htmlButtonLink(0); ?>
            </td>
            <td style="width: 120px;">
                <?php $form->htmlSave(); ?>
            </td>
        </tr>
        </tbody>
    </table>

<?php
    $form->htmlId();
    $form->htmlClose();
?>

    <?php if ( $dev_id > 0): ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Shortcode</th>
            <td>
                <input id="shelly-sc" type="text" value="<?php echo "[{$plugin::$SC_TAG} {$plugin::$SC_ID}=$dev_id]"; ?>" readonly aria-readonly="true" style="width: 10em; border: none; color: #0000cc; background-color: #fff; text-align: center;" onfocus="this.select();" />
                &nbsp;<button onclick="jsShellyCopy2CB();" style="vertical-align: bottom; cursor: pointer;" title="copy to clipboard"><span class="material-icons" style="vertical-align: bottom; color: #0000cc;">content_copy</span></button>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <p style="line-height: 24px;">
        <span class="material-icons" style="vertical-align: bottom; color: #dc3232;">warning</span>
        <strong><em>It's strongly advised to authorize only trusted users to access control of your device!</em></strong>
    </p>


    <hr>

    <table role="presentation" style="margin-top: 1em;">
        <tbody>
        <tr>
            <td style="width: 120px;">
                <?php $form->htmlButtonBack(); ?>
            </td>
        </tr>
        </tbody>
    </table>



</div>
