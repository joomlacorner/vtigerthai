<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_tabs} function plugin
 *
 * Type:     function<br>
 * Name:     html_tabs<br>
 * Input:<br>
 *           - name       (required) - query string name
 *           - values     (required if no options supplied) - array
 *           - options    (required if no values supplied) - associative array
 *           - selected   (optional) - string default not set
 *           - output     (required if not options supplied) - array
 *           - fgclass (optional)
 *           - bgclass (optional)
 * Purpose:  Creates a tab bar from the passed parameters
 * 
 * ChangeLog?: $PHP_SELF reference changed to $_SERVER['PHP_SELF']
 * 				Initialized $_html_top variable
 * 				Added in logic to retain all current $_REQUEST variables when generating new links
 * 
 * @version 1.1
 * @author Unknown
 * @author Owen Cole owenc@totalsales.com - http://www.totalsales.com
 * @link http://smarty.php.net/manual/en/language.function.html.tabs.php {html_image}
 *      (Smarty online manual)
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_tabs($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    
    $name = null;
    $values = null;
    $options = null;
    $selected = array();
    $output = null;
    
    $extra = '';
    
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'fgclass':
            case 'bgclass':
            case 'name':
                $$_key = (string)$_val;
                break;
            
            case 'options':
                $$_key = (array)$_val;
                break;
                
            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;

            case 'selected':
                $$_key = array_map('strval', array_values((array)$_val));
                break;
                
            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_tabs: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (!isset($name) || (!isset($options) && !isset($values)))
        return ''; /* raise error here? */

    if (!isset($fgclass))
        $fgclass = '';

    if (!isset($bgclass))
        $bgclass = '';

    $_html_result = '';
	$_html_top = '';
    if (is_array($options)) {
        
        foreach ($options as $_key=>$_val) {
            $_html_top .= smarty_function_html_tab_top($name,$_key,$selected,$fgclass,$bgclass);
            $_html_result .= smarty_function_html_tab_output($name,$_key, $_val, $selected,$fgclass,$bgclass);
        }

    } else {
        
        foreach ((array)$values as $_i=>$_key) {
            $_val = isset($output[$_i]) ? $output[$_i] : '';
            $_html_top .= smarty_function_html_tab_top($name,$_key,$selected,$fgclass,$bgclass);
            $_html_result .= smarty_function_html_tab_output($name,$_key, $_val, $selected,$fgclass,$bgclass);
        }

    }

    $_html_result = '<table cellpadding="0"><tr>' . "\n" . $_html_top . "</tr>\n<tr>". $_html_result . '</tr></table>' . "\n";

    return $_html_result;

}

function smarty_function_html_tab_top($name,$key, $selected,$fgclass,$bgclass) {
    if (in_array((string)$key, $selected)) {
        if (!empty($fgclass)) {
          $_html_result = "<td class=\"$fgclass\" height=1></td>";
        } else {
          $_html_result = "<td height=1></td>";
        }
    } else {
        if (!empty($bgclass)) {
          $_html_result = "<td class=\"$bgclass\" height=1></td>";
        } else {
          $_html_result = "<td height=1></td>";
        }
    }
    $_html_result .= '<td width=1 height=1></td>'."\n";
    $_html_result .= '<td width=1 height=1></td>'."\n";
    return $_html_result;
}
function smarty_function_html_tab_output($name,$key, $value, $selected,$fgclass,$bgclass) {
	$get_params = "";
	foreach($_REQUEST as $variablename => $val) { 
		if($variablename!=$name) {
			$get_params.="$variablename=$val&"; 
		}
	}

    $_html_result = "<td ";   
    if (in_array((string)$key, $selected)) {
        if (!empty($fgclass)) {
          $_html_result .= " class=\"$fgclass\"";
          $_html_result .= ">&nbsp;<a class=\"$fgclass\" href=\"".$_SERVER['PHP_SELF']."?$get_params$name=".smarty_function_escape_special_chars($key) . '">';
          $_html_result .= smarty_function_escape_special_chars($value) . '</a>&nbsp;</td>' . "\n";
          $_html_result .= "<td width=1 class=\"$fgclass\"></td>\n";
        } else {
          $_html_result .= ">&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?$get_params$name=".smarty_function_escape_special_chars($key) . '">';
          $_html_result .= smarty_function_escape_special_chars($value) . '</a>&nbsp;</td>' . "\n";
          $_html_result .= '<td width=1></td>'."\n";
        }
    } else {
        if (!empty($bgclass)) {
          $_html_result .= " class=\"$bgclass\"";
          $_html_result .= ">&nbsp;<a class=\"$bgclass\" href=\"".$_SERVER['PHP_SELF']."?$get_params$name=".smarty_function_escape_special_chars($key) . '">';
          $_html_result .= smarty_function_escape_special_chars($value) . '</a>&nbsp;</td>';
          $_html_result .= "<td width=1 class=\"$bgclass\"></td>\n";
        } else {
          $_html_result .= ">&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?$get_params$name=".smarty_function_escape_special_chars($key) . '">';
          $_html_result .= smarty_function_escape_special_chars($value) . '</a>&nbsp;</td>' . "\n";
          $_html_result .= '<td width=1></td>'."\n";
        }
    }
    $_html_result .= '<td width=1 height=1></td>'."\n";
    return $_html_result;
}

/* vim: set expandtab: */

?>