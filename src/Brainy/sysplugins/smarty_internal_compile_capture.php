<?php
/**
 * Smarty Internal Plugin Compile Capture
 *
 * Compiles the {capture} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Capture Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Capture extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('name');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('name', 'assign', 'append');

    /**
     * Compiles code for the {capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $buffer = isset($_attr['name']) ? $_attr['name'] : "'default'";
        $assign = isset($_attr['assign']) ? $_attr['assign'] : 'null';
        $append = isset($_attr['append']) ? $_attr['append'] : 'null';

        $compiler->_capture_stack[0][] = array($buffer, $assign, $append);
        $_output = "\$_smarty_tpl->_capture_stack[0][] = array($buffer, $assign, $append);\nob_start();\n";

        return $_output;
    }

}

/**
 * Smarty Internal Plugin Compile Captureclose Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_CaptureClose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($buffer, $assign, $append) = array_pop($compiler->_capture_stack[0]);

        $_output  = "list(\$_capture_buffer, \$_capture_assign, \$_capture_append) = array_pop(\$_smarty_tpl->_capture_stack[0]);\n";
        $_output .= "if (!empty(\$_capture_buffer)) {\n";
        $_output .= " if (isset(\$_capture_assign)) \$_smarty_tpl->assign(\$_capture_assign, ob_get_contents());\n";
        $_output .= " if (isset(\$_capture_append)) \$_smarty_tpl->append(\$_capture_append, ob_get_contents());\n";
        $_output .= " Smarty::\$_smarty_vars['capture'][\$_capture_buffer]=ob_get_clean();\n";
        $_output .= "} else \$_smarty_tpl->capture_error();\n";

        return $_output;
    }

}
