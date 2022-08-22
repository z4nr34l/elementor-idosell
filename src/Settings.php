<?php

namespace ElementorIdosell;

class Settings
{

  private $options;

	public function successNotice() {
		?>
    <div class="notice notice-success is-dismissible">
      <p><?php _e( 'Synchronized!', 'idosell' ); ?></p>
    </div>
		<?php
	}

	public function errorNotice() {
		?>
    <div class="notice notice-error is-dismissible">
      <p><?php _e( 'Synchronization failed!', 'idosell' ); ?></p>
    </div>
		<?php
	}


  public function __construct()
  {
    $this->options = get_option('idosell');
    add_action('admin_menu', [$this, 'addPluginPage']);
    add_action('admin_init', [$this, 'pageInit']);
    if(isset($_GET['sync'])) {
	    if($_GET['sync'] === "1") {
		    add_action( 'admin_notices', [$this, 'successNotice'] );
	    } else {
		    add_action( 'admin_notices', [$this, 'errorNotice'] );
	    }
    }
  }

  public function addPluginPage()
  {
    add_options_page(
      'IdoSell',
      'IdoSell',
      'manage_options',
      'idosell',
      [$this, 'createAdminPage']
    );
  }

  public function createAdminPage(): void
  {
    ?>

      <div class="wrap">
        <h2>Idosell integration by <a href="https://www.zanreal.pl/">Z4NR34L</a></h2>
        <form method="post" action="options.php">
          <?php
          settings_fields('idosell');
          do_settings_sections('idosell-admin');
          submit_button();
          ?>
        </form>
        <a onclick="window.open('<?php echo admin_url();?>options-general.php?page=idosell&idosell_import=1', '_blank', 'location=yes,height=350,width=350,scrollbars=yes,status=yes');" class="button button-secondary">Import</a>
        <p style="font-size: 12px;">WARNING! New window would be opened. Will close after synchronization finish.</p>
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
        'xml_url',
        'XML URL',
        [$this, 'xmlUrlCallback'],
        'idosell-admin',
        'main_section'
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
    if (isset($input['xml_url'])) {
        $sanitary_values['xml_url'] = sanitize_text_field($input['xml_url']);
    }
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

  public function xmlUrlCallback(): void
  {
      printf(
          '<input class="regular-text" type="text" name="idosell[xml_url]" id="idosell_xml_url" value="%s">',
          isset($this->options['xml_url']) ? esc_attr($this->options['xml_url']) : ''
      );
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
			'<input class="regular-text" type="password" name="idosell[password]" id="idosell_password" value="%s">',
			''
		);
	}

}
