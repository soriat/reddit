<?php

/* Karma Farmer v4.0 - Judger
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 */

require("helper.php");
require("mysqliWrapper/MysqliDb.php");

$db = new Mysqlidb('localhost', 'soriat', '', 'reddit');
$potentialPosts = getPotentialPosts($db, 'unvalidated');

foreach($potentialPosts as $post) {
   foreach($post as $key => $val) {
      echo "$key: $val\n";
   }

   echo "Accept y/n\n";

   while(1) {
      if (trim(fgets(STDIN)) == 'y') {
         echo "Comment Accepted\n\n";
         updatePotentialPost($db, $post['RepostName']);
         break;
      }
      if (trim(fgets(STDIN)) == 'n') {
         echo "Comment Denied\n\n";
         deletePotentialPost($db, $post['RepostName']);
         break;
      }
   }
}
?>
