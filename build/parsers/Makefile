all: parsers

parsers: configparser templateparser

configparser: smarty_internal_configfilelexer.php smarty_internal_configfileparser.php
	cp smarty_internal_configfilelexer.php ../../src/Brainy/sysplugins/smarty_internal_configfilelexer.php
	cp smarty_internal_configfileparser.php ../../src/Brainy/sysplugins/smarty_internal_configfileparser.php

smarty_internal_configfilelexer.php: smarty_internal_configfilelexer.plex
	php Create_Config_Parser.php

smarty_internal_configfileparser.php: smarty_internal_configfileparser.y
	php Create_Config_Parser.php


templateparser: smarty_internal_templatelexer.php smarty_internal_templateparser.php
	cp smarty_internal_templatelexer.php ../../src/Brainy/sysplugins/smarty_internal_templatelexer.php
	cp smarty_internal_templateparser.php ../../src/Brainy/sysplugins/smarty_internal_templateparser.php

smarty_internal_templatelexer.php: smarty_internal_templatelexer.plex
	php Create_Template_Parser.php

smarty_internal_templateparser.php: smarty_internal_templateparser.y
	php Create_Template_Parser.php

clean:
	rm -f smarty_internal_*er.out smarty_internal_*er.php
