***REMOVED***

***REMOVED***

class Settings
***REMOVED***

    private $options;

    public function __construct()
    ***REMOVED***
        add_action('admin_menu', [$this, 'addPluginPage']);
        add_action('admin_init', [$this, 'pageInit']);
    ***REMOVED***

    public function addPluginPage()
    ***REMOVED***
        add_options_page(
            'IdoSell', // page_title
            'IdoSell', // menu_title
            'manage_options', // capability
            'idosell', // menu_slug
            [$this, 'createAdminPage']
        );
    ***REMOVED***

    public function createAdminPage(): void
    ***REMOVED***
        $this->options = get_option('idosell'); ?>

        <div class="wrap">
            <h2>Idosell integration by <a href="https://www.zanreal.pl/">Z4NR34L</a></h2>
            <form method="post" action="options.php">
                ***REMOVED***
                settings_fields('idosell');
                do_settings_sections('idosell-admin');
                submit_button();
                ?>
            </form>
        </div>
    ***REMOVED*** ***REMOVED***

    public function pageInit()
    ***REMOVED***
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

    ***REMOVED***

    public function sanitize($input): array
    ***REMOVED***
        $sanitary_values = [];
        if (isset($input['gateway_url'])) ***REMOVED***
            $sanitary_values['gateway_url'] = sanitize_text_field($input['gateway_url']);
        ***REMOVED***
        if (isset($input['login'])) ***REMOVED***
            $sanitary_values['login'] = sanitize_text_field($input['login']);
        ***REMOVED***
        if (isset($input['password'])) ***REMOVED***
            $sanitary_values['password'] = sanitize_text_field($input['password']);
        ***REMOVED***
        return $sanitary_values;
    ***REMOVED***

    public function sectionInfo(): void
    ***REMOVED***
    ***REMOVED***

    public function gatewayUrlCallback(): void
    ***REMOVED***
        printf(
            '<input class="regular-text" type="text" name="idosell[gateway_url]" id="idosell_gateway_url" value="%s">',
            isset($this->options['gateway_url']) ? esc_attr($this->options['gateway_url']) : ''
        );
    ***REMOVED***
    public function loginCallback(): void
    ***REMOVED***
        printf(
            '<input class="regular-text" type="text" name="idosell[login]" id="idosell_login" value="%s">',
            isset($this->options['login']) ? esc_attr($this->options['login']) : ''
        );
    ***REMOVED***
    public function passwordCallback(): void
    ***REMOVED***
        printf(
            '<input class="regular-text" type="password" name="idosell[password]" id="idosell_password">',
            isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    ***REMOVED***

***REMOVED***
