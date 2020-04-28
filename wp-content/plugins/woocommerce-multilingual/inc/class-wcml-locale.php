<?php

class WCML_Locale{

    private $woocommerce_wpml;
    private $sitepress;

	/**
	 * WCML_Locale constructor.
	 *
	 * @param woocommerce_wpml $woocommerce_wpml
	 * @param SitePress        $sitepress
	 */
	public function __construct( $woocommerce_wpml, $sitepress ) {
		$this->woocommerce_wpml = $woocommerce_wpml;
		$this->sitepress        = $sitepress;

		$this->load_locale();

		add_filter( 'locale', [ $this, 'update_product_action_locale_check' ] );
	}

	function load_locale() {
        return load_plugin_textdomain( 'woocommerce-multilingual', false, WCML_PLUGIN_FOLDER.'/locale' );
    }

    public function switch_locale( $lang_code = false ) {
        global $l10n, $st_gettext_hooks;
        static $original_l10n;
        if ( ! empty( $lang_code ) ) {

	        if ( null !== $st_gettext_hooks ) {
		        $st_gettext_hooks->switch_language_hook( $lang_code );
	        }

            $original_l10n = isset( $l10n[ 'woocommerce-multilingual' ] ) ? $l10n[ 'woocommerce-multilingual' ] : null;
            if ( $original_l10n !== null ) {
                unset( $l10n[ 'woocommerce-multilingual' ] );
            }

            return load_textdomain( 'woocommerce-multilingual',
                WCML_LOCALE_PATH . '/woocommerce-multilingual-' . $this->sitepress->get_locale( $lang_code ) . '.mo' );

        } else { // switch back
            $l10n[ 'woocommerce-multilingual' ] = $original_l10n;
        }
    }

    /* Change locale to saving language - needs for sanitize_title exception wcml-390 */
    public function update_product_action_locale_check( $locale ){
        if( isset($_POST['action']) && $_POST['action'] == 'wpml_translation_dialog_save_job' ){
            return $this->sitepress->get_locale( $_POST[ 'job_details' ][ 'target' ] );
        }
        return $locale;
    }
}