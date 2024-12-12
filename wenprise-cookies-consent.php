<?php
/**
 * Plugin Name: Wenprise Cookies Consent
 * Plugin URI: 
 * Description: A simple WordPress cookie consent plugin with customizable notice text
 * Version: 1.0
 * Author: Wenprise
 * License: GPL v2 or later
 * Text Domain: wenprise-cookies-consent
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load text domain
function wprs_cc_load_textdomain() {
    load_plugin_textdomain(
        'wenprise-cookies-consent',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'wprs_cc_load_textdomain');

// Register plugin settings
function wprs_cc_register_settings() {
    register_setting('wprs_cc_options', 'wprs_cc_notice_text');
    add_option('wprs_cc_notice_text', __('This website uses cookies to improve your browsing experience. By continuing to use this site, you agree to our use of cookies.', 'wenprise-cookies-consent'));
}
add_action('admin_init', 'wprs_cc_register_settings');

// Add cookie notice to footer
function wprs_cc_add_cookie_notice() {
    if (!isset($_COOKIE['wprs_cc_notice_accepted'])) {
        $notice_text = get_option('wprs_cc_notice_text');
        ?>
        <style>
            .wprs-cc-notice {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(33, 33, 33, 0.95);
                color: #fff;
                padding: 1rem;
                z-index: 9999;
                box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            }

            .wprs-cc-notice-container {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .wprs-cc-notice-text {
                margin: 0;
                padding: 0;
                flex: 1;
                min-width: 300px;
                font-size: 14px;
                line-height: 1.5;
            }

            .wprs-cc-notice-buttons {
                display: flex;
                gap: 0.5rem;
            }

            .wprs-cc-accept-button,
            .wprs-cc-close-button {
                padding: 0.5em 1em;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
            }

            .wprs-cc-accept-button {
                background: #007bff;
                color: #fff;
            }

            .wprs-cc-accept-button:hover {
                background: #0056b3;
            }

            .wprs-cc-close-button {
                background: #6c757d;
                color: #fff;
            }

            .wprs-cc-close-button:hover {
                background: #5a6268;
            }

            @media (max-width: 768px) {
                .wprs-cc-notice-container {
                    flex-direction: column;
                    text-align: center;
                }
                
                .wprs-cc-notice-buttons {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>

        <div id="wprs-cc-notice" class="wprs-cc-notice">
            <div class="wprs-cc-notice-container">
                <p class="wprs-cc-notice-text">
                    <?php echo esc_html($notice_text); ?>
                </p>
                <div class="wprs-cc-notice-buttons">
                    <button onclick="wprsccAcceptCookies()" class="wprs-cc-accept-button">
                        <?php echo esc_html__('Accept', 'wenprise-cookies-consent'); ?>
                    </button>
                    <button onclick="wprsccCloseCookieNotice()" class="wprs-cc-close-button">
                        <?php echo esc_html__('Close', 'wenprise-cookies-consent'); ?>
                    </button>
                </div>
            </div>
        </div>

        <script>
        function wprsccAcceptCookies() {
            let date = new Date();
            date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
            document.cookie = "wprs_cc_notice_accepted=1; expires=" + date.toUTCString() + "; path=/";
            document.getElementById('wprs-cc-notice').style.display = 'none';
        }

        function wprsccCloseCookieNotice() {
            let date = new Date();
            date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
            document.cookie = "wprs_cc_notice_accepted=1; expires=" + date.toUTCString() + "; path=/";
            document.getElementById('wprs-cc-notice').style.display = 'none';
        }
        </script>
        <?php
    }
}
add_action('wp_footer', 'wprs_cc_add_cookie_notice');

// Add admin menu
function wprs_cc_add_admin_menu() {
    add_options_page(
        __('Wenprise Cookies Consent Settings', 'wenprise-cookies-consent'),
        __('Cookies Consent', 'wenprise-cookies-consent'),
        'manage_options',
        'wprs-cc-settings',
        'wprs_cc_settings_page'
    );
}
add_action('admin_menu', 'wprs_cc_add_admin_menu');

// Settings page
function wprs_cc_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['wprs_cc_notice_text'])) {
        update_option('wprs_cc_notice_text', sanitize_textarea_field($_POST['wprs_cc_notice_text']));
        ?>
        <div class="notice notice-success">
            <p><?php echo esc_html__('Settings saved.', 'wenprise-cookies-consent'); ?></p>
        </div>
        <?php
    }

    $notice_text = get_option('wprs_cc_notice_text');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Wenprise Cookies Consent Settings', 'wenprise-cookies-consent'); ?></h1>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wprs_cc_notice_text"><?php echo esc_html__('Notice Text', 'wenprise-cookies-consent'); ?></label>
                    </th>
                    <td>
                        <textarea name="wprs_cc_notice_text" id="wprs_cc_notice_text" rows="4" 
                                class="large-text"><?php echo esc_textarea($notice_text); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Customize the cookie consent notice text displayed to visitors.', 'wenprise-cookies-consent'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="description">
                <?php echo esc_html__('- Click "Accept" button: notice will not show for 30 days', 'wenprise-cookies-consent'); ?><br>
                <?php echo esc_html__('- Click "Close" button: notice will not show for 1 day', 'wenprise-cookies-consent'); ?>
            </p>

            <?php submit_button(__('Save Settings', 'wenprise-cookies-consent')); ?>
        </form>
    </div>
    <?php
}