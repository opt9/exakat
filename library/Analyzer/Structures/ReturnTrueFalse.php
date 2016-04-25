<?php

namespace Analyzer\Structures;

use Analyzer;

class ReturnTrueFalse extends Analyzer\Analyzer {
    public function analyze() {
        // If ($a == 2) { return true; } else { return false; } 
        // If ($a == 2) { return false; } else { return true; } 
        $this->atomIs('Ifthen')

             ->outIs('THEN')
             ->outIs('ELEMENT')
             ->atomIs('Return')
             ->outIs('RETURN')
             ->atomIs('Boolean')
             ->code(array('true', 'false'))
             ->savePropertyAs('code', 'first')
             ->inIs('RETURN')
             ->inIs('ELEMENT')
             ->inIs('THEN')

             ->outIs('ELSE')
             ->outIs('ELEMENT')
             ->atomIs('Return')
             ->outIs('RETURN')
             ->atomIs('Boolean')
             ->code(array('true', 'false'))
             ->notSamePropertyAs('code', 'first')

             ->back('first');
        $this->prepareQuery();

        // If ($a == 2) { $b = true; } else { $b = false; } 
        // If ($a == 2) { $b = false; } else { $b = true; } 
        $this->atomIs('Ifthen')

             ->outIs('THEN')
             ->outIs('ELEMENT')
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'container')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->atomIs('Boolean')

             ->code(array('true', 'false'))
             ->savePropertyAs('code', 'first')
             ->inIs('RIGHT')
             ->inIs('ELEMENT')
             ->inIs('THEN')

             ->outIs('ELSE')
             ->outIs('ELEMENT')
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->samePropertyAs('fullcode', 'container')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->atomIs('Boolean')
             ->code(array('true', 'false'))
             ->notSamePropertyAs('code', 'first')

             ->back('first');
        $this->prepareQuery();

        // $a = ($b == 2) ? true : false;
        // $a = ($b == 2) ? false : true;
        $this->atomIs('Assignation')
             ->outIs('RIGHT')
             ->atomIs('Ternary')

             ->outIs('THEN')
             ->atomIs('Boolean')
             ->code(array('true', 'false'))
             ->savePropertyAs('code', 'first')
             ->inIs('THEN')

             ->outIs('ELSE')
             ->atomIs('Boolean')
             ->code(array('true', 'false'))
             ->notSamePropertyAs('code', 'first')

             ->back('first');
        $this->prepareQuery();
    }
}

?>