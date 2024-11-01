<?php
/**
	Plugin Name: WooCommerce Recargo de Equivalencia
	Description: Gestión simple del impuesto Recargo de Equivalencia para WooCommerce
	Plugin URI: http://fsxmart.factusol-woocommerce.es/2017/06/15/recargo-equivalencia-en-woocommerce/

	Version: 1.6.24
	
	Copyright: (c) RedondoWS
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	
	Author: RedondoWS
	Author URI: http://www.RedondoWS.com

	Text Domain: woocommerce-recargo-de-equivalencia
	Domain Path: /languages
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Miscellaneous functions

if (!function_exists('rws_r'))
{
  function rws_r($data, $flag = false){

    echo '<pre>';print_r($data);echo '</pre>';
    if ( $flag) die();
  }
}

if (!function_exists('wpbo_get_woo_version_number'))
{
function wpbo_get_woo_version_number() {
    // https://wpbackoffice.com/get-current-woocommerce-version-number/
    // If get_plugins() isn't available, require it
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
        // Create the plugins folder and file variables
	$plugin_folder = get_plugins( '/' . 'woocommerce' );
	$plugin_file = 'woocommerce.php';
	
	// If the plugin version number is set, return it 
	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		return $plugin_folder[$plugin_file]['Version'];

	} else {
	// Otherwise return null
		return NULL;
	}
}
}


/**
 * Check if WooCommerce is active
 **/
if (!function_exists('wse_active_nw_plugins'))
{
	// Get active network plugins - "Stolen" from Novalnet Payment Gateway
	function wse_active_nw_plugins() {
		if (!is_multisite())
			return false;
		$wse_activePlugins = (get_site_option('active_sitewide_plugins')) ? array_keys(get_site_option('active_sitewide_plugins')) : array();
		return $wse_activePlugins;
	}
}

if (   in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins')) 
	|| in_array('woocommerce/woocommerce.php', (array) wse_active_nw_plugins())) {

	class WC_Recargo_Equivalencia {

		public $version = '1.5.23';

		//Init the class
		public function __construct() {

			//Add admin menu item
			add_action('admin_menu', array($this, 'add_admin_menu_item'));

			//Process
			add_action('admin_init', array($this, 'woocommerce_recargo_de_equivalencia_page_process'));

			//Defaults - Options saved by the user
			$this->defaults=get_option('woocommerce_re_roles');
			if (!$this->defaults) {
				$this->defaults=array();
			}
			$this->taxes=get_option('woocommerce_re_taxes');
			if (!$this->taxes) {
				$this->taxes=array( 'IVA_normal'        => '-1', 'IVA_normal_RE'        => '-1',
									'IVA_reducido'      => '-1', 'IVA_reducido_RE'      => '-1',
									'IVA_superreducido' => '-1', 'IVA_superreducido_RE' => '-1',
					);
			}
		}

		// Check capabilities
		public function check_capabilities() {
			//Maybe a bit redundant
			return ( current_user_can('manage_options') || current_user_can('manage_woocommerce') );
		}

		// Add admin menu item
		public function add_admin_menu_item() {
			 if ($this->check_capabilities()) 
			 	add_submenu_page('woocommerce', 
			 		'Recargo de Equivalencia',
			 		'Recargo de Equivalencia', 
			 		'manage_woocommerce', 
			 		'woocommerce_recargo_de_equivalencia', 
			 		array($this, 'woocommerce_recargo_de_equivalencia_page'));
		}

		// Admin screen
		public function woocommerce_recargo_de_equivalencia_page() {
			$url = plugin_dir_url(__FILE__);
			?>
			<div class="wrap">
				<h2>Recargo de Equivalencia</h2>
				<p>Indique los Impuestos que pueden tener Recargo de Equivalencia. Seleccione los Grupos de Clientes / Roles a los que se aplicará el Recargo de Equivalencia. <a class="page-title-action flip_1">Ayuda</a></p>

<style>
.helpimage {
	padding: 8px;
	border-right:  #999999 8px outset; 
	border-bottom: #999999 8px outset; 
	border-left:   #000000 4px outset; 
	border-top:    #000000 4px outset;
}
</style>
<div style="display: none" class="panel_1">		
<div class="helpimage">	
	<p>Para configurar <b>WooCommerce Recargo de Equivalencia</b>, siga los pasos:</p>
	<ol>
		<li>Crear el Rol / Roles a los que se aplicará Recargo de Equivalencia. Por ejemplo: "Cliente con RE".<br />NOTA: puede que necesite un plugin adicional para hacer esto.</li>

		<li>Crear los Impuestos, en WooCommerce -> Ajustes -> Impuestos:<br /><br />

		<img src="<?php echo $url; ?>screenshot-1.png" title="Definir los Impuestos" class="helpimage" /><br /><br />

		<ul style="list-style-type: circle;">
			<li>IVA Normal<br /><br />

			<img src="<?php echo $url; ?>screenshot-2.png" title="Definir el IVA Normal" class="helpimage" /><br /><br />
			</li>

			<li>IVA Normal con RE<br /><br />

			<img src="<?php echo $url; ?>screenshot-3.png" title="Definir el IVA Normal con Recargo de Equivalencia" class="helpimage" /><br />
			</li>
		</ul>
		</li>

		<li>Si es necesario, repita el paso anterior para los "IVA Reducido" y "IVA Super-Reducido".</li>

		<li>Desde la página de administración del plugin se seleccionan los Impuestos y los Roles que están sujetos a Recargo de Equivalencia:<br /><br />

		<img src="<?php echo $url; ?>screenshot-4.png" title="Pantalla de administración del plugin" class="helpimage" /><br /><br />
		</li>
	</ol>
</div>		
	<hr style="height: 2px; margin-top: 12px; margin-bottom: 12px;" />
	<p><a class="page-title-action flip_1">Ocultar Ayuda</a></p>
	<hr style="height: 2px; margin-top: 12px; margin-bottom: 12px;" />
</div>				

				<form method="post" id="woocoomerce-stock-export-form" action="">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row" class="titledesc">Impuestos</th>
								<td>
									<div>
										IVA Normal: <?php echo $this->get_current_taxes_selector( "IVA[IVA_normal]", $this->taxes['IVA_normal'] ); ?> &nbsp; :: &nbsp; 
										IVA Normal con RE: <?php echo $this->get_current_taxes_selector( "IVA[IVA_normal_RE]", $this->taxes['IVA_normal_RE'] ); ?>
									</div>
									<hr />
									<div>
										IVA Reducido: <?php echo $this->get_current_taxes_selector( "IVA[IVA_reducido]", $this->taxes['IVA_reducido'] ); ?> &nbsp; :: &nbsp; 
										IVA Reducido con RE: <?php echo $this->get_current_taxes_selector( "IVA[IVA_reducido_RE]", $this->taxes['IVA_reducido_RE'] ); ?>
									</div>
									<hr />
									<div>
										IVA Super-Reducido: <?php echo $this->get_current_taxes_selector( "IVA[IVA_superreducido]", $this->taxes['IVA_superreducido'] ); ?> &nbsp; :: &nbsp; 
										IVA Super-Reducido con RE: <?php echo $this->get_current_taxes_selector( "IVA[IVA_superreducido_RE]", $this->taxes['IVA_superreducido_RE'] ); ?>
									</div>
									<hr />
								</td>
							</tr>
							<tr>
								<th scope="row" class="titledesc">Grupos de Clientes / Roles</th>
								<td>
									<?php
									foreach( $this->get_current_roles() as $role_value => $role_name ) {
										?>
										<div>
													<input type="checkbox" name="woocommerce_re_roles[]" id="export_fields_options_<?php echo $role_value; ?>" value="<?php echo $role_value; ?>"<?php if ( in_array($role_value, $this->defaults) ) echo ' checked="checked"'; ?>/> <?php echo $role_name; ?>
										</div>
										<?php
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>
					<?php submit_button( 'Guardar', 'primary', 'woocoomerce_recargo_de_equivalencia_button'); ?>
				</form>
			</div>

<script type="text/javascript">
	// https://crunchify.com/jquery-very-simple-showhide-panel-on-mouse-click-event/
	jQuery(document).ready(function() {

	//	jQuery(".panel_1").slideToggle("slow");

		jQuery(".flip_1").click(function() {
			jQuery(".panel_1").slideToggle("slow");
			jQuery('html, body').animate({scrollTop : 0},800);
			return false;
		});

		//Check to see if the window is top if not then display button
		jQuery(window).scroll(function(){
			if (jQuery(this).scrollTop() > 100) {
				jQuery('.scrollToTop').fadeIn();
			} else {
				jQuery('.scrollToTop').fadeOut();
			}
		});
		
		//Click event to scroll to top
		jQuery('.scrollToTop').click(function(){
			jQuery('html, body').animate({scrollTop : 0},800);
			return false;
		});

	});
</script>
			<?php
		}

		// Admin screen - export
		public function woocommerce_recargo_de_equivalencia_page_process() {
			global $plugin_page;

			if ($plugin_page=='woocommerce_recargo_de_equivalencia' && $this->check_capabilities()) {
				if (isset($_POST['woocoomerce_recargo_de_equivalencia_button'])) {
					update_option( 'woocommerce_re_roles', isset($_POST['woocommerce_re_roles']) ? $_POST['woocommerce_re_roles'] : array() );
					$this->defaults=get_option('woocommerce_re_roles');
					update_option( 'woocommerce_re_taxes', $_POST['IVA'] );
					$this->taxes=get_option('woocommerce_re_taxes');
					$dictio = array( $this->taxes['IVA_normal']        => $this->taxes['IVA_normal_RE'] ,
									 $this->taxes['IVA_reducido']      => $this->taxes['IVA_reducido_RE'] ,
									 $this->taxes['IVA_superreducido'] => $this->taxes['IVA_superreducido_RE'] ,
						);
					update_option( 'woocommerce_re_taxes_dictio', $dictio );
				}
			}
		}

		// get Roles
		public function get_current_roles() {
			// 
			// global $wp_roles;

			// if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();
				$roles = $wp_roles->get_names();

			// 	foreach ($roles as $role_value => $role_name) {
			// 		echo '<p><input type="checkbox" value="' . $role_value . '">'.$role_name.'</p>';
			//   	}

			return $roles;
		}

		// get Taxes
		public function get_current_taxes_selector( $selector_id, $val='-1' ) {
			// 
			$options = array();

			$tax_classes = WC_Tax::get_tax_classes();

			if ( $tax_classes )
				foreach ( $tax_classes as $class ) {
					$options[ sanitize_title( $class ) ] = esc_html( $class );
				}

			$opt = '';
			foreach ( $options as $key => $value ) {
				$opt .= '<option '. $this->fsx_selected( esc_attr( $key ) ,$val).' value="' . esc_attr( $key ) . '">' . $value . '</option>';
			}

			$selector = '<select id="'.$selector_id.'" name="'.$selector_id.'">
					      	<option value="-1">-- Seleccione --</option>
					      	<option '. $this->fsx_selected( esc_attr( '' ) ,$val).' value="">'.__( 'Standard', 'woocommerce' ).'</option>
					      	'.$opt.'
					     </select>';	

			return $selector;
		}

		// Check 
		public function fsx_selected( $selected, $current = true ) {
			return selected( $selected, $current, false ) ? 'selected="selected"' : '' ;
		}

	}	// class WC_Recargo_Equivalencia ENDS
	

	if (is_admin()) {
		$wse = new WC_Recargo_Equivalencia();
	}


// Do the Mambo!
if (!function_exists('woocommerce_version_check'))
{
function woocommerce_version_check( $version = '3.0' ) {
	// https://gist.github.com/hlashbrooke/9133402
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}
	}
	return false;
}
}

function wc_recargo_de_equivalencia( $tax_class, $product ) {  

	foreach ( get_option('woocommerce_re_roles', array()) as $role ) {
		
		if ( current_user_can( $role ) ) {  
			// $tax_class = 'R.E.';
			// ToDo: if ($tax_class == 'IVA Normal') $tax_class == 'IVA Normal + RE';
			$retaxes=get_option('woocommerce_re_taxes_dictio');
			if ( isset( $retaxes[$tax_class] ) ) $tax_class = $retaxes[$tax_class];
			break;
		}  

	}

 	return $tax_class;  
}

// $tax_filter = version_compare( WC_VERSION, '3.0', '>=' ) ? 'woocommerce_product_get_tax_class' : 'woocommerce_product_tax_class';
// $tax_filter = woocommerce_version_check( ) ? 'woocommerce_product_get_tax_class' : 'woocommerce_product_tax_class';
$tax_filter = version_compare( wpbo_get_woo_version_number(), '3.0', '>=' ) ? 'woocommerce_product_get_tax_class' : 'woocommerce_product_tax_class';

add_filter( $tax_filter, 'wc_recargo_de_equivalencia', 1, 2 );

if ( version_compare( wpbo_get_woo_version_number(), '3.0', '>=' ) ) 
	add_filter( 'woocommerce_product_variation_get_tax_class', 'wc_recargo_de_equivalencia', 1, 2 );


	/* If you're reading this you must know what you're doing ;-) Greetings from sunny Alcafrán! */

}
