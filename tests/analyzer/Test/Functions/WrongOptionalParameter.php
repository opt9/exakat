<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_WrongOptionalParameter extends Analyzer {
    /* 7 methods */

    public function testFunctions_WrongOptionalParameter01()  { $this->generic_test('Functions_WrongOptionalParameter.01'); }
    public function testFunctions_WrongOptionalParameter02()  { $this->generic_test('Functions_WrongOptionalParameter.02'); }
    public function testFunctions_WrongOptionalParameter03()  { $this->generic_test('Functions_WrongOptionalParameter.03'); }
    public function testFunctions_WrongOptionalParameter04()  { $this->generic_test('Functions/WrongOptionalParameter.04'); }
    public function testFunctions_WrongOptionalParameter05()  { $this->generic_test('Functions/WrongOptionalParameter.05'); }
    public function testFunctions_WrongOptionalParameter06()  { $this->generic_test('Functions/WrongOptionalParameter.06'); }
    public function testFunctions_WrongOptionalParameter07()  { $this->generic_test('Functions/WrongOptionalParameter.07'); }
}
?>