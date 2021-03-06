<?php
/**
 * Brainy modifier enforcement tests
 *
 * @package PHPunit
 * @author Matt Basta
 */

class EnforceModifierTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        // We want to show all errors for this test suite.
        error_reporting(E_ALL);
    }
    public function tearDown() {
        Smarty::$enforce_expression_modifiers = array();
        Smarty::$enforce_modifiers_on_static_expressions = false;
    }

    public function testNormalExpressionsPass() {
        $this->expectOutputString('foo');
        // Static expressions should not require escaping by default.
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"}');
    }

    public function testModifiedExpressionsPass() {
        $this->expectOutputString('foo');
        // Static expressions should not require escaping by default.
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testDeeplyModifiedExpressionsPass() {
        $this->expectOutputString('Foo');
        // Static expressions should not require escaping by default.
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    public function testModifiedExpressionsWithAttributesPass() {
        $this->expectOutputString('%66%6f%6f');
        // Static expressions should not require escaping by default.
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testNormalExpressionsThrowWithStatic() {
        Smarty::$enforce_expression_modifiers = array('foo');
        Smarty::$enforce_modifiers_on_static_expressions = true;
        $this->smarty->display('eval:{"foo"}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testModifiedExpressionsThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        Smarty::$enforce_modifiers_on_static_expressions = true;
        $this->smarty->display('eval:{"foo"|escape}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testDeeplyModifiedExpressionsThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        Smarty::$enforce_modifiers_on_static_expressions = true;
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    public function testModifiedExpressionsWithAttributesThrow() {
        $this->expectOutputString('%66%6f%6f');
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }


    public function testModifiedExpressionsDoNotThrow() {
        $this->expectOutputString('foo');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testModifiedExpressionsWithAttributesDoNotThrow() {
        $this->expectOutputString('%66%6f%6f');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testExpressionsThatDoNotEndWithEnforcedModifiersThrow() {
        Smarty::$enforce_expression_modifiers = array('escape');
        Smarty::$enforce_modifiers_on_static_expressions = true;
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testBareSmartyVariablesThrow() {
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{$smarty.request.foo}');
    }

    public function testProtectedSmartyVariablesThrow() {
        $_REQUEST['foo'] = 'bar';
        $this->expectOutputString('bar');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{$smarty.request.foo|escape}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testNonStaticModifiersThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('escapetype', 'html');
        $this->smarty->display('eval:{"foo"|escape:$escapetype}');
    }

    /**
     * @expectedException BrainyModifierEnforcementException
     */
    public function testNestedNonStaticModifiersThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('escapetype', 'html');
        $this->smarty->display('eval:{"foo"|escape:$escapetype|capitalize}');
    }


    public function passing_example_provider() {
        return array(
            array('{($integer+1)|escape:"html"}', array('escape'), '124'),
        );
    }
    public function failing_example_provider() {
        return array(
            array('{$integer+1|escape:"html"}', array('escape')),
        );
    }

    /**
     * @dataProvider passing_example_provider
     */
    public function testPassingExamples($example, $modifiers, $expected) {
        $this->expectOutputString($expected);
        $this->smarty->assign('integer', 123);
        Smarty::$enforce_expression_modifiers = $modifiers;
        $this->smarty->display('eval:' . $example);
    }

    /**
     * @dataProvider failing_example_provider
     * @expectedException BrainyModifierEnforcementException
     */
    public function testFailingExamples($example, $modifiers) {
        $this->smarty->assign('integer', 123);
        Smarty::$enforce_expression_modifiers = $modifiers;
        $this->smarty->display('eval:' . $example);
    }

}
