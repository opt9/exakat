<?php

namespace Analyzer\Structures;

use Analyzer;

class DynamicCode extends Analyzer\Analyzer {
    public function analyze() {
        // $$v
        $this->atomIs('Variable')
             ->outIs('NAME')
             ->tokenIsNot('T_STRING')
             ->back('first');
        $this->prepareQuery();

        // $v['a' . 'b']
        $this->atomIs('Array')
             ->outIs('INDEX')
             ->atomIsNot(array('Integer', 'String', 'Identifier', 'Boolean'))
             ->back('first');
        $this->prepareQuery();

        // v('a' . 'b')
        $this->atomIs('Array')
             ->outIs('INDEX')
             ->atomIsNot(array('Integer', 'String', 'Identifier', 'Boolean'))
             ->back('first');
        $this->prepareQuery();

        // $o->$p();
        $this->atomIs('Methodcall')
             ->outIs('METHOD')
             ->tokenIsNot(array('T_STRING', 'T_NS_SEPARATOR'))
             ->back('first');
        $this->prepareQuery();

        //$classname::$methodcall();
        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->tokenIsNot(array('T_STRING', 'T_NS_SEPARATOR'))
             ->back('first');
        $this->prepareQuery();
        
        $this->atomIs('Staticmethodcall')
             ->outIs('METHOD')
             ->tokenIsNot(array('T_STRING', 'T_NS_SEPARATOR'))
             ->back('first');
        $this->prepareQuery();

        //$functioncall(2,3,3);
        //new $classname(); (also done here)
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIsNot(array('T_STRING', 'T_NS_SEPARATOR', 'T_ISSET', 'T_ARRAY', 'T_EMPTY', 'T_LIST', 'T_UNSET'))
             ->back('first');
        $this->prepareQuery();

        // functioncall(2 + 2);
        $this->atomIs('Functioncall')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->tokenIsNot(array('T_STRING', 'T_NS_SEPARATOR', 'T_VOID', 'T_INTEGER', 'T_CONSTANT_ENCAPSED_STRING', 'T_VARIABLE', 'T_LNUMBER'))
             ->back('first');
        $this->prepareQuery();
        
        // class_alias, extract
        // functioncall(2 + 2);
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath(array('\\class_alias', '\\extract', '\\parse_str'));
        $this->prepareQuery();
    }
}

?>
