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

  public function __construct()
  {
    $this->options = get_option('idosell');
    add_action('admin_menu', [$this, 'addPluginPage']);
    add_action('admin_init', [$this, 'pageInit']);
    if(isset($_GET['sync']) && $_GET['sync'] === 1) {
	    add_action( 'admin_notices', [$this, 'successNotice'] );
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
        <a href="<?php echo admin_url();?>options-general.php?page=idosell&idosell_import=1" class="button button-secondary">Importuj</a>
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

  }

  public function sanitize($input): array
  {
      $sanitary_values = [];
      if (isset($input['xml_url'])) {
          $sanitary_values['xml_url'] = sanitize_text_field($input['xml_url']);
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

}
