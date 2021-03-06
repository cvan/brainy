<?php
/**
 * @package Brainy
 * @subpackage PluginsModifierCompiler
 */


/**
 * Parameter decode helper, since parameters are unparsed
 *
 * @author Matt Basta
 * @param string $value The unparsed parameter
 * @return mixed
 */
function smarty_modifiercompiler_escape_helper($value) {
    if ($value[0] === "'" && $value[strlen($value) - 1] === "'") {
        $value = '"' . substr($value, 1, -1) . '"';
    }
    return json_decode($value);
}

/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  escape string for output
 *
 * @link http://www.smarty.net/docsv2/en/language.modifier.escape
 * @author Rodney Rehm
 * @param array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_escape($params, $compiler) {
    try {
        $esc_type = isset($params[1]) ? smarty_modifiercompiler_escape_helper($params[1]) : 'html';
        $char_set = isset($params[2]) && $params[2] !== 'null' ? $params[2] : '"' . Smarty::$_CHARSET . '"';
        $double_encode = isset($params[3]) ? $params[3] : 'true';

        if (!$char_set) {
            $char_set = '"' . Smarty::$_CHARSET . '"';
        }

        switch ($esc_type) {
            case 'html':
                return 'htmlspecialchars('
                    . $params[0] .', ENT_QUOTES, '
                    . $char_set . ', '
                    . $double_encode . ')';

            case 'htmlall':
                if (Smarty::$_MBSTRING) {
                    return 'mb_convert_encoding(htmlspecialchars('
                        . $params[0] .', ENT_QUOTES, '
                        . $char_set . ', '
                        . $double_encode
                        . '), "HTML-ENTITIES", '
                        . $char_set . ')';
                }

                // no MBString fallback
                return 'htmlentities('
                    . $params[0] .', ENT_QUOTES, '
                    . $char_set . ', '
                    . $double_encode . ')';

            case 'url':
                return 'rawurlencode(' . $params[0] . ')';

            case 'urlpathinfo':
                return 'str_replace("%2F", "/", rawurlencode(' . $params[0] . '))';

            case 'quotes':
                // escape unescaped single quotes
                return 'preg_replace("%(?<!\\\\\\\\)\'%", "\\\'",' . $params[0] . ')';

            case 'javascript':
                // escape quotes and backslashes, newlines, etc.
                return 'strtr(' . $params[0] . ', array("\\\\" => "\\\\\\\\", "\'" => "\\\\\'", "\"" => "\\\\\"", "\\r" => "\\\\r", "\\n" => "\\\n", "</" => "<\/" ))';

        }
    } catch (SmartyException $e) {
        // pass through to regular plugin fallback
    }

    // could not optimize |escape call, so fallback to regular plugin
    $compiler->template->required_plugins['compiled']['escape']['modifier']['file'] = SMARTY_PLUGINS_DIR .'modifier.escape.php';
    $compiler->template->required_plugins['compiled']['escape']['modifier']['function'] = 'smarty_modifier_escape';

    return 'smarty_modifier_escape(' . join( ', ', $params ) . ')';
}
