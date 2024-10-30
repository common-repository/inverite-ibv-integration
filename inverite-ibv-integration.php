<?php
/**
 * Inverite IBV Integration
 *
 * @package           InveriteIbvIntegration
 * @author            Pier-Yves C Valade
 * @copyright         2023 Pier-Yves C Valade - pycvala.de
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Inverite IBV Integration
 * Description:       A simple way to add an Inverite Instant Banking Verification iFrame Integration to your Wordpress website.
 * Version:           1.0.7
 * Requires at least: 7.0.0
 * Requires PHP:      7.4.0
 * Author:            Pier-Yves C Valade
 * Author URI:        https://pycvala.de/
 * Text Domain:       inverite-ibv-integration
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://pycvala.de/
 */

function inverite_wp_init() {
  $strPluginSlug = basename(dirname(__FILE__));
  load_plugin_textdomain( 'inverite-iframe-plugin', false, $strPluginSlug.'/languages');
}
add_action('plugins_loaded', 'inverite_wp_init');

add_shortcode('inverite-iframe', 'inverite_iframe_shortcode_create');
function inverite_iframe_shortcode_create($atts = [], $content = null, $tag = ''){
  $atts = shortcode_atts( array(
      'site-id' => false,
      'sandbox' => false,
      'ref' => false
   ), $atts );
  $aParams = array(
    'ref' => (isset($_REQUEST['ref']))? strtolower(sanitize_text_field($_REQUEST['ref'])) : null,
    'email' => (isset($_REQUEST['email']))? sanitize_email($_REQUEST['email']) : null,
    'firstname' => (isset($_REQUEST['fn']))? sanitize_text_field($_REQUEST['fn']): null,
    'lastname' => (isset($_REQUEST['ln']))? sanitize_text_field($_REQUEST['ln']): null,
    'province' => 'ON'
  );
  if(!$aParams['ref'] && $atts['ref']){
    $aParams['ref'] = uniqid($atts['ref'].'_');
  }
  $aParams = array_filter($aParams);

  $strHtml = sprintf('<p>%s</p>', __('Please provide a site id to use inverite.', 'inverite-iframe-plugin'));

  if(isset($atts['site-id']) && $atts['site-id']){
    if(isset($aParams['email']) && $atts['site-id']){
      $aParams['site'] = $atts['site-id'];
      $aParams['referenceid'] = (isset($aParams['ref']))? $aParams['ref'] : uniqid($atts['site-id'].'_');
      $strIframeUrl = sprintf('%s?%s',
          ($atts['sandbox'])? 'https://sandbox.inverite.com/customer/web/create' : 'https://www.inverite.com/customer/web/create',
          http_build_query($aParams)
        );

      $strHtml = sprintf('<iframe src="%s" class="inverite-iframe" id="inverite-iframe"></iframe>', $strIframeUrl);
    } else {
      $strHtml = sprintf('
      <form action="" method="post" id="inverite-data-form" class="inverite-data-form">
        <input type="text" id="fn" name="fn" value="" placeholder="%s" class="form-field" />
        <input type="text" id="ln" name="ln" value="" placeholder="%s" class="form-field" />
        <input type="email" id="email" name="email" value="" placeholder="%s" class="form-field" />
        <input type="submit" id="submit" name="submit" value="%s" class="button" />
      </form>',
            __('First name', 'inverite-iframe-plugin'),
            __('Last name', 'inverite-iframe-plugin'),
            __('Email', 'inverite-iframe-plugin'),
            __('Complete my IBV', 'inverite-iframe-plugin')
          );
    }
  }
  return $strHtml;
}

add_action('wp_enqueue_scripts', 'inverite_enqueue_script');
function inverite_enqueue_script() {
  wp_register_style('inveritewp', plugins_url('/style.css', __FILE__), false, '1.0.0', 'all');
  wp_enqueue_style('inveritewp');
}
