<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

function themes_apply($content) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_apply', array(
			$content 
		));
	}
	
	if (!$ret) {
		$ret = core_hook('common', 'themes_apply', array(
			$content 
		));
	}
	
	return $ret;
}

function themes_submenu($content) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_submenu', array(
			$content 
		));
	}
	
	if (!$ret) {
		$ret = core_hook('common', 'themes_submenu', array(
			$content 
		));
	}
	
	return $ret;
}

function themes_get_menu_tree($menus = '') {
	global $menu_config;
	
	if ($menus) {
		$menu_config = $menus;
	}
	$ret = themes_buildmenu($menu_config);
	
	return $ret;
}

function themes_buildmenu($menu_config) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_buildmenu', array(
			$menu_config 
		));
	}
	
	if (!$ret) {
		$ret = core_hook('common', 'themes_buildmenu', array(
			$menu_config 
		));
	}
	
	return $ret;
}

function themes_navbar($num, $nav, $max_nav, $url, $page) {
	$search = themes_search_session();
	
	if ($search['keyword']) {
		$search_url = '&search_keyword=' . urlencode($search['keyword']);
	}
	if ($search['category']) {
		$search_url .= '&search_category=' . urlencode($search['category']);
	}
	$url = $url . $search_url;
	$nav_pages = '';
	if ($theme) {
		$nav_pages = core_hook($theme, 'themes_navbar', array(
			$num,
			$nav,
			$max_nav,
			$url,
			$page 
		));
	}
	
	if (!$nav_pages) {
		$nav_pages = core_hook('common', 'themes_navbar', array(
			$num,
			$nav,
			$max_nav,
			$url,
			$page 
		));
	}
	
	return $nav_pages;
}

function themes_nav($count, $url = '') {
	$ret = FALSE;
	
	$lines_per_page = 20;
	$max_nav = 5;
	$num = ceil($count / $lines_per_page);
	$nav = (_NAV_ ? _NAV_ : 1);
	$page = (_PAGE_ ? _PAGE_ : 1);
	$url = (trim($url) ? trim($url) : $_SERVER['REQUEST_URI']);
	if ($ret['form'] = themes_navbar($num, $nav, $max_nav, $url, $page)) {
		$ret['limit'] = $lines_per_page;
		$ret['offset'] = ($page - 1) * $lines_per_page;
		$ret['top'] = ($count - ($lines_per_page * ($page - 1))) + 1;
		$ret['nav'] = $nav;
		$ret['page'] = $page;
		$ret['url'] = $url;
	}
	$_SESSION['tmp']['themes_nav'] = $ret;
	
	return $ret;
}

function themes_nav_session() {
	return $_SESSION['tmp']['themes_nav'];
}

function themes_search($search_category = array(), $url = '', $keyword_converter = array()) {
	global $core_config;
	
	$ret['keyword'] = $_REQUEST['search_keyword'];
	$ret['url'] = (trim($url) ? trim($url) : $_SERVER['REQUEST_URI']);
	$ret['category'] = $_REQUEST['search_category'];
	$option_search_category = "<option value=\"\">" . _('Search') . "</option>";
	foreach ($search_category as $key => $val) {
		
		$c_keyword = $ret['keyword'];
		
		if ($c_function = $keyword_converter[$val]) {
			if (function_exists($c_function)) {
				$c_keyword = $c_function($ret['keyword']);
			}
		}
		
		if ($selected = ($ret['category'] == $val ? 'selected' : '') && $c_keyword) {
			$ret['dba_keywords'] = array(
				$val => '%' . $c_keyword . '%' 
			);
		}
		
		$option_search_category .= "<option value=\"" . $val . "\" $selected>" . ucfirst($key) . "</option>";
		
		if ($c_keyword) {
			$tmp_dba_keywords[$val] = '%' . $c_keyword . '%';
		}
	}
	
	if ((!$ret['category']) && $ret['keyword']) {
		$ret['dba_keywords'] = $tmp_dba_keywords;
	}
	
	$content = "
		<form action='" . $ret['url'] . "' method=POST>
		" . _CSRF_FORM_ . "
		<div class=search_box>
			<div class=search_box_select><select name='search_category' class=search_input_category>" . $option_search_category . "</select></div>
			<div class=search_box_input><input type='text' name='search_keyword' class=search_input_keyword value='" . $ret['keyword'] . "' maxlength='30' onEnter='document.searchbar.submit();'></div>
		</div>
		</form>";
	$ret['form'] = $content;
	$_SESSION['tmp']['themes_search'] = $ret;
	
	return $ret;
}

function themes_search_session() {
	return $_SESSION['tmp']['themes_search'];
}

function themes_button_back($url) {
	global $core_config;
	
	$content = themes_button($url, _('Back'), 'button_back');
	
	return $content;
}

function themes_link($url, $title = '', $css_class = "", $css_id = "") {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_link', array(
			$url,
			$title,
			$css_class,
			$css_id 
		));
	}
	if (!$ret) {
		$url = _u($url);
		$c_title = ($title ? $title : $url);
		$css_class = ($css_class ? " class=\"" . $css_class . "\"" : '');
		$css_id = ($css_id ? " id=\"" . $css_id . "\"" : '');
		$ret = "<a href=\"" . _u($url) . "\"" . $css_class . $css_id . ">" . $c_title . "</a>";
	}
	
	return $ret;
}

function themes_url($url) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_url', array(
			$url 
		));
	}
	if (!$ret) {
		
		// we will do clean URL mod here when necessary
		$ret = $url;
	}
	
	return $ret;
}

function themes_button($url, $title, $css_class = '', $css_id = '') {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_button', array(
			$url,
			$title,
			$css_class,
			$css_id 
		));
	}
	
	if (!$ret) {
		$css_class = ($css_class ? " " . $css_class : '');
		$css_id = ($css_id ? " id=\"" . $css_id . "\"" : '');
		$ret = "<a href=# class=\"button" . $css_class . "\" " . $css_id . "value=\"" . $title . "\" onClick=\"javascript:window.location.href='" . _u($url) . "'\" />" . $title . "</a>";
	}
	
	return $ret;
}

function themes_hint($text) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_hint', array(
			$text 
		));
	}
	
	if (!$ret) {
		$ret = "<i class='glyphicon glyphicon-info-sign playsms-tooltip' data-toggle=tooltip title='" . $text . "' rel=tooltip></i>";
	}
	
	return $ret;
}

function themes_mandatory($text) {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_mandatory', array(
			$text 
		));
	}
	
	if (!$ret) {
		$ret = $text . " <i class='glyphicon glyphicon-exclamation-sign playsms-mandatory' data-toggle=tooltip title='" . _('This field is required') . "' rel=tooltip></i>";
	}
	
	return $ret;
}

/**
 * Generate options for select HTML tag
 *
 * @param array $options
 *        Select options
 * @param string $selected
 *        Selected option
 * @return string Options for select HTML tag
 */
function themes_select_options($options = array(), $selected = '') {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_select_options', array(
			$options,
			$selected 
		));
	}
	
	if (!$ret) {
		foreach ($options as $key => $val) {
			if (is_int($key)) {
				$key = $val;
			}
			$c_selected = ($val == $selected ? 'selected' : '');
			$ret .= '<option value="' . $val . '" ' . $c_selected . '>' . $key . '</option>';
		}
	}
	
	return $ret;
}

/**
 * Generate select HTML tag
 *
 * @param string $name
 *        Tag name
 * @param array $options
 *        Select options
 * @param string $selected
 *        Selected option
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string Select HTML tag
 */
function themes_select($name, $options = array(), $selected = '', $tag_params = array(), $css_id = '', $css_class = '') {
	$select_options = themes_select_options($options, $selected);
	if (is_array($tag_params)) {
		foreach ($tag_params as $key => $val) {
			$params .= ' ' . $key . '="' . $val . '"';
		}
	}
	$ret = '<select name="' . $name . '" id="' . $css_id . '" class="playsms-select ' . $css_class . '" ' . $params . '>' . $select_options . '</select>';
	
	return $ret;
}

/**
 * Generate select HTML tag for yes-no or enabled-disabled type of options
 *
 * @param string $name
 *        Tag name
 * @param boolean $selected
 *        TRUE if yes/enabled
 * @param string $yes
 *        'Yes' or 'Enabled' option
 * @param string $no
 *        'No' or 'Disabled' option
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string Select HTML tag
 */
function themes_select_yesno($name, $selected, $yes = '', $no = '', $tag_params = array(), $css_id = '', $css_class = '') {
	$yes = ($yes ? $yes : _('yes'));
	$no = ($no ? $no : _('no'));
	$options = array(
		$yes => 1,
		$no => 0 
	);
	
	return themes_select($name, $options, $selected, $tag_params, $css_id, $css_class);
}

/**
 * Display error string from function parameter or session
 *
 * @param array $error_string
 *        Array of error strings (optional)
 * @return string HTML string of error strings
 */
function themes_display_error_string($error_string = array()) {
	if (is_array($error_string) && (count($error_string) > 0)) {
		$errors = $error_string;
	} else {
		$errors = $_SESSION['error_string'];
	}
	
	if (!is_array($errors)) {
		$errors = array(
			$errors 
		);
	}
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_display_error_string', array(
			$errors 
		));
	}
	
	if (!$ret) {
		if (count($errors) > 0) {
			foreach ($errors as $err) {
				if (trim($err)) {
					$ret .= '<div class=error_string>' . trim($err) . '</div>';
				}
			}
		}
	}
	
	return $ret;
}

function themes_select_users_single($select_field_name, $selected_value = '', $tag_params = array(), $css_id = '', $css_class = '') {
	global $user_config;
	
	$ret = '';
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_select_users_single', array(
			$select_field_name,
			$selected_value,
			$tag_params,
			$css_id,
			$css_class 
		));
	}
	if (!$ret) {
		
		if (!is_array($selected_value)) {
			$selected_value = array(
				$selected_value 
			);
		}
		
		if (auth_isadmin()) {
			$admins = user_getallwithstatus(2);
			$users = user_getallwithstatus(3);
		}
		$subusers = user_getsubuserbyuid($user_config['uid']);
		
		$option_user .= '<option value="0">' . _('Select users') . '</option>';
		if (count($admins) > 0) {
			$option_user .= '<optgroup label="' . _('Administrators') . '">';
			
			foreach ($admins as $admin) {
				$selected = '';
				foreach ($selected_value as $sv) {
					if ($admin['uid'] == $sv) {
						$selected = 'selected';
						break;
					}
				}
				$option_user .= '<option value="' . $admin['uid'] . '" ' . $selected . '>' . $admin['name'] . ' (' . $admin['username'] . ') - ' . _('Administrator') . '</option>';
			}
			$option_user .= '</optgroup>';
		}
		
		if (count($users) > 0) {
			
			$option_user .= '<optgroup label="' . _('Users') . '">';
			
			foreach ($users as $user) {
				$selected = '';
				foreach ($selected_value as $sv) {
					if ($user['uid'] == $sv) {
						$selected = 'selected';
						break;
					}
				}
				$option_user .= '<option value="' . $user['uid'] . '" ' . $selected . '>' . $user['name'] . ' (' . $user['username'] . ') - ' . _('User') . '</option>';
			}
			$option_user .= '</optgroup>';
		}
		
		if (count($subusers) > 0) {
			
			$option_user .= '<optgroup label="' . _('Subusers') . '">';
			
			foreach ($subusers as $subuser) {
				$selected = '';
				foreach ($selected_value as $sv) {
					if ($subuser['uid'] == $sv) {
						$selected = 'selected';
						break;
					}
				}
				$option_user .= '<option value="' . $subuser['uid'] . '"' . $selected . '>' . $subuser['name'] . ' (' . $subuser['username'] . ') - ' . _('Subuser') . '</option>';
			}
			$option_user .= '</optgroup>';
		}
		
		$css_id = (trim($css_id) ? trim($css_id) : 'playsms-select-users-single-' . core_sanitize_alphanumeric($select_field_name));
		
		if (is_array($tag_params)) {
			foreach ($tag_params as $key => $val) {
				$params .= ' ' . $key . '="' . $val . '"';
			}
		}
		
		$placeholder = ($tag_params['placeholder'] ? $tag_params['placeholder'] : _('Select users'));
		$width = ($tag_params['width'] ? $tag_params['width'] : 'resolve');
		
		$js = '
			<script language="javascript" type="text/javascript">
				$(document).ready(function() {
					$("#' . $css_id . '").select2({
						placeholder: "' . $placeholder . '",
						width: "' . $width . '",
						separator: [\',\'],
						tokenSeparators: [\',\'],
					});
				});
			</script>
		';
		
		$ret = $js . PHP_EOL . '<select name="' . $select_field_name . '" id="' . $css_id . '" class="playsms-select ' . $css_class . '" ' . $params . '>' . $option_user . '</select>';
		
		return $ret;
	}
}

function themes_select_users_multi($select_field_name, $selected_value = array(), $tag_params = array(), $css_id = '', $css_class = '') {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_select_users_multi', array(
			$select_field_name,
			$selected_value,
			$tag_params,
			$css_id,
			$css_class 
		));
	}
	
	if (!$ret) {
		$tag_params['multiple'] = 'multiple';
		$ret = themes_select_users_single($select_field_name . '[]', $selected_value, $tag_params, $css_id, $css_class);
	}
	
	return $ret;
}

/**
 * Generate HTML input tag
 *
 * @param string $type
 *        Input type
 * @param string $name
 *        Input name
 * @param string $value
 *        Input default value
 * @param array $tag_params
 *        Additional input tag parameters
 * @param string $css_id
 *        CSS ID
 * @param string $css_class
 *        CSS class name
 * @return string HTML input tag
 */
function themes_input($type = 'text', $name = '', $value = '', $tag_params = array(), $css_id = '', $css_class = '') {
	$ret = '';
	
	if (core_themes_get()) {
		$ret = core_hook(core_themes_get(), 'themes_input', array(
			$type,
			$name,
			$value,
			$tag_params,
			$css_id,
			$css_class 
		));
	}
	
	if (!$ret) {
		if (is_array($tag_params)) {
			foreach ($tag_params as $key => $val) {
				if (is_numeric($key)) {
					$params .= ' ' . $val;
				} else {
					$params .= ' ' . $key . '="' . $val . '"';
				}
			}
		} else {
			$params = $tag_params;
		}
		$ret = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" id="' . $css_id . '" class="playsms-input ' . $css_class . '" ' . $params . '>';
	}
	
	return $ret;
}
