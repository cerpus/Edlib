<?php

namespace App\Libraries\HTMLPurify\Config;
use App\Libraries\HTMLPurify\HTMLModule\MathML;
use HTMLPurifier_Config;
use HTMLPurifier_HTML5Config;
use HTMLPurifier_ConfigSchema;
use HTMLPurifier_PropertyList;
use HTMLPurifier_VarParser;

class MathMLConfig extends HTMLPurifier_HTML5Config
{
    const REVISION = 2019042605;

    /**
     * @param  string|array|HTMLPurifier_Config $config
     * @param  HTMLPurifier_ConfigSchema $schema OPTIONAL
     * @return HTMLPurifier_HTML5Config
     */
    public static function create($config, $schema = null)
    {
        if ($config instanceof HTMLPurifier_Config) {
            $schema = $config->def;
            $config = null;
        }

        if (!$schema instanceof HTMLPurifier_ConfigSchema) {
            $schema = HTMLPurifier_ConfigSchema::instance();
        }

        $html5Config = parent::create($schema, $schema);
        $mathConfig = self::inherit($html5Config);
        $mathConfig->set('HTML.DefinitionID', __CLASS__);
        $mathConfig->set('HTML.DefinitionRev', self::REVISION);

        return $mathConfig;
    }

    /**
     * Creates a configuration object using the default config schema instance
     *
     * @return HTMLPurifier_HTML5Config
     */
    public static function createDefault()
    {
        return self::create(null);
    }

    /**
     * Creates a new config object that inherits from a previous one
     *
     * @param  HTMLPurifier_Config $config
     * @return HTMLPurifier_HTML5Config
     */
    public static function inherit(HTMLPurifier_Config $config)
    {
        return new self($config->def, $config->plist);
    }

    /**
     * @param HTMLPurifier_ConfigSchema $schema
     * @param HTMLPurifier_PropertyList $parent OPTIONAL
     */
    public function __construct(HTMLPurifier_ConfigSchema $schema, HTMLPurifier_PropertyList $parent = null)
    {
        parent::__construct($schema, $parent);
        $schema->add('HTML.MathML', false, HTMLPurifier_VarParser::C_BOOL, true);
    }

    public function getDefinition($type, $raw = false, $optimized = false)
    {
        // Setting HTML.* keys removes any previously instantiated HTML
        // definition object, so set up HTML5 definition as late as possible
        $needSetup = $type === 'HTML' && !isset($this->definitions[$type]);
        if ($needSetup) {
            if ($def = parent::getDefinition($type, true, true)) {
                $mathModule = new MathML();
                $def->manager->addModule($mathModule);
            }
        }
        return parent::getDefinition($type, $raw, $optimized);
    }

    static public function getMathTags(){
        return array(
            'math',
            'maction',
            'maligngroup',
            'malignmark',
            'menclose',
            'merror',
            'mfenced',
            'mfrac',
            'mglyph',
            'mi',
            'mlabeledtr',
            'mlongdiv',
            'mmultiscripts',
            'mn',
            'mo',
            'mover',
            'mpadded',
            'mphantom',
            'mroot',
            'mrow',
            'ms',
            'mscarries',
            'mscarry',
            'msgroup',
            'msline',
            'mspace',
            'msqrt',
            'msrow',
            'mstack',
            'mstyle',
            'msub',
            'msup',
            'msubsup',
            'mtable',
            'mtd',
            'mtext',
            'mtr',
            'munder',
            'munderover',
            'semantics',
            'annotation',
            'annotation-xml',
        );
    }
}
