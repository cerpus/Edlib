<?php

namespace App\Libraries\HTMLPurify\HTMLModule;

use HTMLPurifier_Config;
use HTMLPurifier_HTMLModule;
use App\Libraries\HTMLPurify\Injector\MathMLSpaceNormalize;

/**
 * MathML 3 specification.
 */
class MathML extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'MathML';

    // Prefix in case MathML is imported
    private $mathml_prefix = 'm';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        // Normalize whitespace inside text elements as per MathML spec 2.1.7
        $this->info_injector[] = new MathMLSpaceNormalize();

        /*****************************************************************
         * Meta variables
         * Used in this file to simplify code and help adapt the DTD
         *****************************************************************/

        // The elements inside <math> are not supposed to be outside, so they
        // can't be neither block nor inline:
        $default_display = false;

        // This array will contain all the necessary DTD entities used
        // throughout the MathML DTD, in order to avoid collisions and allow
        // for special characters ('.', '-') in entity names
        $E = [];

        // Prefix used for xlink attrs; is not specified by the MathML DTD
        $E['XLINK.prefix'] = 'xlink';

        $proprietary_att_wrs = [
            'wrs:valign' => 'CDATA',
            'wrs:columnalign' => 'CDATA',
            'wrs:positionable' => 'CDATA',
            'wrs:linecolor' => 'CDATA',
            'wrs:baseline' => 'CDATA',
            'wrs:reserved' => 'CDATA',
            'wrs:decimalseparators' => 'CDATA',
            // TODO: Add xmlns:wrs
        ];

        $proprietary_att_dsi = [
            'xmlns:dsi' => 'Bool#http://www.dessci.com/mathml',
            'dsi:background' => 'CDATA',
            'dsi:color' => 'CDATA',
        ];

        /*****************************************************************
         * DTD code
         * Code from the DTD ported and adapted
         *****************************************************************/

        $E['MalignExpression'] = 'maligngroup|malignmark';
        $E['TokenExpression'] = 'mi|mn|mo|mtext|mspace|ms';
        $E['PresentationExpression'] =
            $E['TokenExpression'] .
            '|' . $E['MalignExpression'] .
            '|mrow|mfrac|msqrt|mroot|mstyle|merror|mpadded|mphantom|mfenced' .
            '|menclose|msub|msup|msubsup|munder|mover|munderover' .
            '|mmultiscripts|mtable|mstack|mlongdiv|maction';

        $E['DefEncAtt'] = [
            'encoding' => 'CDATA',
            'definitionurl' => 'CDATA',
        ];

        $E['CommonAtt'] = array_merge(
            [
                'xmlns' => 'Bool#http://www.w3.org/1998/Math/MathML',
                $E['XLINK.prefix'] . ':href' => 'CDATA',
                $E['XLINK.prefix'] . ':type' => 'CDATA',
                'xml:lang' => 'CDATA',
                'xml:space' => 'Enum#default,preserve',
                'id' => 'CDATA', // MathML allows multiple elements with same ID
                'xref' => 'CDATA',
                'class' => 'CDATA',
                'style' => 'CDATA',
                'href' => 'CDATA',
                'other' => 'CDATA',
            ],
            $proprietary_att_wrs,
            $proprietary_att_dsi,
        );

        // These two sets of attrs appear commonly together.
        // For conciseness and efficiency we merge them here once:
        $CDEAtt = array_merge(
            $E['CommonAtt'],
            $E['DefEncAtt'],
        );

        $this->addElement(
            'cn',
            $default_display,
            'Custom: (#PCDATA|mglyph|sep|' . $E['PresentationExpression'] . ')*',
            [],
            array_merge(
                $CDEAtt,
                [
                    'type' => 'CDATA',
                    'base' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'ci',
            $default_display,
            'Custom: (#PCDATA|mglyph|' . $E['PresentationExpression'] . ')*',
            [],
            array_merge(
                $CDEAtt,
                [
                    'type' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'csymbol',
            $default_display,
            'Custom: (#PCDATA|mglyph|' . $E['PresentationExpression'] . ')*',
            [],
            array_merge(
                $CDEAtt,
                [
                    'type' => 'CDATA',
                    'cd' => 'CDATA',
                ],
            ),
        );

        $E['SymbolName'] = '#PCDATA';
        $E['BvarQ'] = '(bvar)*';
        $E['DomainQ'] = '(domainofapplication|condition|(lowlimit,uplimit?))*';
        $E['constant-arith.mmlclass'] = 'exponentiale|imaginaryi|notanumber' .
            '|true|false|pi|eulergamma|infinity';
        $E['constant-set.mmlclass'] = 'integers|reals|rationals' .
            '|naturalnumbers|complexes|primes|emptyset';
        $E['binary-linalg.mmlclass'] = 'vectorproduct|scalarproduct' .
            '|outerproduct';
        $E['nary-linalg.mmlclass'] = 'selector';
        $E['unary-linalg.mmlclass'] = 'determinant|transpose';
        $E['nary-constructor.mmlclass'] = 'vector|matrix|matrixrow';
        $E['nary-stats.mmlclass'] = 'mean|sdev|variance|median|mode';
        $E['unary-elementary.mmlclass'] = 'sin|cos|tan|sec|csc|cot|sinh|cosh|tanh|sech' .
            '|csch|coth|arcsin|arccos|arctan|arccosh|arccot|arccoth|arccsc' .
            '|arccsch|arcsec|arcsech|arcsinh|arctanh';
        $E['limit.mmlclass'] = 'limit';
        $E['product.mmlclass'] = 'product';
        $E['sum.mmlclass'] = 'sum';
        $E['unary-set.mmlclass'] = 'card';
        $E['nary-set-reln.mmlclass'] = 'subset|prsubset';
        $E['binary-set.mmlclass'] = 'in|notin|notsubset|notprsubset|setdiff';
        $E['nary-set.mmlclass'] = 'union|intersect|cartesianproduct';
        $E['nary-setlist-constructor.mmlclass'] = 'set|list';
        $E['unary-veccalc.mmlclass'] = 'divergence|grad|curl|laplacian';
        $E['partialdiff.mmlclass'] = 'partialdiff';
        $E['Differential-Operator.mmlclass'] = 'diff';
        $E['int.mmlclass'] = 'int';
        $E['binary-reln.mmlclass'] = 'neq|approx|factorof|tendsto';
        $E['nary-reln.mmlclass'] = 'eq|gt|lt|geq|leq';
        $E['quantifier.mmlclass'] = 'forall|exists';
        $E['binary-logical.mmlclass'] = 'implies|equivalent';
        $E['unary-logical.mmlclass'] = 'not';
        $E['nary-logical.mmlclass'] = 'and|or|xor';
        $E['nary-arith.mmlclass'] = 'plus|times|gcd|lcm';
        $E['nary-minmax.mmlclass'] = 'max|min';
        $E['unary-arith.mmlclass'] = 'factorial|abs|conjugate|arg|real' .
            '|imaginary|floor|ceiling|exp';
        $E['binary-arith.mmlclass'] = 'quotient|divide|minus|power|rem|root';
        $E['nary-functional.mmlclass'] = 'compose';
        $E['lambda.mmlclass'] = 'lambda';
        $E['unary-functional.mmlclass'] = 'inverse|ident|domain|codomain' .
            '|image|ln|log|moment';
        $E['interval.mmlclass'] = 'interval';
        $E['DeprecatedContExp'] = 'reln|fn|declare';
        $E['Qualifier'] = '(' . $E['DomainQ'] . ')|degree|momentabout|logbase';
        $E['ContExp'] = 'piecewise|' .
            $E['DeprecatedContExp'] .
            '|' . $E['interval.mmlclass'] .
            '|' . $E['unary-functional.mmlclass'] .
            '|' . $E['lambda.mmlclass'] .
            '|' . $E['nary-functional.mmlclass'] .
            '|' . $E['binary-arith.mmlclass'] .
            '|' . $E['unary-arith.mmlclass'] .
            '|' . $E['nary-minmax.mmlclass'] .
            '|' . $E['nary-arith.mmlclass'] .
            '|' . $E['nary-logical.mmlclass'] .
            '|' . $E['unary-logical.mmlclass'] .
            '|' . $E['binary-logical.mmlclass'] .
            '|' . $E['quantifier.mmlclass'] .
            '|' . $E['nary-reln.mmlclass'] .
            '|' . $E['binary-reln.mmlclass'] .
            '|' . $E['int.mmlclass'] .
            '|' . $E['Differential-Operator.mmlclass'] .
            '|' . $E['partialdiff.mmlclass'] .
            '|' . $E['unary-veccalc.mmlclass'] .
            '|' . $E['nary-setlist-constructor.mmlclass'] .
            '|' . $E['nary-set.mmlclass'] .
            '|' . $E['binary-set.mmlclass'] .
            '|' . $E['nary-set-reln.mmlclass'] .
            '|' . $E['unary-set.mmlclass'] .
            '|' . $E['sum.mmlclass'] .
            '|' . $E['product.mmlclass'] .
            '|' . $E['limit.mmlclass'] .
            '|' . $E['unary-elementary.mmlclass'] .
            '|' . $E['nary-stats.mmlclass'] .
            '|' . $E['nary-constructor.mmlclass'] .
            '|' . $E['unary-linalg.mmlclass'] .
            '|' . $E['nary-linalg.mmlclass'] .
            '|' . $E['binary-linalg.mmlclass'] .
            '|' . $E['constant-set.mmlclass'] .
            '|' . $E['constant-arith.mmlclass'] .
            '|semantics|cn|ci|csymbol|apply|bind|share|cerror|cbytes|cs';

        $E['apply.content'] = '(' . $E['ContExp'] . '),(' . $E['BvarQ'] .
            '),(' . $E['Qualifier'] . ')*,(' . $E['ContExp'] . ')*';

        $this->addElement(
            'apply',
            $default_display,
            'Custom: ' . $E['apply.content'],
            [],
            $E['CommonAtt'],
        );

        $this->addElement(
            'bind',
            $default_display,
            'Custom: ' . $E['apply.content'],
            [],
            $E['CommonAtt'],
        );

        $this->addElement(
            'share',
            $default_display,
            'Empty',
            [],
            array_merge(
                $E['CommonAtt'],
                ['src' => 'CDATA'],
            ),
        );

        $this->addElement(
            'cerror',
            $default_display,
            'Custom: (csymbol,(' . $E['ContExp'] . ')*)',
            [],
            $E['CommonAtt'],
        );

        $this->addElement(
            'cbytes',
            $default_display,
            // The * is not in the DTD but we add it to allow empty tag
            'Custom: (#PCDATA)*',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'cs',
            $default_display,
            // The * is not in the DTD but we add it to allow empty tag
            'Custom: (#PCDATA)*',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'bvar',
            $default_display,
            'Custom: ((degree,(ci|semantics))|((ci|semantics),(degree)?))',
            [],
            $E['CommonAtt'],
        );

        $this->addElement(
            'sep',
            $default_display,
            'Empty',
            [],
            [],
        );

        $this->addElement(
            'domainofapplication',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'condition',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'uplimit',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'lowlimit',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'degree',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'momentabout',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'logbase',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'piecewise',
            $default_display,
            'Custom: (piece|otherwise)*',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'piece',
            $default_display,
            'Custom: ((' . $E['ContExp'] . '),(' . $E['ContExp'] . '))',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'otherwise',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'reln',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')*',
            [],
            [],
        );

        $this->addElement(
            'fn',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')',
            [],
            [],
        );

        $this->addElement(
            'declare',
            $default_display,
            'Custom: (' . $E['ContExp'] . ')+',
            [],
            array_merge(
                [
                    'type' => 'CDATA',
                    'scope' => 'CDATA',
                    'nargs' => 'CDATA',
                    'occurrence' => 'Enum#prefix,infix,function-model',
                ],
                $E['DefEncAtt'],
            ),
        );

        $this->addElement(
            'interval',
            $default_display,
            'Custom: ((' . $E['ContExp'] . '),(' . $E['ContExp'] . '))',
            [],
            array_merge(
                $CDEAtt,
                ['closure' => 'CDATA'],
            ),
        );

        $this->addElement(
            'inverse',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'ident',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'domain',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'codomain',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'image',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'ln',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'log',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'moment',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'lambda',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
            $E['ContExp'] . '))',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'compose',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'quotient',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'divide',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'minus',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'power',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'rem',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'root',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'factorial',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'abs',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'conjugate',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arg',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'real',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'imaginary',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'floor',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'ceiling',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'exp',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'max',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'min',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'plus',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'times',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'gcd',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'lcm',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'and',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'or',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'xor',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'not',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'implies',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'equivalent',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'forall',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'exists',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'eq',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'gt',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'lt',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'geq',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'leq',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'neq',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'approx',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'factorof',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'tendsto',
            $default_display,
            'Empty',
            [],
            array_merge(
                $CDEAtt,
                ['type' => 'CDATA'],
            ),
        );

        $this->addElement(
            'int',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'diff',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'partialdiff',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'divergence',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'grad',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'curl',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'laplacian',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'set',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . ')*,(' . $E['DomainQ'] . ')*,(' .
            $E['ContExp'] . ')*)',
            [],
            array_merge(
                $CDEAtt,
                ['type' => 'CDATA'],
            ),
        );

        $this->addElement(
            'list',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . ')*,(' . $E['DomainQ'] . ')*,(' .
            $E['ContExp'] . ')*)',
            [],
            array_merge(
                $CDEAtt,
                ['order' => 'Enum#numeric,lexicographic'],
            ),
        );

        $this->addElement(
            'union',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'intersect',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'cartesianproduct',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'in',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'notin',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'notsubset',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'notprsubset',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'setdiff',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'subset',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'prsubset',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'card',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sum',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'product',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'limit',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sin',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'cos',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'tan',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sec',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'csc',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'cot',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sinh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'cosh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'tanh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sech',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'csch',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'coth',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arcsin',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccos',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arctan',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccosh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccot',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccoth',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccsc',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arccsch',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arcsec',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arcsech',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arcsinh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'arctanh',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'mean',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'sdev',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'variance',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'median',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'mode',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'vector',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
            $E['ContExp'] . ')*)',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'matrix',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
            $E['ContExp'] . ')*)',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'matrixrow',
            $default_display,
            'Custom: ((' . $E['BvarQ'] . '),(' . $E['DomainQ'] . '),(' .
            $E['ContExp'] . ')*)',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'determinant',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'transpose',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'selector',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'vectorproduct',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'scalarproduct',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'outerproduct',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'integers',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'reals',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'rationals',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'naturalnumbers',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'complexes',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'primes',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'emptyset',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'exponentiale',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'imaginaryi',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'notanumber',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'true',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'false',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'pi',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'eulergamma',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $this->addElement(
            'infinity',
            $default_display,
            'Empty',
            [],
            $CDEAtt,
        );

        $E['MathExpression'] = $E['ContExp'] .
            '|' . $E['PresentationExpression'];
        $E['ImpliedMrow'] = '(' . $E['MathExpression'] . ')*';
        $E['TableRowExpression'] = 'mtr|mlabeledtr';
        $E['TableCellExpression'] = 'mtd';
        $E['MstackExpression'] = $E['MathExpression'] .
            '|mscarries|msline|msrow|msgroup';
        $E['MsrowExpression'] = $E['MathExpression'] . '|none';
        $E['MultiscriptExpression'] = '(' .
            $E['MathExpression'] . '|none),(' .
            $E['MathExpression'] . '|none)';

        $E['mpadded-length'] = 'CDATA';
        $E['linestyle'] = 'Enum#none,solid,dashed';
        $E['columnalignstyle'] = 'Enum#left,center,right';
        $E['unsigned-integer'] = 'CDATA';
        $E['integer'] = 'CDATA';
        $E['number'] = 'CDATA';
        $E['character'] = 'CDATA';
        $E['color'] = 'CDATA';
        $E['positive-integer'] = 'CDATA';

        $E['token.content'] = '#PCDATA|mglyph|malignmark';

        $E['length'] = 'CDATA';
        $E['DeprecatedTokenAtt'] = [
            'fontfamily' => 'CDATA',
            'fontweight' => 'Enum#normal,bold',
            'fontstyle' => 'Enum#normal,italic',
            'fontsize' => $E['length'],
            'color' => $E['color'],
            'background' => 'CDATA',
        ];
        $E['TokenAtt'] = array_merge(
            [
                'mathvariant' => 'Enum#normal,bold,italic,bold-italic' .
                    ',double-struck,bold-fraktur,script,bold-script,fraktur' .
                    ',sans-serif,bold-sans-serif,sans-serif-italic' .
                    ',sans-serif-bold-italic,monospace,initial,tailed,looped' .
                    ',stretched',
                'mathsize' => 'CDATA',
                'dir' => 'Enum#ltr,rtl',
            ],
            $E['DeprecatedTokenAtt'],
        );
        $E['CommonPresAtt'] = [
            'mathcolor' => $E['color'],
            'mathbackground' => 'CDATA',
        ];

        // These sets of attrs appear commonly together.
        // For conciseness and efficiency we merge them here once:
        $CCPAtt = array_merge(
            $E['CommonAtt'],
            $E['CommonPresAtt'],
        );
        $CCPTAtt = array_merge(
            $CCPAtt,
            $E['TokenAtt'],
        );

        $this->addElement(
            'mi',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            [],
            $CCPTAtt,
        );

        $this->addElement(
            'mn',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            [],
            $CCPTAtt,
        );

        $this->addElement(
            'mo',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            [],
            array_merge(
                $CCPTAtt,
                [
                    'form' => 'Enum#prefix,infix,postfix',
                    'fence' => 'Enum#true,false',
                    'separator' => 'Enum#true,false',
                    'lspace' => $E['length'],
                    'rspace' => $E['length'],
                    'stretchy' => 'Enum#true,false',
                    'symmetric' => 'Enum#true,false',
                    'maxsize' => 'CDATA',
                    'minsize' => $E['length'],
                    'largeop' => 'Enum#true,false',
                    'movablelimits' => 'Enum#true,false',
                    'accent' => 'Enum#true,false',
                    'linebreak' => 'Enum#auto,newline,nobreak,goodbreak' .
                        ',badbreak',
                    'lineleading' => $E['length'],
                    'linebreakstyle' => 'Enum#before,after,duplicate' .
                        ',infixlinebreakstyle',
                    'linebreakmultchar' => 'CDATA',
                    'indentalign' => 'Enum#left,center,right,auto,id',
                    'indentshift' => $E['length'],
                    'indenttarget' => 'CDATA',
                    'indentalignfirst' => 'Enum#left,center,right,auto,id' .
                        ',indentalign',
                    'indentshiftfirst' => 'CDATA',
                    'indentalignlast' => 'Enum#left,center,right,auto,id' .
                        ',indentalign',
                    'indentshiftlast' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'mtext',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            [],
            $CCPTAtt,
        );

        $this->addElement(
            'mspace',
            $default_display,
            'Empty',
            [],
            array_merge(
                $CCPTAtt,
                [
                    'width' => $E['length'],
                    'height' => $E['length'],
                    'depth' => $E['length'],
                    'linebreak' => 'Enum#auto,newline,nobreak,goodbreak' .
                        ',badbreak,indentingnewline',
                    'indentalign' => 'Enum#left,center,right,auto,id',
                    'indentshift' => $E['length'],
                    'indenttarget' => 'CDATA',
                    'indentalignfirst' => 'Enum#left,center,right,auto,id' .
                        ',indentalign',
                    'indentshiftfirst' => 'CDATA',
                    'indentalignlast' => 'Enum#left,center,right,auto,id' .
                        ',indentalign',
                    'indentshiftlast' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'ms',
            $default_display,
            'Custom: (' . $E['token.content'] . ')*',
            [],
            array_merge(
                $CCPTAtt,
                [
                    'lquote' => 'CDATA',
                    'rquote' => 'CDATA',
                ],
            ),
        );

        $E['mglyph.deprecatedattributes'] = array_merge(
            [
                'index' => $E['integer'],
                'mathvariant' => 'Enum#normal,bold,italic,bold-italic' .
                    ',double-struck,bold-fraktur,script,bold-script,fraktur' .
                    ',sans-serif,bold-sans-serif,sans-serif-italic' .
                    ',sans-serif-bold-italic,monospace,initial,tailed,looped' .
                    ',stretched',
                'mathsize' => 'CDATA',
            ],
            $E['DeprecatedTokenAtt'],
        );

        $E['mglyph.attributes'] = array_merge(
            $CCPAtt,
            [
                'src' => 'CDATA',
                'width' => $E['length'],
                'height' => $E['length'],
                'valign' => $E['length'],
                'alt' => 'CDATA',
            ],
        );

        $this->addElement(
            'mglyph',
            $default_display,
            'Empty',
            [],
            array_merge(
                $E['mglyph.attributes'],
                $E['mglyph.deprecatedattributes'],
            ),
        );

        $this->addElement(
            'msline',
            $default_display,
            'Empty',
            [],
            array_merge(
                $CCPAtt,
                [
                    'position' => $E['integer'],
                    'length' => $E['unsigned-integer'],
                    'leftoverhang' => $E['length'],
                    'rightoverhang' => $E['length'],
                    'mslinethickness' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'none',
            $default_display,
            'Empty',
            [],
            $CCPAtt,
        );

        $this->addElement(
            'mprescripts',
            $default_display,
            'Empty',
            [],
            $CCPAtt,
        );

        $this->addElement(
            'malignmark',
            $default_display,
            'Empty',
            [],
            array_merge(
                $CCPAtt,
                ['edge' => 'Enum#left,right'],
            ),
        );

        $this->addElement(
            'maligngroup',
            $default_display,
            'Empty',
            [],
            array_merge(
                $CCPAtt,
                ['groupalign' => 'Enum#left,right,right,decimalpoint'],
            ),
        );

        $this->addElement(
            'mrow',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                ['dir' => 'Enum#ltr,rtl'],
            ),
        );

        $this->addElement(
            'mfrac',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                [
                    'linethickness' => 'CDATA',
                    'numalign' => 'Enum#left,center,right',
                    'denomalign' => 'Enum#left,center,right',
                    'bevelled' => 'Enum#true,false',
                ],
            ),
        );

        $this->addElement(
            'msqrt',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            $CCPAtt,
        );

        $this->addElement(
            'mroot',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            $CCPAtt,
        );

        $E['mstyle.deprecatedattributes'] = array_merge(
            $E['DeprecatedTokenAtt'],
            [
                'veryverythinmathspace' => $E['length'],
                'verythinmathspace' => $E['length'],
                'thinmathspace' => $E['length'],
                'mediummathspace' => $E['length'],
                'thickmathspace' => $E['length'],
                'verythickmathspace' => $E['length'],
                'veryverythickmathspace' => $E['length'],
            ],
        );

        $E['mstyle.generalattributes'] = [
            'accent' => 'Enum#true,false',
            'accentunder' => 'Enum#true,false',
            'align' => 'Enum#left,right,center',
            'alignmentscope' => 'CDATA',
            'bevelled' => 'Enum#true,false',
            'charalign' => 'Enum#left,center,right',
            'charspacing' => 'CDATA',
            'close' => 'CDATA',
            'columnalign' => 'CDATA',
            'columnlines' => 'CDATA',
            'columnspacing' => 'CDATA',
            'columnspan' => $E['positive-integer'],
            'columnwidth' => 'CDATA',
            'crossout' => 'CDATA',
            'denomalign' => 'Enum#left,center,right',
            'depth' => $E['length'],
            'dir' => 'Enum#ltr,rtl',
            'edge' => 'Enum#left,right',
            'equalcolumns' => 'Enum#true,false',
            'equalrows' => 'Enum#true,false',
            'fence' => 'Enum#true,false',
            'form' => 'Enum#prefix,infix,postfix',
            'frame' => $E['linestyle'],
            'framespacing' => 'CDATA',
            'groupalign' => 'CDATA',
            'height' => $E['length'],
            'indentalign' => 'Enum#left,center,right,auto,id',
            'indentalignfirst' => 'Enum#left,center,right,auto,id,indentalign',
            'indentalignlast' => 'Enum#left,center,right,auto,id,indentalign',
            'indentshift' => $E['length'],
            'indentshiftfirst' => 'CDATA',
            'indentshiftlast' => 'CDATA',
            'indenttarget' => 'CDATA',
            'largeop' => 'Enum#true,false',
            'leftoverhang' => $E['length'],
            'length' => $E['unsigned-integer'],
            'linebreak' => 'Enum#auto,newline,nobreak,goodbreak,badbreak',
            'linebreakmultchar' => 'CDATA',
            'linebreakstyle' => 'Enum#before,after,duplicate' .
                ',infixlinebreakstyle',
            'lineleading' => $E['length'],
            'linethickness' => 'CDATA',
            'location' => 'Enum#w,nw,n,ne,e,se,s,sw',
            'longdivstyle' => 'CDATA',
            'lquote' => 'CDATA',
            'lspace' => $E['length'],
            'mathsize' => 'CDATA',
            'mathvariant' => 'Enum#normal,bold,italic,bold-italic' .
                ',double-struck,bold-fraktur,script,bold-script,fraktur' .
                ',sans-serif,bold-sans-serif,sans-serif-italic' .
                ',sans-serif-bold-italic,monospace,initial,tailed,looped' .
                ',stretched',
            'maxsize' => 'CDATA',
            'minlabelspacing' => $E['length'],
            'minsize' => $E['length'],
            'movablelimits' => 'Enum#true,false',
            'mslinethickness' => 'CDATA',
            'notation' => 'CDATA',
            'numalign' => 'Enum#left,center,right',
            'open' => 'CDATA',
            'position' => $E['integer'],
            'rightoverhang' => $E['length'],
            'rowalign' => 'CDATA',
            'rowlines' => 'CDATA',
            'rowspacing' => 'CDATA',
            'rowspan' => $E['positive-integer'],
            'rquote' => 'CDATA',
            'rspace' => $E['length'],
            'selection' => $E['positive-integer'],
            'separator' => 'Enum#true,false',
            'separators' => 'CDATA',
            'shift' => $E['integer'],
            'side' => 'Enum#left,right,leftoverlap,rightoverlap',
            'stackalign' => 'Enum#left,center,right,decimalpoint',
            'stretchy' => 'Enum#true,false',
            'subscriptshift' => $E['length'],
            'superscriptshift' => $E['length'],
            'symmetric' => 'Enum#true,false',
            'valign' => $E['length'],
            'width' => $E['length'],
        ];

        $E['mstyle.specificattributes'] = [
            'scriptlevel' => $E['integer'],
            'displaystyle' => 'Enum#true,false',
            'scriptsizemultiplier' => $E['number'],
            'scriptminsize' => $E['length'],
            'infixlinebreakstyle' => 'Enum#before,after,duplicate',
            'decimalpoint' => $E['character'],
        ];

        $this->addElement(
            'mstyle',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            array_merge(
                $CCPAtt,
                $E['mstyle.specificattributes'],
                $E['mstyle.generalattributes'],
                $E['mstyle.deprecatedattributes'],
            ),
        );

        $this->addElement(
            'merror',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            $CCPAtt,
        );

        $this->addElement(
            'mpadded',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            array_merge(
                $CCPAtt,
                [
                    'height' => $E['mpadded-length'],
                    'depth' => $E['mpadded-length'],
                    'width' => $E['mpadded-length'],
                    'lspace' => $E['mpadded-length'],
                    'voffset' => $E['mpadded-length'],
                ],
            ),
        );

        $this->addElement(
            'mphantom',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            $CCPAtt,
        );

        $this->addElement(
            'mfenced',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                [
                    'open' => 'CDATA',
                    'close' => 'CDATA',
                    'separators' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'menclose',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            array_merge(
                $CCPAtt,
                ['notation' => 'CDATA' ],
            ),
        );

        $this->addElement(
            'msub',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                ['subscriptshift' => $E['length']],
            ),
        );

        $this->addElement(
            'msup',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                ['superscriptshift' => $E['length']],
            ),
        );

        $E['msubsup.attributes'] = array_merge(
            $CCPAtt,
            [
                'subscriptshift' => $E['length'],
                'superscriptshift' => $E['length'],
            ],
        );

        $this->addElement(
            'msubsup',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            $E['msubsup.attributes'],
        );

        $this->addElement(
            'munder',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                [
                    'accentunder' => 'Enum#true,false',
                    'align' => 'Enum#left,right,center',
                ],
            ),
        );

        $this->addElement(
            'mover',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                [
                    'accent' => 'Enum#true,false',
                    'align' => 'Enum#left,right,center',
                ],
            ),
        );

        $this->addElement(
            'munderover',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '),(' .
            $E['MathExpression'] . '))',
            [],
            array_merge(
                $CCPAtt,
                [
                    'accent' => 'Enum#true,false',
                    'accentunder' => 'Enum#true,false',
                    'align' => 'Enum#left,right,center',
                ],
            ),
        );

        $this->addElement(
            'mmultiscripts',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . '),(' .
            $E['MultiscriptExpression'] . ')*,(mprescripts,(' .
            $E['MultiscriptExpression'] . ')*)?)',
            [],
            $E['msubsup.attributes'],
        );

        $this->addElement(
            'mtable',
            $default_display,
            'Custom: (' . $E['TableRowExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                [
                    'align' => 'CDATA',
                    'rowalign' => 'CDATA',
                    'columnalign' => 'CDATA',
                    'groupalign' => 'CDATA',
                    'alignmentscope' => 'CDATA',
                    'columnwidth' => 'CDATA',
                    'width' => 'CDATA',
                    'rowspacing' => 'CDATA',
                    'columnspacing' => 'CDATA',
                    'rowlines' => 'CDATA',
                    'columnlines' => 'CDATA',
                    'frame' => $E['linestyle'],
                    'framespacing' => 'CDATA',
                    'equalrows' => 'Enum#true,false',
                    'equalcolumns' => 'Enum#true,false',
                    'displaystyle' => 'Enum#true,false',
                    'side' => 'Enum#left,right,leftoverlap,rightoverlap',
                    'minlabelspacing' => $E['length'],
                ],
            ),
        );

        $E['mtr.attributes'] = array_merge(
            $CCPAtt,
            [
                'rowalign' => 'Enum#top,bottom,center,baseline,axis',
                'columnalign' => 'CDATA',
                'groupalign' => 'CDATA',
            ],
        );

        $this->addElement(
            'mlabeledtr',
            $default_display,
            'Custom: (' . $E['TableCellExpression'] . ')+',
            [],
            $E['mtr.attributes'],
        );

        $this->addElement(
            'mtr',
            $default_display,
            'Custom: (' . $E['TableCellExpression'] . ')+',
            [],
            $E['mtr.attributes'],
        );

        $this->addElement(
            'mtd',
            $default_display,
            'Custom: (' . $E['ImpliedMrow'] . ')',
            [],
            array_merge(
                $CCPAtt,
                [
                    'rowspan' => $E['positive-integer'],
                    'columnspan' => $E['positive-integer'],
                    'rowalign' => 'Enum#top,bottom,center,baseline,axis',
                    'columnalign' => $E['columnalignstyle'],
                    'groupalign' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'mstack',
            $default_display,
            'Custom: (' . $E['MstackExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                [
                    'align' => 'CDATA',
                    'stackalign' => 'Enum#left,center,right,decimalpoint',
                    'charalign' => 'Enum#left,center,right',
                    'charspacing' => 'CDATA',
                ],
            ),
        );

        $E['msgroup.attributes'] = array_merge(
            $CCPAtt,
            [
                'position' => $E['integer'],
                'shift' => $E['integer'],
            ],
        );

        $this->addElement(
            'mlongdiv',
            $default_display,
            'Custom: ((' . $E['MstackExpression'] . '),(' .
            $E['MstackExpression'] . '),(' . $E['MstackExpression'] . ')+)',
            [],
            array_merge(
                $E['msgroup.attributes'],
                ['longdivstyle' => 'CDATA'],
            ),
        );

        $this->addElement(
            'msgroup',
            $default_display,
            'Custom: (' . $E['MstackExpression'] . ')*',
            [],
            $E['msgroup.attributes'],
        );

        $this->addElement(
            'msrow',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                ['position' => $E['integer']],
            ),
        );

        $this->addElement(
            'mscarries',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . '|mscarry)*',
            [],
            array_merge(
                $CCPAtt,
                [
                    'position' => $E['integer'],
                    'location' => 'Enum#w,nw,n,ne,e,se,s,sw',
                    'crossout' => 'CDATA',
                    'scriptsizemultiplier' => $E['number'],
                ],
            ),
        );

        $this->addElement(
            'mscarry',
            $default_display,
            'Custom: (' . $E['MsrowExpression'] . ')*',
            [],
            array_merge(
                $CCPAtt,
                [
                    'location' => 'Enum#w,nw,n,ne,e,se,s,sw',
                    'crossout' => 'CDATA',
                ],
            ),
        );

        $this->addElement(
            'maction',
            $default_display,
            'Custom: (' . $E['MathExpression'] . ')+',
            [],
            array_merge(
                $CCPAtt,
                [
                    'actiontype*' => 'CDATA',
                    'selection' => $E['positive-integer'],
                ],
            ),
        );

        $E['math.deprecatedattributes'] = [
            'mode' => 'CDATA',
            'macros' => 'CDATA',
        ];

        $this->addElement(
            'math',
            // The specification allows math to be either inline or block
            // according to the display parameter or infer it from context.
            // We set it to Flow so that it can be inside both elements that
            // allow inline, and elements that allow block
            'Flow',
            'Custom: (' . $E['MathExpression'] . ')*',
            [],
            array_merge(
                $E['CommonAtt'],
                [
                    'display' => 'Enum#block,inline',
                    'maxwidth' => $E['length'],
                    'overflow' => 'Enum#linebreak,scroll,elide,truncate,scale',
                    'altimg' => 'CDATA',
                    'altimg-width' => $E['length'],
                    'altimg-height' => $E['length'],
                    'altimg-valign' => 'CDATA',
                    'alttext' => 'CDATA',
                    'cdgroup' => 'CDATA',
                ],
                $E['math.deprecatedattributes'],
                $E['CommonPresAtt'],
                $E['mstyle.specificattributes'],
                $E['mstyle.generalattributes'],
            ),
        );

        $E['annotation.attributes'] = array_merge(
            $E['CommonAtt'],
            [
                'cd' => 'CDATA',
                'name' => 'CDATA',
            ],
            $E['DefEncAtt'],
            ['src' => 'CDATA'],
        );

        $this->addElement(
            'annotation',
            $default_display,
            // The * is not in the DTD but we add it to allow empty tag
            'Custom: (#PCDATA)*',
            [],
            $E['annotation.attributes'],
        );

        $this->addElement(
            'annotation-xml',
            $default_display,
            'Custom: ((' . $E['MathExpression'] . ')*)',
            [],
            $E['annotation.attributes'],
        );

        $this->addElement(
            'semantics',
            $default_display,
            'Custom: ((' . $E['MathExpression'] .
            '),(annotation|annotation-xml)*)',
            [],
            array_merge(
                $CDEAtt,
                [
                    'cd' => 'CDATA',
                    'name' => 'CDATA',
                ],
            ),
        );
    }
}
