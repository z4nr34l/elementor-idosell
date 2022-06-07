***REMOVED***

***REMOVED***

class Settings
***REMOVED***

  private $options;

	public function successNotice() ***REMOVED***
		?>
    <div class="notice notice-success is-dismissible">
      <p>***REMOVED*** _e( 'Synchronized!', 'idosell' ); ?></p>
    </div>
		***REMOVED***
***REMOVED***

  public function __construct()
  ***REMOVED***
    $this->options = get_option('idosell');
    add_action('admin_menu', [$this, 'addPluginPage']);
    add_action('admin_init', [$this, 'pageInit']);
    if(isset($_GET['sync']) && $_GET['sync'] === 1) ***REMOVED***
	    add_action( 'admin_notices', [$this, 'successNotice'] );
    ***REMOVED***
  ***REMOVED***

  public function addPluginPage()
  ***REMOVED***
    add_options_page(
      'IdoSell',
      'IdoSell',
      'manage_options',
      'idosell',
      [$this, 'createAdminPage']
    );
  ***REMOVED***

  public function createAdminPage(): void
  ***REMOVED***
    ?>

      <div class="wrap">
        <h2>Idosell integration by <a href="https://www.zanreal.pl/">Z4NR34L</a></h2>
        <form method="post" action="options.php">
          ***REMOVED***
          settings_fields('idosell');
          do_settings_sections('idosell-admin');
          submit_button();
          ?>
        </form>
        <a href="***REMOVED*** echo admin_url();?>options-general.php?page=idosell&idosell_import=1" class="button button-secondary">Importuj</a>
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
          'xml_url',
          'XML URL',
          [$this, 'xmlUrlCallback'],
          'idosell-admin',
          'main_section'
      );

  ***REMOVED***

  public function sanitize($input): array
  ***REMOVED***
      $sanitary_values = [];
      if (isset($input['xml_url'])) ***REMOVED***
          $sanitary_values['xml_url'] = sanitize_text_field($input['xml_url']);
      ***REMOVED***
      return $sanitary_values;
  ***REMOVED***

  public function sectionInfo(): void
  ***REMOVED***
  ***REMOVED***

  public function xmlUrlCallback(): void
  ***REMOVED***
      printf(
          '<input class="regular-text" type="text" name="idosell[xml_url]" id="idosell_xml_url" value="%s">',
          isset($this->options['xml_url']) ? esc_attr($this->options['xml_url']) : ''
      );
  ***REMOVED***

***REMOVED***
