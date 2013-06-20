<?php
include_once __DIR__ . '/../src/PHPRegex.php';

$parser = new RegexSyntaxParser();

echo 'a => ' . $parser->parse(PEG::context('a')) . "\n\n";

echo 'a|b =>' .  $parser->parse(PEG::context('a|b')) . "\n\n"; 

echo 'a(bc) => ' . $parser->parse(PEG::context('a(bc)')) . "\n\n";

echo 'a+b*c? => ' . $parser->parse(PEG::context('a+b*c?')) . "\n\n";
