<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Namespaces_ConstantFullyQualified extends Analyzer {
    /* 2 methods */

    public function testNamespaces_ConstantFullyQualified01()  { $this->generic_test('Namespaces_ConstantFullyQualified.01'); }
    public function testNamespaces_ConstantFullyQualified02()  { $this->generic_test('Namespaces_ConstantFullyQualified.02'); }
}
?>