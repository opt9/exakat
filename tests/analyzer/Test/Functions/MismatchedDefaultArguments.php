<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_MismatchedDefaultArguments extends Analyzer {
    /* 4 methods */

    public function testFunctions_MismatchedDefaultArguments01()  { $this->generic_test('Functions/MismatchedDefaultArguments.01'); }
    public function testFunctions_MismatchedDefaultArguments02()  { $this->generic_test('Functions/MismatchedDefaultArguments.02'); }
    public function testFunctions_MismatchedDefaultArguments03()  { $this->generic_test('Functions/MismatchedDefaultArguments.03'); }
    public function testFunctions_MismatchedDefaultArguments04()  { $this->generic_test('Functions/MismatchedDefaultArguments.04'); }
}
?>