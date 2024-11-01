<?php
use \SOSIDEE_SHELLY\SRC as SRC;

$plugin = \SOSIDEE_SHELLY\SosPlugin::instance();
$form = $plugin->formDeviceList;
$items = $form->items;

?>
<h1>Devices List</h1>

<div class="wrap">

<?php $plugin::msgHtml(); ?>

<?php $form->htmlOpen(); ?>

    <br>
    <?php $form->htmlRowCount(count($items)); ?>
    <table class="form-table wso bordered pad2p" role="presentation">
        <thead>
        <tr>
            <th scope="col" class="bordered middled centered" style="width: 70%;">Description</th>
            <th scope="col" class="bordered middled centered" style="width: 10%;">ID</th>
            <th scope="col" class="bordered middled centered" style="width: 10%;">Channel</th>
            <th scope="col" class="bordered middled centered" style="width: 10%;">
                <?php $form->htmlButtonNew(); ?>
            </th>
        </tr>
        </thead>
        <tbody>
<?php
if ( is_array($items) && count($items) > 0 ) {
    for ( $n=0; $n<count($items); $n++ ) {
        $item = $items[$n];
        $id = $item->id;
        $description = $item->description;
        $sid = $item->sid;
        $channel = $item->channel;
?>
        <tr>
            <td class="bordered middled centered"><?php echo esc_html( $description ); ?></td>
            <td class="bordered middled centered"><?php echo esc_html( $sid ); ?></td>
            <td class="bordered middled centered"><?php echo esc_html( $channel ); ?></td>
            <td class="bordered middled centered"><?php $form->htmlButtonEdit( $id ); ?></td>
        </tr>
<?php
    }
}
?>
        </tbody>
    </table>

<?php
$form->htmlClose();
?>

</div>
