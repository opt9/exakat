<?php

$x = [ 15, 16, 8, 6, 15, 12, 12, 18, 12, 20, 12, 14, ];
$y = [ 17.24, 15, 14.91, 4.5, 18, 6.29, 19.23, 18.69, 7.21, 42.06, 7.5, 8,];

sprintf("%3.5f", stats_covariance($x, $y));

?>
