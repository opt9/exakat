<?php

namespace Analyzer\Namespaces;

use Analyzer;

class Alias extends Analyzer\Analyzer {

    function analyze() {
        $this->atomIs("Use")
             ->out('AS');
    }
}

?>