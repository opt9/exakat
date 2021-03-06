<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Classes_UsedClass extends Analyzer {
    /* 5 methods */

    public function testClasses_UsedClass01()  { $this->generic_test('Classes_UsedClass.01'); }
    public function testClasses_UsedClass02()  { $this->generic_test('Classes_UsedClass.02'); }
    public function testClasses_UsedClass03()  { $this->generic_test('Classes/UsedClass.03'); }
    public function testClasses_UsedClass04()  { $this->generic_test('Classes/UsedClass.04'); }
    public function testClasses_UsedClass05()  { $this->generic_test('Classes/UsedClass.05'); }
}
?>