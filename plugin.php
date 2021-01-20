<?php
/**
 * Plugin Name: VOS Simple Popup
 * Plugin URI:
 * Description: Simple popup for wordpress
 * Author: Vilhelm Sjölander
 * Author URI:
 * Version: 1.0.0
 * License:
 * License URI:
 *
 */

class VosSimplePopup
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public function init()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'vos_simple_popup_menu'));
            add_action('admin_init', array($this, 'vos_simple_popup_page_settings'));
            add_action('admin_enqueue_scripts', array($this, 'vos_simple_popup_load_scripts'));
        } else {
            if (get_option('vos-simple-popup-active')) {
                add_action('wp_enqueue_scripts', array($this, 'vos_simple_popup_enqueue_script'));
                add_action('wp_footer', array($this, 'vos_simple_popup_render_popup'));
                add_action('wp_print_styles', array($this, 'vos_simple_popup_enqueue_style'));
            }
        }
    }

    function vos_simple_popup_load_scripts()
    {
        wp_enqueue_media();
        wp_register_script(
            'vos-simple-popup-admin',
            plugins_url('/admin/vos-simple-popup.js', __FILE__),
            array('jquery'),
            'v1.0',
            true
        );
        wp_enqueue_script('vos-simple-popup-admin');
    }

    function vos_simple_popup_render_popup()
    {
        $this->options = [get_option('vos-simple-popup-options'), "url" => get_option('vos-simple-popup-background-image'), "takeover" => get_option('vos-simple-popup-takeover')];
        ?>
        <div class="vos-simple-popup<?php if ($this->options['takeover']) echo ' vos-simple-popup--takeover'; ?>">
            <div class="vos-simple-popup__backdrop"></div>
            <div class="vos-simple-popup__container">
                <div class="vos-simple-popup__modal"
                     style="background-image: url('<?php echo $this->options['url'] ?>');">
                    <div class="vos-simple-popup__content">
                        <?php echo $this->options[0]['content']; ?>
                    </div>
                    <div class="close"><?= ($this->options['takeover'] ?  'Till Restaurang Folkparken >>' :  '&times;'); ?></div>
                </div>
            </div>
        </div>
        <?php
    }

    function vos_simple_popup_render_options_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $this->options = get_option('vos-simple-popup-options');
        ?>
        <div class="wrap">
            <h1>VOS Simple Popup</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('my_option_group');
                do_settings_sections('vos-simple-popup-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    function vos_simple_popup_page_settings()
    {
        register_setting(
            'my_option_group',
            'vos-simple-popup-options'
        );

        register_setting(
            "my_option_group",
            "vos-simple-popup-active"
        );

        register_setting(
            "my_option_group",
            "vos-simple-popup-takeover"
        );

        register_setting(
            "my_option_group",
            "vos-simple-popup-background-image",
            array($this, "handle_file_upload")
        );

        register_setting(
            "my_option_group",
            "vos-simple-popup-storage"
        );

        add_settings_section(
            'vos-simple-popup-section-1',
            '',
            array(),
            'vos-simple-popup-settings'
        );

        add_settings_field(
            'vos-simple-popup-active',
            'Activate Popup',
            array($this, 'activate_popup_callback'),
            'vos-simple-popup-settings',
            'vos-simple-popup-section-1'
        );

        add_settings_field(
            'vos-simple-popup-storage',
            'Reset popup',
            array($this, 'storage_select_render'),
            'vos-simple-popup-settings',
            'vos-simple-popup-section-1'
        );

        add_settings_field(
            'content',
            'Popup Content',
            array($this, 'content_callback'),
            'vos-simple-popup-settings',
            'vos-simple-popup-section-1'
        );

        add_settings_field(
            'vos-simple-popup-background-image',
            'Background Image',
            array($this, 'background_image_callback'),
            'vos-simple-popup-settings',
            'vos-simple-popup-section-1'
        );

        add_settings_field(
            'vos-simple-popup-takeover',
            'Toggle takeover',
            array($this, 'takeover_callback'),
            'vos-simple-popup-settings',
            'vos-simple-popup-section-1'
        );
    }

    public function handle_file_upload($option)
    {
        if (!empty($_FILES["vos-simple-popup-background-image"]["tmp_name"])) {
            $urls = wp_handle_upload($_FILES["vos-simple-popup-background-image"], array('test_form' => FALSE));
            $temp = $urls["url"];
            return $temp;
        }

        return $option;
    }

    function storage_select_render()
    {
        ?>
        <select name="vos-simple-popup-storage">
            <option value="local" <?php selected(get_option('vos-simple-popup-storage'), "local"); ?>>Reset after 24h</option>
            <option value="session" <?php selected(get_option('vos-simple-popup-storage'), "session"); ?>>Reset every session</option>
        </select>
        <?php
    }

    function activate_popup_callback()
    {
        ?>
        <input type="checkbox" name="vos-simple-popup-active"
               value="1" <?php checked(1, get_option('vos-simple-popup-active'), true); ?> />
        <?php
    }

    function takeover_callback()
    {
        ?>
        <input type="checkbox" name="vos-simple-popup-takeover"
               value="1" <?php checked(1, get_option('vos-simple-popup-takeover'), true); ?> />
        <?php
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function content_callback()
    {
        printf(
            wp_editor($this->options['content'], 'vos-simple-popup-options[content]')
        );
    }

    public function background_image_callback()
    {
        $option = get_option('vos-simple-popup-background-image');

        echo '
        <button type="button" name="vos-simple-popup-background-image" class="upload_image_button button insert-media add_media">
            Ändra bakgrundsbild
        </button>
        <button type="submit" class="remove_image_button button">&times;</button>
        <div class="card" style="margin-bottom: 24px;">
            <img id="vos-simple-popup-background-image__image" src="' . $option . '" style="max-width: 100%;" />
        </div>
        <input type="hidden" name="vos-simple-popup-background-image" id="vos-simple-popup-background-image" value="' . $option . '"/>
        ';
    }


    function vos_simple_popup_menu()
    {
        add_menu_page('VOS Simple Popup Options', 'VOS Simple Popup', 'manage_options', 'vos-simple-popup', array($this, 'vos_simple_popup_render_options_page'), 'dashicons-admin-generic');
    }

    function vos_simple_popup_enqueue_script()
    {
        wp_register_script(
            'vos-simple-popup-js',
            plugins_url('/vos-simple-popup.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );
        $vos_simple_popup_data = array();
        $vos_simple_popup_data["storage"] = get_option('vos-simple-popup-storage');
        wp_localize_script( 'vos-simple-popup-js', 'js_data', $vos_simple_popup_data );
        wp_enqueue_script('vos-simple-popup-js', plugins_url('/vos-simple-popup.js', __FILE__));
    }

    function vos_simple_popup_enqueue_style()
    {
        wp_enqueue_style(
            'vos-simple-popup-css',
            plugins_url('/vos-simple-popup.css', __FILE__),
            false,
            '1.1'
        );
    }
}

$VosSimplePopup = new VosSimplePopup;

add_action('init', array($VosSimplePopup, 'init'));
