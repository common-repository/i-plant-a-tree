<?php
/*
Plugin Name: I Plant A Tree
Text Domain: i-plant-a-tree
Plugin URI: https://lightframefx.de/projects/i-plant-tree-wordpress-plugin/?lang=en
Description: This plugin shows the count of planted trees via "I Plant A Tree", as well as saved CO2.
Author: Micha
Version: 1.7.3
Author URI: https://lightframefx.de
URI: https://lightframefx.de
Tags: ipat,widget,i plant a tree
Requires at least: 4.2.2
Tested up to: 5.8.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0
*/

/*
written 2015-2020 Michael Roth

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License Version 3 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the	GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die( 'You may not access this file directly.' );

function tl_save_error() {
	update_option( 'plugin_error',  ob_get_contents() );
}
add_action( 'activated_plugin', 'tl_save_error' );

if (!class_exists('ipat_widget_class')) {
	class ipat_widget_class {
		var $settings;

		function ipat_widget() {
			$this->getOptions();
			if (function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain('i-plant-a-tree', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/languages', dirname(plugin_basename( __FILE__ )).'/languages');
			}

			// Example Shortcode [date] add with: add_shortcode('date', array(&$this,'show_date'));
			// 			add_shortcode('ipat_widget', array(&$this,'ipat_widgetShortcode'));
			// 			add_shortcode( 'ipat_widget', 'ipat_widgetShortcode' );

			if(is_admin()) {
				add_action('admin_menu', array(&$this, 'add_menupages'));
			}
			// Example Filter add_filter( 'user_can_richedit', array(&$this, 'disable_wysiwyg') );
		}
		function getOptions() {
			// $this->settings = get_option('ipat_widget');

			// check, if all required settings are in DB
			$basicSettings = get_option('ipat_widget');
			if (!array_key_exists('remoteHost',$basicSettings)) {
				$basicSettings['remoteHost']=get_bloginfo('wpurl');
			}
			if (!array_key_exists('widgetType',$basicSettings)) {
				$basicSettings['widgetType']=2;
			}
			if (!array_key_exists('userID',$basicSettings)) {
				$basicSettings['userID']=0;
			}
			if (!array_key_exists('userName',$basicSettings)) {
				$basicSettings['userName']="";
			}
			if (!array_key_exists('teamID',$basicSettings)) {
				$basicSettings['teamID']=0;
			}
			if (!array_key_exists('isTeamWidget',$basicSettings)) {
				$basicSettings['isTeamWidget']=false;
			}
			if (!array_key_exists('lang',$basicSettings)) {
				$basicSettings['lang']='de';
			}
			if (!array_key_exists('lastUpdate',$basicSettings)) {
				$basicSettings['lastUpdate']=0;
			}
			if (!array_key_exists('refreshInterval',$basicSettings)) {
				$basicSettings['refreshInterval']=60;
			}
			if (!array_key_exists('treeCount',$basicSettings)) {
				$basicSettings['treeCount']=0;
			}
			if (!array_key_exists('co2Saving',$basicSettings)) {
				$basicSettings['co2Saving']=0;
			}
			if (!array_key_exists('widgetControlTitle',$basicSettings)) {
				$basicSettings['widgetControlTitle']='I Plant A Tree';
			}
			if (!array_key_exists('widgetControlAlign',$basicSettings)) {
				$basicSettings['widgetControlAlign']='center';
			}
			if (!array_key_exists('widgetControlTextBefore',$basicSettings)) {
				$basicSettings['widgetControlTextBefore']='Dieses Blog ist CO<sub>2</sub>-neutral.';
			}
			if (!array_key_exists('widgetControlTextAfter',$basicSettings)) {
				$basicSettings['widgetControlTextAfter']='';
			}
			update_option('ipat_widget', $basicSettings);
			$basicSettings = get_option('ipat_widget');
		}
		static function activate() {
			// Create Tables if needed or generate whatever on installation
			$basicSettings = get_option('ipat_widget');
			if (!($basicSettings)) {
				// first activation of plugin ever
				$basicSettings['userName']="";
				$basicSettings['userID']=0;
				$basicSettings['teamID']=0;
				$basicSettings['isTeamWidget']=false;
				$basicSettings['lastUpdate']=0;
				$basicSettings['remoteHost']=get_bloginfo('wpurl');
				$basicSettings['widgetType']=2;
				$basicSettings['lang']='de';
				$basicSettings['refreshInterval']=60;
				$basicSettings['treeCount']=0;
				$basicSettings['co2Saving']=0;
				$basicSettings['widgetControlTitle']='I Plant A Tree';
				$basicSettings['widgetControlAlign']='center';
				$basicSettings['widgetControlTextBefore']='Dieses Blog ist CO<sub>2</sub>-neutral.';
				$basicSettings['widgetControlTextAfter']='';
				update_option('ipat_widget', $basicSettings);
			}
		}
		function uninstall() {
			// Delete Tables or settings if needed be on deinstallation
		}
		function add_menupages() {
			// For Option Pages, see WordPress function: add_options_page()
			// For own Menu Pages, see WordPress function: add_menu_page() and add_submenu_page()
		}
		public static function ipat_widgetShortcode($atts) {
			ipat_updateIfNecessary();
			$ipat_settings = get_option('ipat_widget');
			$atts = shortcode_atts( array(
				'align' => '',
				'class' => '',
				'language' => ''
			), $atts, 'ipat_widget_class' );
			// switch ($atts['language']) {
			// case 'de': $ipat_extraLanguage='de'; break;
			// case 'en': $ipat_extraLanguage='en'; break;
			// case 'it': $ipat_extraLanguage='it'; break;
			// default: //ipat_detectLanguage();
			// $ipat_extraLanguage=$ipat_settings['lang'];	// if no local language is set, use global
			// break;
			// }
			$widgetHTML='';
			switch ($ipat_settings['widgetType']) {
				case 2: $widgetImageSize='220x90'; break;
				case 3: $widgetImageSize='100x150'; break;
				case 4: $widgetImageSize='180x80'; break;
				default: $widgetImageSize='120x190'; break;
			}
			$widgetHTML=ipat_generateWidgetHTML($ipat_settings['widgetType'],'shortcodeAlign_'.$atts['align'],$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],$atts['class']);
			return $widgetHTML;
		}
	}

	function ipat_widget1() {
		global $ipat_widget;
		$ipat_widget = new ipat_widget_class();
		$ipat_widget->getOptions();
	}
	add_action('init', 'ipat_widget1');

	function ipat_addStylesheet() {
		$myStyleFile = WP_PLUGIN_URL .'/'.dirname(plugin_basename(__FILE__)).'/assets/css/ipat_style.css';
		wp_register_style('ipat_styleSheet', $myStyleFile);
		wp_enqueue_style( 'ipat_styleSheet');
	}
	add_action('admin_head', 'ipat_addStylesheet');
	add_action('wp_print_styles', 'ipat_addStylesheet');

	function ipat_plugin_menu() {
		add_options_page( 'I plant a tree Options', 'I plant a tree', 'manage_options', 'i_plant_a_tree', 'ipat_plugin_options' );
	}
	add_action( 'admin_menu', 'ipat_plugin_menu' );
}


if (function_exists('register_activation_hook')) { register_activation_hook(__FILE__, array('ipat_widget_class', 'activate')); }
if (function_exists('register_uninstall_hook')) { register_uninstall_hook(__FILE__, array('ipat_widget_class', 'uninstall')); }



function ipat_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$ipat_settings = get_option('ipat_widget');
	$ipat_remoteUpdateSuccessful=true;
	if (isset($_POST['ipat_submit'])) {
		// save submitted settings
		$ipat_settings['userName']=wp_strip_all_tags($_POST['ipat_userName'],true);
		$ipat_settings['userID']=intval($_POST['ipat_userID'],10);
		$ipat_settings['teamID']=intval($_POST['ipat_teamID'],10);
		$ipat_settings['isTeamWidget']=false;
		if($_POST['ipat_isTeamWidget']=='true') $ipat_settings['isTeamWidget']=true;
		$ipat_settings['remoteHost']=get_bloginfo('wpurl');
		//$ipat_settings['widgetType']=intval($_POST['ipat_widgetType'],10);
		$ipat_settings['widgetType']=wp_strip_all_tags($_POST['ipat_widgetType'],true);
		if ($ipat_settings['widgetType']=='') $ipat_settings['widgetType']='1';
		$ipat_language=sanitize_text_field($_POST['ipat_language']);
		$ipat_supportedLanguages=array('de','en','it');
		if (!in_array($ipat_language,$ipat_supportedLanguages)) $ipat_language='de';
		$ipat_settings['lang']=$ipat_language;
		$ipat_settings['refreshInterval']=intval($_POST['ipat_refreshInterval'],10);
		if ($ipat_settings['refreshInterval']==0 || $ipat_settings['refreshInterval']==1) $ipat_settings['refreshInterval']=1440;
		update_option('ipat_widget', $ipat_settings);
		$ipat_remoteUpdateSuccessful=ipat_updateIfNecessary(true);
		$ipat_settings = get_option('ipat_widget');
	}
	$ipat_userName=$ipat_settings['userName'];
	$ipat_userID=$ipat_settings['userID'];
	$ipat_teamID=$ipat_settings['teamID'];
	?>
	<div class="wrap">
		<h2><?php _e('Settings','i-plant-a-tree');?> > I Plant A Tree</h2>
		<form novalidate="novalidate" method="post">
			<table class="form-table ipatSettings">
				<tr>
					<th scope="row">
						<label for="ipat_userName">IPAT <?php _e('user name','i-plant-a-tree');?></label>
					</th>
					<td colspan="4">
						<input id="ipat_userName" class="regular-text" type="text" value="<?php echo $ipat_userName; ?>" name="ipat_userName">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ipat_teamID">IPAT <?php _e('team ID','i-plant-a-tree');?></label>
					</th>
					<td colspan="4">
						<input id="ipat_teamID" class="regular-text" type="text" value="<?php echo $ipat_teamID; ?>" name="ipat_teamID">
						<p class="description"><?php _e('If this widget is for a team, please enter the team ID. Otherwise, just leave it at zero.','i-plant-a-tree');?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ipat_widgetType"><?php _e('user or team widget','i-plant-a-tree');?></label>
					</th>
					<td colspan="4">
						<input type="radio" <?php if ($ipat_settings['isTeamWidget']==false) echo 'checked="checked"';?> value="false" name="ipat_isTeamWidget"><span class="ipat_isTeamWidget"><?php _e('user widget','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['isTeamWidget']==true) echo 'checked="checked"';?> value="true" name="ipat_isTeamWidget"><span class="ipat_isTeamWidget"><?php _e('team widget','i-plant-a-tree');?></span>
						<p class="description"><?php _e('Please select if this widget is for a single user or for a team.','i-plant-a-tree');?></p>
						<p class="description" style="color:#f00"><?php _e('Important notice: Unfortunately this feature is currently not available due to the IPAT website relaunch but will hopefully be reimplemented into their API soon.','i-plant-a-tree');?></strong></p>
					</td>
				</tr>
				<!--tr>
					<th scope="row">
						<label for="ipat_widgetType"><?php _e('language','i-plant-a-tree');?></label>
					</th>
					<td colspan="4">
						<input type="radio" <?php if ($ipat_settings['lang']=="de") echo 'checked="checked"';?> value="de" name="ipat_language"><span class="ipat_language"><?php _e('german','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['lang']=="en") echo 'checked="checked"';?> value="en" name="ipat_language"><span class="ipat_language"><?php _e('english','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['lang']=="it") echo 'checked="checked"';?> value="it" name="ipat_language"><span class="ipat_language"><?php _e('italian','i-plant-a-tree');?></span>
						<p class="description"><?php _e('This global setting can be overridden manually in the shortcode, if necessary.','i-plant-a-tree');?></p>
					</td>
				</tr-->
				<tr class="ipat_widgetType">
					<th scope="row">
						<label for="ipat_widgetType"><?php _e('widget type (classic)','i-plant-a-tree');?></label>
					</th>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='1') echo 'checked="checked"';?> value="1" name="ipat_widgetType"><span class="m-l">120 x 190 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML(1,'',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='2') echo 'checked="checked"';?> value="2" name="ipat_widgetType"><span class="m-l">220 x 90 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML(2,'',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='3') echo 'checked="checked"';?> value="3" name="ipat_widgetType"><span class="m-l">100 x 150 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML(3,'',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='4') echo 'checked="checked"';?> value="4" name="ipat_widgetType"><span class="m-l">180 x 80 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML(4,'',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
				</tr>
				<tr class="ipat_widgetType">
					<th scope="row">
						<label for="ipat_widgetType"><?php _e('widget type','i-plant-a-tree');?></label>
					</th>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_100x100') echo 'checked="checked"';?> value="white_100x100" name="ipat_widgetType"><span class="m-l">100×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_100x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_200x100') echo 'checked="checked"';?> value="white_200x100" name="ipat_widgetType"><span class="m-l">200×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_200x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_300x100') echo 'checked="checked"';?> value="white_300x100" name="ipat_widgetType"><span class="m-l">300×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_300x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_400x100') echo 'checked="checked"';?> value="white_400x100" name="ipat_widgetType"><span class="m-l">400×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_400x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
				</tr>
				<tr class="ipat_widgetType">
					<th>
					</th>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_100x100') echo 'checked="checked"';?> value="green_100x100" name="ipat_widgetType"><span class="m-l">100×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_100x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_200x100') echo 'checked="checked"';?> value="green_200x100" name="ipat_widgetType"><span class="m-l">200×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_200x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_300x100') echo 'checked="checked"';?> value="green_300x100" name="ipat_widgetType"><span class="m-l">300×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_300x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_400x100') echo 'checked="checked"';?> value="green_400x100" name="ipat_widgetType"><span class="m-l">400×100 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_400x100','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
				</tr>
				<tr class="ipat_widgetType">
					<th>
					</th>
					<td>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_100x200') echo 'checked="checked"';?> value="white_100x200" name="ipat_widgetType"><span class="m-l">100×200 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_100x200','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_100x300') echo 'checked="checked"';?> value="white_100x300" name="ipat_widgetType"><span class="m-l">100×300 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_100x300','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='white_100x400') echo 'checked="checked"';?> value="white_100x400" name="ipat_widgetType"><span class="m-l">100×400 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('white_100x400','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
				</tr>
				<tr class="ipat_widgetType">
					<th>
					</th>
					<td>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_100x200') echo 'checked="checked"';?> value="green_100x200" name="ipat_widgetType"><span class="m-l">100×200 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_100x200','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_100x300') echo 'checked="checked"';?> value="green_100x300" name="ipat_widgetType"><span class="m-l">100×300 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_100x300','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
					<td>
						<input type="radio" <?php if ($ipat_settings['widgetType']=='green_100x400') echo 'checked="checked"';?> value="green_100x400" name="ipat_widgetType"><span class="m-l">100×400 px</span><br/>
						<?php
							echo ipat_generateWidgetHTML('green_100x400','',$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="ipat_refreshInterval"><?php _e('update interval','i-plant-a-tree');?></label>
					</th>
					<td colspan="4">
						<?php $ipat_intervalChecked=false; ?>
						<input type="radio" <?php if ($ipat_settings['refreshInterval']==240) {echo 'checked="checked"'; $ipat_intervalChecked=true;} ?> value="240" name="ipat_refreshInterval"><span class="m-l"><?php _e('4 hours','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['refreshInterval']==720) {echo 'checked="checked"'; $ipat_intervalChecked=true;}?> value="720" name="ipat_refreshInterval"><span class="m-l"><?php _e('12 hours','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['refreshInterval']==1440) {echo 'checked="checked"'; $ipat_intervalChecked=true;}?> value="1440" name="ipat_refreshInterval"><span class="m-l"><?php _e('24 hours','i-plant-a-tree');?></span>
						<input type="radio" <?php if ($ipat_settings['refreshInterval']==10080 || !$ipat_intervalChecked) echo 'checked="checked"';?> value="10080" name="ipat_refreshInterval"><span class="m-l"><?php _e('weekly','i-plant-a-tree');?></span>
						<p class="description"><?php _e('The update interval determines how often new data will be gotten from server. Usually a daily update will do.','i-plant-a-tree');?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input id="ipat_submit" class="button button-primary" type="submit" value="<?php _e('save changes','i-plant-a-tree');?>" name="ipat_submit">
			</p>
			<?php
			if (!$ipat_remoteUpdateSuccessful) {
				echo '<h3>';
				_e('Error: Server not accessible.','i-plant-a-tree');
				echo '</h3>';
				echo '<p>';
				_e('Your settings were saved, but no actual data could be retrieved from the IPAT-server.','i-plant-a-tree');
				echo '</p>';
			}
			?>
		</form>
	</div>
	<?php
}

function ipat_updateIfNecessary($forced=false) {
	$ipat_remoteUpdateSuccessful=false;
	$ipat_settings = get_option('ipat_widget');
	if (($ipat_settings['lastUpdate']+$ipat_settings['refreshInterval']*60)<time() || $forced) {
		$ipat_remoteUpdateSuccessful=ipat_getRemoteWidgetData ($ipat_settings);
	}
	return $ipat_remoteUpdateSuccessful;
}

function ipat_urlGetContents_cURL ($url) {
	if (!function_exists('curl_init')){		// cURL is not installed!
		return false;
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ipat_fileContent = curl_exec($ch);
	curl_close($ch);
	return $ipat_fileContent;
}

function ipat_urlGetContents_file_get_contents ($url) {
	$ipat_fileContent=@file_get_contents($url);
	return $ipat_fileContent;
}

function ipat_urlGetContents_fopen ($url) {
	if (function_exists('fopen') && function_exists('stream_get_contents')) {
		$handle = fopen ($url, "r");
		$ipat_fileContent=stream_get_contents($handle);
		fclose ($handle);
		return $ipat_fileContent;
	} else {
		return false;
	}
}

function ipat_getRemoteWidgetData ($ipat_settings) {
	if ($ipat_settings['isTeamWidget']) {
		$url = "https://www.iplantatree.org/teamWidget?tid=".$ipat_settings['teamID']."&wt=".$ipat_settings['widgetType']."&lang=".$ipat_settings['lang']."";
	} else {
		//~ $url = "https://www.iplantatree.org/widget/ipatWidget.html?uid=".$ipat_settings['userID']."&wt=".$ipat_settings['widgetType']."&lang=".$ipat_settings['lang']."";
		$url = "https://iplantatree.org/p/u/reports/co2/user?userName=".$ipat_settings['userName'];
	}
	$ipat_fileContent=ipat_urlGetContents_cURL($url);
	if (!$ipat_fileContent) { $ipat_fileContent=ipat_urlGetContents_fopen($url); }
	if (!$ipat_fileContent) { $ipat_fileContent=ipat_urlGetContents_file_get_contents($url);
		return false;
	} else {
		//~ $ipat_fileContent = str_replace(" ", '', $ipat_fileContent);
		//~ $ipat_fileContent = str_replace(array("\r\n","\r","\n","\t","\v","\f","\e"), '', $ipat_fileContent);
		//~ $ipat_fileContent = str_replace("<br>", '', $ipat_fileContent);
		//~ $ipat_fileContent = str_replace(",}}}", '}}}', $ipat_fileContent);
		//~ preg_match_all("/{.+}/s",$ipat_fileContent,$jsonPart);
		//~ $widget=json_decode($jsonPart[0][0]);
		$widget=json_decode($ipat_fileContent);
		//~ $treeCount=$widget->{"Widget"}->{"Data"}->{"treeCount"};
		//~ $co2Saving=$widget->{"Widget"}->{"Data"}->{"co2Saving"};
		$treeCount=$widget->{"treesCount"};
		$co2Saving=$widget->{"co2"};
		$ipat_settings['lastUpdate']=time();
		if ((!$ipat_settings['isTeamWidget'] && $ipat_settings['userID']==0 && $ipat_settings['userName']=='') || ($ipat_settings['isTeamWidget'] && $ipat_settings['teamID']==0)) {
			$ipat_settings['treeCount']=1;
			$ipat_settings['co2Saving']=0;
		} else {
			$ipat_settings['treeCount']=intval($treeCount,10);
			$ipat_settings['co2Saving']=floatval($co2Saving);
		}
		update_option('ipat_widget', $ipat_settings);
		return true;
	}
}

function ipat_sidebarDisplay($args) {
	ipat_updateIfNecessary();
	$ipat_settings = get_option('ipat_widget');

	$ipat_sidebarWidgetHTML.=$args['before_widget'];
	$ipat_sidebarWidgetHTML.=$args['before_title'].$ipat_settings['widgetControlTitle'].$args['after_title'];
	$ipat_sidebarWidgetHTML.='<div class="textwidget">';
	$ipat_sidebarWidgetHTML.='<p>'.$ipat_settings['widgetControlTextBefore'].'</p>';
	$ipat_sidebarWidgetHTML.='<p>';

	$ipat_sidebarWidgetHTML.=ipat_generateWidgetHTML($ipat_settings['widgetType'],$ipat_settings['widgetControlAlign'],$ipat_settings['isTeamWidget'],$ipat_settings['userName'],$ipat_settings['teamID'],$ipat_settings['treeCount'],$ipat_settings['co2Saving'],'');

	$ipat_sidebarWidgetHTML.='</p>';
	$ipat_sidebarWidgetHTML.='<p>'.$ipat_settings['widgetControlTextAfter'].'</p>';
	$ipat_sidebarWidgetHTML.='</div>';
	$ipat_sidebarWidgetHTML.=$args['after_widget'];

	echo $ipat_sidebarWidgetHTML;
}

function ipat_generateWidgetHTML($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass) {
	$ipat_sidebarWidgetHTML='';

	switch ($ipat_widgetType) {
		case '1':
			$ipat_sidebarWidgetHTML=ipat_generateWidgetHTML_classic($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass); break;
		case '2':
			$ipat_sidebarWidgetHTML=ipat_generateWidgetHTML_classic($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass); break;
		case '3':
			$ipat_sidebarWidgetHTML=ipat_generateWidgetHTML_classic($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass); break;
		case '4':
			$ipat_sidebarWidgetHTML=ipat_generateWidgetHTML_classic($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass); break;
		default:
			$ipat_sidebarWidgetHTML=ipat_generateWidgetHTML_modern($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass); break;
	}

	return $ipat_sidebarWidgetHTML;
}

function ipat_generateWidgetHTML_modern($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass) {

	$ipat_sidebarWidgetHTML='<div class="ipat_widget ipat_widgetType_'.$ipat_widgetType;

	switch ($ipat_widgetControlAlign) {
		case 'left': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarLeft'; break;
		case 'right': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarRight'; break;
		case 'center': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarCenter'; break;
		case 'shortcodeAlign_left': $ipat_sidebarWidgetHTML.=' ipat_alignLeft'; break;
		case 'shortcodeAlign_right': $ipat_sidebarWidgetHTML.=' ipat_alignRight'; break;
		case 'shortcodeAlign_center': $ipat_sidebarWidgetHTML.=' ipat_alignCenter'; break;
	}
	$ipat_sidebarWidgetHTML.=' '.$ipat_extraClass.'">';

	$ipat_sidebarWidgetHTML.='<a ';
	if ($ipat_co2Saving>1) {
		$ipat_sidebarWidgetHTML.='title="'.round(($ipat_co2Saving*1000),1).' kg" ';
	}
	if ($ipat_isTeamWidget) {
		$ipat_sidebarWidgetHTML.='href="https://www.iplantatree.org/team/'.$ipat_teamID.'" target="_blank" rel="noopener">';
	} else {
		$ipat_sidebarWidgetHTML.='href="https://www.iplantatree.org/user/'.$ipat_userName.'" target="_blank" rel="noopener">';
	}

	$ipat_sidebarWidgetHTML.='<img src="'.plugins_url().'/'.dirname(plugin_basename(__FILE__)).'/assets/image/ipat_widget_'.$ipat_widgetType.'.svg" />';

	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetTreeCountTextWrapper">';
	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetTreeCountText">';
	if ($ipat_widgetType=='white_100x100' || $ipat_widgetType=='green_100x100') {
		$ipat_sidebarWidgetHTML.=__('trees','i-plant-a-tree').':</div>';
	} else {
		$ipat_sidebarWidgetHTML.=__('planted trees','i-plant-a-tree').':</div>';
	}
	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetTreeCount">'.$ipat_treeCount.'</div>';
	$ipat_sidebarWidgetHTML.='</div>';

	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetCo2SavingWrapper">';
	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetCo2SavingText">';
	if ($ipat_widgetType=='white_100x100' || $ipat_widgetType=='green_100x100') {
		$ipat_sidebarWidgetHTML.='CO<sub>2</sub>:</div>';
	} else {
		$ipat_sidebarWidgetHTML.=__('CO<sub>2</sub> saved','i-plant-a-tree').':</div>';
	}
	$ipat_sidebarWidgetHTML.='<div class="ipat_m_widgetCo2Saving">'.ipat_formatHumanReadable($ipat_co2Saving).'</div>';
	$ipat_sidebarWidgetHTML.='</div>';

	$ipat_sidebarWidgetHTML.='</a>';
	$ipat_sidebarWidgetHTML.='</div>';

	return $ipat_sidebarWidgetHTML;
}

function ipat_generateWidgetHTML_classic($ipat_widgetType,$ipat_widgetControlAlign,$ipat_isTeamWidget,$ipat_userName,$ipat_teamID,$ipat_treeCount,$ipat_co2Saving,$ipat_extraClass) {
	$ipat_sidebarWidgetHTML='';

	switch ($ipat_widgetType) {
		case '1': $widgetImageSize='120x190'; break;
		case '2': $widgetImageSize='220x90'; break;
		case '3': $widgetImageSize='100x150'; break;
		case '4': $widgetImageSize='180x80'; break;
	}

	$ipat_sidebarWidgetHTML.='<div class="ipat_widget ipat_widgetType'.$ipat_widgetType;
	switch ($ipat_widgetControlAlign) {
		case 'left': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarLeft'; break;
		case 'right': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarRight'; break;
		case 'center': $ipat_sidebarWidgetHTML.=' ipat_alignSidebarCenter'; break;
		case 'shortcodeAlign_left': $ipat_sidebarWidgetHTML.=' ipat_alignLeft'; break;
		case 'shortcodeAlign_right': $ipat_sidebarWidgetHTML.=' ipat_alignRight'; break;
		case 'shortcodeAlign_center': $ipat_sidebarWidgetHTML.=' ipat_alignCenter'; break;
	}
	$ipat_sidebarWidgetHTML.=' '.$ipat_extraClass.'">';

	$ipat_sidebarWidgetHTML.='<a ';
	if ($ipat_co2Saving>1) {
		$ipat_sidebarWidgetHTML.='title="'.round(($ipat_co2Saving*1000),1).' kg" ';
	}
	if ($ipat_isTeamWidget) {
		$ipat_sidebarWidgetHTML.='href="https://www.iplantatree.org/team/'.$ipat_teamID.'" target="_blank" rel="noopener">';
	} else {
		$ipat_sidebarWidgetHTML.='href="https://www.iplantatree.org/user/'.$ipat_userName.'" target="_blank" rel="noopener">';
	}

	$ipat_sidebarWidgetHTML.='<img src="'.plugins_url().'/'.dirname(plugin_basename(__FILE__)).'/assets/image/widget-'.$widgetImageSize.'.png"/>';
	$ipat_sidebarWidgetHTML.='<div class="ipat_widgetTreeCountText">'.__('planted trees','i-plant-a-tree').':</div>';
	$ipat_sidebarWidgetHTML.='<div class="ipat_widgetTreeCount">'.$ipat_treeCount.'</div>';
	$ipat_sidebarWidgetHTML.='<div class="ipat_widgetCo2SavingText">'.__('CO<sub>2</sub> saved','i-plant-a-tree').':</div>';
	$ipat_sidebarWidgetHTML.='<div class="ipat_widgetCo2Saving">'.ipat_formatHumanReadable($ipat_co2Saving).'</div>';
	$ipat_sidebarWidgetHTML.='</a>';
	$ipat_sidebarWidgetHTML.='</div>';

	return $ipat_sidebarWidgetHTML;
}

function ipat_formatHumanReadable($ipat_co2Saving) {
	$co2SavingFormated=$ipat_co2Saving;
	if ($ipat_co2Saving<0.001) {
		$co2SavingFormated=round(($ipat_co2Saving*1000*1000),0);
		$co2SavingFormated=$co2SavingFormated.' g';
	} elseif ($ipat_co2Saving<1) {
		$co2SavingFormated=round(($ipat_co2Saving*1000),1);
		$co2SavingFormated=$co2SavingFormated.' kg';
	} else {
		$co2SavingFormated=number_format(round($ipat_co2Saving,2),2,'.',' ');
		$co2SavingFormated=$co2SavingFormated.' t';
	}
	return $co2SavingFormated;
}

wp_register_sidebar_widget(
	'ipat_sidebar',				// unique widget id
	'I Plant A Tree',			// widget name
	'ipat_sidebarDisplay',		// callback function
	array(						// options
		'description' => 'Shows the saved CO2 in your sidebar.')
	);
	wp_register_widget_control(
		'ipat_sidebar',			// unique widget id
		'I Plant A Tree',			// widget name
		'ipat_widgetControl'		// Callback function
	);

	function ipat_detectLanguage() {
		return "de";
	}

	function ipat_widgetControl($args=array(), $params=array()) {
		//the form is submitted, save into database
		$ipat_settings=get_option('ipat_widget');
		if (isset($_POST['submitted'])) {
			$ipat_settings['widgetControlTitle']=sanitize_text_field($_POST['ipat_widgetControlTitle']);
			$ipat_settings['widgetControlAlign']=sanitize_text_field($_POST['ipat_widgetControlAlign']);
			$ipat_settings['widgetControlTextBefore']=sanitize_text_field($_POST['ipat_widgetControlTextBefore']);
			$ipat_settings['widgetControlTextAfter']=sanitize_text_field($_POST['ipat_widgetControlTextAfter']);
			update_option('ipat_widget',$ipat_settings);
		}
		?>

		<p>
			<label for="ipat_widgetControlTitle"><?php _e('Title','i-plant-a-tree'); ?></label>
			<input id="ipat_widgetControlTitle" class="widefat" type="text" value="<?php echo $ipat_settings['widgetControlTitle']; ?>" name="ipat_widgetControlTitle">
		</p>
		<p>
			<label for="ipat_widgetControlAlign"><?php _e('Widget alignment','i-plant-a-tree'); ?></label>
			<input type="radio" <?php if ($ipat_settings['widgetControlAlign']=="left") echo 'checked="checked"';?> value="left" name="ipat_widgetControlAlign"><?php _e('left','i-plant-a-tree');?>
			<input type="radio" <?php if ($ipat_settings['widgetControlAlign']=="center") echo 'checked="checked"';?> value="center" name="ipat_widgetControlAlign"><?php _e('centered','i-plant-a-tree');?>
			<input type="radio" <?php if ($ipat_settings['widgetControlAlign']=="right") echo 'checked="checked"';?> value="right" name="ipat_widgetControlAlign"><?php _e('right','i-plant-a-tree');?>
		</p>
		<p>
			<label for="ipat_widgetControlTextBefore"><?php _e('Text above widget','i-plant-a-tree'); ?></label>
			<textarea id="ipat_widgetControlTextBefore" class="widefat" name="ipat_widgetControlTextBefore" cols="20" rows="2"><?php echo $ipat_settings['widgetControlTextBefore']; ?></textarea>
		</p>
		<p>
			<label for="ipat_widgetControlTextAfter"><?php _e('Text below widget','i-plant-a-tree'); ?></label>
			<textarea id="ipat_widgetControlTextAfter" class="widefat" name="ipat_widgetControlTextAfter" cols="20" rows="2"><?php echo $ipat_settings['widgetControlTextAfter']; ?></textarea>
		</p>
		<input type="hidden" name="ipat_widgetControlSubmitted" value="1" />
		<input type="hidden" name="submitted" value="1" />

		<?php
	}

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ipat_add_action_links' );
	function ipat_add_action_links ( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=i_plant_a_tree' ) . '">'. __('Settings','i-plant-a-tree') .'</a>',
		);
		return array_merge( $links, $mylinks );
	}

	add_shortcode( 'ipat_widget', array( 'ipat_widget_class', 'ipat_widgetShortcode') );
	?>
