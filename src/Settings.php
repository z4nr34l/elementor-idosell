<?php

namespace ElementorIdosell;

class Settings
{

    private $options;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addPluginPage']);
        add_action('admin_init', [$this, 'pageInit']);
    }

    public function addPluginPage()
    {
        add_options_page(
            'IdoSell', // page_title
            'IdoSell', // menu_title
            'manage_options', // capability
            'idosell', // menu_slug
            [$this, 'createAdminPage']
        );
    }

    public function createAdminPage(): void
    {
        $this->options = get_option('idosell'); ?>

        <div class="wrap">
            <h2>Idosell integration by <a href="https://www.zanreal.pl/">Z4NR34L</a></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('idosell');
                do_settings_sections('idosell-admin');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function pageInit()
    {
        register_setting(
            'idosell',
            'idosell',
            [$this, 'sanitize']
        );

        add_settings_section(
            'main_section',
            'Ogólne',
            [$this, 'sectionInfo'],
            'idosell-admin'
        );

        add_settings_field(
            'gateway_url',
            'Gateway URL',
            [$this, 'gatewayUrlCallback'],
            'idosell-admin',
            'main_section'
        );
        add_settings_field(
            'login',
            'Login',
            [$this, 'loginCallback'],
            'idosell-admin',
            'main_section'
        );
        add_settings_field(
            'password',
            'Password',
            [$this, 'passwordCallback'],
            'idosell-admin',
            'main_section'
        );

    }

    public function sanitize($input): array
    {
        $sanitary_values = [];
        if (isset($input['gateway_url'])) {
            $sanitary_values['gateway_url'] = sanitize_text_field($input['gateway_url']);
        }
        if (isset($input['login'])) {
            $sanitary_values['login'] = sanitize_text_field($input['login']);
        }
        if (isset($input['password'])) {
            $sanitary_values['password'] = sanitize_text_field($input['password']);
        }
        return $sanitary_values;
    }

    public function sectionInfo(): void
    {
    }

    public function gatewayUrlCallback(): void
    {
        printf(
            '<input class="regular-text" type="text" name="idosell[gateway_url]" id="idosell_gateway_url" value="%s">',
            isset($this->options['gateway_url']) ? esc_attr($this->options['gateway_url']) : ''
        );
    }
    public function loginCallback(): void
    {
        printf(
            '<input class="regular-text" type="text" name="idosell[login]" id="idosell_login" value="%s">',
            isset($this->options['login']) ? esc_attr($this->options['login']) : ''
        );
    }
    public function passwordCallback(): void
    {
        printf(
            '<input class="regular-text" type="password" name="idosell[password]" id="idosell_password">',
            isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    }

}
