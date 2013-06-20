<?php
include_once __DIR__ . '/../vendor/autoload.php';

class RegexSyntaxParser implements PEG_IParser
{ 
    protected $regexParser;

    function __construct()
    {
        /*
         * regex <- split*
         * split <- operations ("|" operations)*
         * operations <- operation*
         * operation <- target operator
         * target <- charClass / group / singleCharacter
         * suffixOperator <- "*" / "+" / "?"
         * group <- "(" split ")"
         * charClass <- "[" (!"]" .)+ "]"
         * singleCharacter <- ![+*?|[)] .
         */

        $singleCharacter = self::objectize('singleCharacter', 
            PEG::second(PEG::not(PEG::choice('*', '+', '?', '|', '[', ')')), PEG::anything())
        );
        $charClass = self::objectize('charClass', PEG::second(
            '[', 
            PEG::many1(PEG::second(PEG::not(']'), PEG::anything())),
            ']'
        ));
        $group = self::objectize('group', PEG::memo(PEG::second(
            '(', PEG::ref($split), ')'
        )));
        $suffixOperator = self::objectize('suffixOperator', PEG::choice('*', '+', '?'));
        $target = PEG::choice(
            $charClass, $group, $singleCharacter
        );
        $operation = self::objectize('operation', 
            PEG::seq($target, PEG::optional($suffixOperator))
        );
        $operations = self::objectize('operations', PEG::many($operation));
        $split = self::objectize('split', 
            PEG::choice(PEG::listof($operations, '|'), '')
        );

        $this->regexParser = self::objectize('regex', PEG::many($split));
    }

    /**
     * @return PEG::IParser
     */
    function getParser() 
    {
        return $this->regexParser;
    }

    /**
     * @param String $str
     */
    function parse(PEG_IContext $context)
    {
        return $this->regexParser->parse($context);
    }

    /**
     * @param PEG_IParser
     * @return PEG_IParser
     */
    protected static function objectize($name, PEG_IParser $parser)
    {
        return PEG::hook(function($result) use($name) {
            return new RegexSyntaxNode($name, $result);
        }, $parser);
    }

}

class RegexSyntaxNode
{
    protected $name, $content;

    function __construct($name, $content) 
    {
        $this->name = $name;
        $this->content = $content;
    }

    function __toString()
    {
        $result = '';

        $result .= $this->name . " {\n";

        $result .= $this->dump($this->content);

        $result .= "}";

        return $result;
    }

    protected function dump($content)
    {
        $result = '';
        if (is_array($content)) {
            foreach ($content as $i => $element) {
                $result .= $this->dump($element);
            }
        } elseif ($content instanceof self) {
            $result .= self::indent($content->__toString()) . "\n";
        } else {
            $result .= self::indent(var_export($content, true)) . "\n";
        }

        return $result;
    }

    static function indent($str) {
        $lines = preg_split("/\r|\n|\r\n/", $str);

        foreach ($lines as $i => $line) {
            $lines[$i] = '  ' . $line;
        }

        return implode($lines, "\n");
    }
}

