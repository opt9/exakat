<?php

$expected     = array('$a->p == 1 && $a->p == 3',
                      '$a->p == 1 AND $a->p == 4',
                      '$a->p == 1 and $a->p == 2',
                     );

$expected_not = array('$a->p == 1 &&  $b->p == 5',
                      '$a->p == 1 ||  $a->p == 2',
                     );

?>