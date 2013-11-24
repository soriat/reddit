<?php
define('MIN_SCORE', 250);
define('MIN_WAIT', 8);

function wait($minutes) {
   echo "Sleeping for $minutes minutes";
   for ($i = 0, $j = 12; $i < $minutes; $j = 12) {
      while($j--) {
        sleep(5);
        echo ".";
      }
      echo ++$i;
   }
   echo " Completed\n\n";
}

function estTime($posts) {
   $hours = floor($posts * MIN_WAIT / 60);
   $minutes = $posts * 8 - $hours * 60;
   $est = $hours ? "$hours Hours and " : '';
   $est .= "$minutes Minutes";

   echo "It will take $est to post all $count comments\n\n";
}

?>
