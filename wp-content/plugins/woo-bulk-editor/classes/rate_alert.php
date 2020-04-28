<?php

class WOOBE_RATE_ALERT {

    protected $notes_for_free = true;

    public function __construct($for_free) {
        $this->notes_for_free = $for_free;
    }

    public function init() {
        if (is_admin()) {


            global $wp_version;
            if (version_compare($wp_version, '4.2', '>=')) {
                $hide_alert = get_option('woobe_rate_alert_', 0);

                if (!$hide_alert) {
                    $alert = intval(get_option('woobe_alert_rev', 0));

                    if (!$alert) {
                        update_option('woobe_alert_rev', time());
                        $alert = time();
                    }

                    if (time() >= ($alert + 86400 * 7)) {//7 days
                        add_action('admin_notices', array($this, 'woobe_alert'));
                        add_action('network_admin_notices', array($this, 'woobe_alert'));
                        add_action('wp_ajax_woobe_dismiss_rate_alert', array($this, 'woobe_dismiss_alert'));
                    }
                }
            }
        }
    }

    function woobe_alert() {

        if (isset($_GET['page']) AND $_GET['page'] == 'woobe') {
            $support_link = 'https://pluginus.net/support/forum/woobe-woocommerce-bulk-editor-professional/';
            ?>
            <div class="notice notice-warning is-dismissible" id="woobe_rate_alert" data-nonce="<?php echo json_encode(wp_create_nonce('woobe_dissmiss_rate_alert')) ?>">
                <p class="plugin-card-woocommerce-currency-switcher">
                    <?php printf(__("Hello! Looks like you using <b>WooCommerce Bulk Editor</b> for some time and I hope this software helped you with your business. If you happy with the plugin functionality and like WooCommerce Bulk Editor - rate please WOOBE with 5-stars, also share your opinion and ideas with us. Thank you!<br /> P.S. If you have troubles you can always ask %s.", 'woo-bulk-editor'), "<a href='{$support_link}' target='_blank'>" . __('support', 'woo-bulk-editor') . "</a>") ?>
                </p>

                <hr />

                <?php
                $link = 'https://codecanyon.net/downloads#item-21779835';
                if ($this->notes_for_free) {
                    $link = 'https://wordpress.org/support/plugin/woo-bulk-editor/reviews/#new-post';
                }
                ?>


                <table style="width: 100%; margin-bottom: 7px;">
                    <tr>
                        <td style="width: 33%; text-align: center;">
                            <a href="<?= $link ?>" target="_blank" class="woobe-panel-button dashicons-before dashicons-star-filled">&nbsp;<?php echo __('Write marvellous review about WOOBE features', 'woo-bulk-editor') ?></a>
                        </td>

                        <td style="width: 33%; text-align: center;">
                            <a href="javascript: jQuery('#woobe_rate_alert .notice-dismiss').trigger('click');void(0);" class="button button-large dashicons-before dashicons-thumbs-up">&nbsp;<?php echo __('It is done!', 'woo-bulk-editor') ?></a>
                        </td>

                        <td style="width: 33%; text-align: center;">
                            <a href="https://pluginus.net/support/forum/woobe-woocommerce-bulk-editor-professional/" target="_blank" class="woobe-panel-button dashicons-before dashicons-hammer"><?php echo __('WooCommerce Bulk Editor SUPPORT', 'woo-bulk-editor') ?></a>
                        </td>
                    </tr>
                </table>


            </div>
            <script>
                jQuery(function ($) {
                    var alert_w = $('#woobe_rate_alert');
                    alert_w.on('click', '.notice-dismiss', function (e) {
                        //e.preventDefault 

                        $.post(ajaxurl, {action: 'woobe_dismiss_rate_alert',
                            sec: <?php echo json_encode(wp_create_nonce('woobe_dissmiss_rate_alert')) ?>
                        });
                    });
                });
            </script>

            <?php
        }
    }

    public function woobe_dismiss_alert() {
        check_ajax_referer('woobe_dissmiss_rate_alert', 'sec');

        add_option('woobe_rate_alert_', 1, '', 'no');
        update_option('woobe_rate_alert_', 1);

        exit;
    }

}
