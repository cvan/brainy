<?php
/**
* Smarty PHPunit tests for File resources
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for file resource tests
*/
class FileResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    protected function relative($path) {
        $path = str_replace( dirname(__FILE__), '.', $path );
        if (DS == "\\") {
            $path = str_replace( "\\", "/", $path );
        }

        return $path;
    }

    public function testGetTemplateFilepath() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals(realpath('test/templates/helloworld.tpl'), str_replace('\\','/',$tpl->source->filepath));
    }

    public function testTemplateFileExists1() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue($tpl->source->exists);
    }
    public function testTemplateFileExists2() {
        $this->assertTrue($this->smarty->templateExists('helloworld.tpl'));
    }

    public function testTemplateFileNotExists1() {
        $tpl = $this->smarty->createTemplate('notthere.tpl');
        $this->assertFalse($tpl->source->exists);
    }
    public function testTemplateFileNotExists2() {
        $this->assertFalse($this->smarty->templateExists('notthere.tpl'));
    }
    public function testTemplateFileNotExists3() {
        try {
            $result = $this->smarty->fetch('notthere.tpl');
        } catch (Exception $e) {
            $this->assertContains('Unable to load template file \'notthere.tpl\'', $e->getMessage());

            return;
        }
        $this->fail('Exception for not existing template is missing');
    }

    public function testGetTemplateTimestamp() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }

    public function testGetTemplateSource() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals('hello world', $tpl->source->content);
    }

    public function testUsesCompiler() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->source->uncompiled);
    }

    public function testIsEvaluated() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->source->recompiled);
    }

    public function testGetCompiledFilepath() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $expected = './compiled/'.sha1($this->smarty->getTemplateDir(0) . 'helloworld.tpl').'.file.helloworld.tpl.php';
        $this->assertEquals($expected, $this->relative($tpl->compiled->filepath));
    }

    public function testGetCompiledTimestampPrepare() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        // create dummy compiled file
        file_put_contents($tpl->compiled->filepath, '<?php ?>');
        touch($tpl->compiled->filepath, $tpl->source->timestamp);
    }
    public function testGetCompiledTimestamp() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue(is_integer($tpl->compiled->timestamp));
        $this->assertEquals(10, strlen($tpl->compiled->timestamp));
        $this->assertEquals($tpl->compiled->timestamp, $tpl->source->timestamp);
    }

    public function testMustCompileExisting() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->mustCompile());
    }

    public function testMustCompileAtForceCompile() {
        $this->smarty->force_compile = true;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue($tpl->mustCompile());
    }

    public function testMustCompileTouchedSource() {
        $this->smarty->force_compile = false;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        touch($tpl->source->filepath);
        // reset cache for this test to work
        unset($tpl->source->timestamp);
        $this->assertTrue($tpl->mustCompile());
        // clean up for next tests
        $this->smarty->clearCompiledTemplate();
    }

    public function testCompileTemplateFile() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $tpl->compileTemplateSource();
    }

    public function testCompiledTemplateFileExits() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue(file_exists($tpl->compiled->filepath));
    }

    public function testGetCachedFilepath() {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $expected = './cache/'.sha1($this->smarty->getTemplateDir(0) . 'helloworld.tpl').'.helloworld.tpl.php';
        $this->assertEquals($expected, $this->relative($tpl->cached->filepath));
    }

    public function testGetCachedTimestamp() {
        // create dummy cache file for the following test
        file_put_contents('test/cache/' . sha1($this->smarty->getTemplateDir(0) . 'helloworld.tpl').'.helloworld.tpl.php', '<?php ?>');
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue(is_integer($tpl->cached->timestamp));
        $this->assertEquals(10, strlen($tpl->cached->timestamp));
    }

    public function testIsCachedPrepare() {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        // clean up for next tests
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
        // compile and cache
        $this->smarty->fetch($tpl);
    }

    public function testForceCache() {
        $this->smarty->caching = true;
        $this->smarty->force_cache = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->isCached());
    }

    public function testIsCachedTouchedSourcePrepare() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        sleep(0.5);
        touch ($tpl->source->filepath);
    }
    public function testIsCachedTouchedSource() {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->isCached());
    }

    public function testIsCachedCachingDisabled() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->isCached());
    }

    public function testIsCachedForceCompile() {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->force_compile = true;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->isCached());
    }

    public function testGetRenderedTemplate() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals('hello world', $tpl->fetch());
    }

    public function testSmartyIsCachedPrepare() {
        // prepare files for next test
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        // clean up for next tests
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->smarty->fetch($tpl);
    }

    public function testSmartyIsCachedCachingDisabled() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($this->smarty->isCached($tpl));
    }

    public function testRelativeInclude() {
        $result = $this->smarty->fetch('relative.tpl');
        $this->assertContains('hello world', $result);
    }

    public function testRelativeIncludeSub() {
        $result = $this->smarty->fetch('sub/relative.tpl');
        $this->assertContains('hello world', $result);
    }

    public function testRelativeIncludeFail() {
        try {
            $this->smarty->fetch('relative_sub.tpl');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("Unable to load template"), $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown relative filepath has not been raised.');
    }

    public function testRelativeIncludeFailOtherDir() {
        $this->smarty->addTemplateDir('test/templates_2');
        try {
            $this->smarty->fetch('relative_notexist.tpl');
        } catch (Exception $e) {
            $this->assertContains("Unable to load template", $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown relative filepath has not been raised.');
    }

    public function testRelativeFetch() {
        $this->smarty->setTemplateDir(array(
            dirname(__FILE__) . '/does-not-exist/',
            dirname(__FILE__) . '/templates/sub/',
        ));
        $this->smarty->security_policy = null;
        $this->assertEquals('hello world', $this->smarty->fetch('./relative.tpl'));
        $this->assertEquals('hello world', $this->smarty->fetch('../helloworld.tpl'));
    }

    protected function _relativeMap($map, $cwd=null) {
        foreach ($map as $file => $result) {
            $this->smarty->clearCompiledTemplate();
            $this->smarty->clearAllCache();

            if ($result === null) {
                try {
                    $this->smarty->fetch($file);
                    if ($cwd !== null) {
                        chdir($cwd);
                    }

                    $this->fail('Exception expected for ' . $file);

                    return;
                } catch (SmartyException $e) {
                    // this was expected to fail
                }
            } else {
                try {
                    $_res = $this->smarty->fetch($file);
                    $this->assertEquals($result, $_res, $file);
                } catch (Exception $e) {
                    if ($cwd !== null) {
                        chdir($cwd);
                    }

                    throw $e;
                }
            }
        }

        if ($cwd !== null) {
            chdir($cwd);
        }
    }
    public function testRelativity() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            $dn . '/templates/relativity/theory/',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            '././foo.tpl' => 'theory',
            '../foo.tpl' => 'relativity',
            '.././foo.tpl' => 'relativity',
            './../foo.tpl' => 'relativity',
            'einstein/foo.tpl' => 'einstein',
            './einstein/foo.tpl' => 'einstein',
            '../theory/einstein/foo.tpl' => 'einstein',
            'test/templates/relativity/relativity.tpl' => 'relativity',
            './test/templates/relativity/relativity.tpl' => 'relativity',
        );

        $this->_relativeMap($map);

        $this->smarty->setTemplateDir(array(
            'test/templates/relativity/theory/',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            '././foo.tpl' => 'theory',
            '../foo.tpl' => 'relativity',
            '.././foo.tpl' => 'relativity',
            './../foo.tpl' => 'relativity',
            'einstein/foo.tpl' => 'einstein',
            './einstein/foo.tpl' => 'einstein',
            '../theory/einstein/foo.tpl' => 'einstein',
            'test/templates/relativity/relativity.tpl' => 'relativity',
            './test/templates/relativity/relativity.tpl' => 'relativity',
        );

        $this->_relativeMap($map);
    }
    public function testRelativityPrecedence() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            $dn . '/templates/relativity/theory/einstein/',
        ));

        $map = array(
            'foo.tpl' => 'einstein',
            './foo.tpl' => 'einstein',
            '././foo.tpl' => 'einstein',
            '../foo.tpl' => 'theory',
            '.././foo.tpl' => 'theory',
            './../foo.tpl' => 'theory',
            '../../foo.tpl' => 'relativity',
        );

        $newdest = $dn . '/templates/relativity/theory/';
        chdir($newdest);
        if (!file_exists($newdest . 'cache')) {
            mkdir($newdest . 'cache');
        }
        $this->_relativeMap($map, $cwd);

        $map = array(
            '../theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            '../../relativity.tpl' => 'relativity',
            '../relativity.tpl' => 'relativity',
            './einstein.tpl' => 'einstein',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
        );

        chdir($dn . '/templates/relativity/theory/');
        $this->_relativeMap($map, $cwd);
    }
    public function testRelativityRelRel() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            '../..',
        ));

        $map = array(
            'foo.tpl' => 'relativity',
            './foo.tpl' => 'relativity',
            '././foo.tpl' => 'relativity',
        );

        $newdest = $dn . '/templates/relativity/theory/einstein';
        chdir($newdest);
        if (!file_exists($newdest . '/cache')) {
            mkdir($newdest . '/cache');
        }
        $this->_relativeMap($map, $cwd);

        $map = array(
            'relativity.tpl' => 'relativity',
            './relativity.tpl' => 'relativity',
            'theory/theory.tpl' => 'theory',
            './theory/theory.tpl' => 'theory',
        );

        $newdest = $dn . '/templates/relativity/theory/einstein/';
        chdir($newdest);
        if (!file_exists($newdest . 'cache')) {
            mkdir($newdest . 'cache');
        }
        $this->_relativeMap($map, $cwd);

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            'theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
            '../theory/einstein/einstein.tpl' => 'einstein',
            '../relativity.tpl' => 'relativity',
            './../relativity.tpl' => 'relativity',
            '.././relativity.tpl' => 'relativity',
        );

        $this->smarty->setTemplateDir(array(
            '..',
        ));
        chdir($dn . '/templates/relativity/theory/einstein/');
        $this->_relativeMap($map, $cwd);
    }
    public function testRelativityRelRel1() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            '..',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            'theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
            '../theory/einstein/einstein.tpl' => 'einstein',
            '../relativity.tpl' => 'relativity',
            './../relativity.tpl' => 'relativity',
            '.././relativity.tpl' => 'relativity',
        );

        chdir($dn . '/templates/relativity/theory/einstein/');
        $this->_relativeMap($map, $cwd);
    }

    /**
    * final cleanup
    */
    public function testFinalCleanup() {
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
    }
}
