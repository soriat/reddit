<?php

/* Karma Farmer v1.0
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 *
 * Bot Concept: Find reposts and copy the top rated comment from the OC post
 */

require("reddit.php");

if ($argc != 5) {
   echo "Usage: bot.php username password subreddit postcount\n";
   exit;
}

$username = $argv[1];
$password = $argv[2];
$subreddit = $argv[3];
$postsRemaining = $argv[4];

$after = '';
$reddit = new reddit($username, $password);

while($postsRemaining > 0) {
   $posts = $reddit->getCleanPosts($subreddit, $after);

   // Time to see if these are reposts
   foreach($posts as $post) {
      if ($postsRemaining <= 0) {
         return;
      }

      // Only do posts that don't have that many comments
      if ($post['comments'] > 50 || !strstr($post['domain'], 'imgur')) {
         continue;
      }

      $after = $post['name'];
      $potentialPost = array('score' => 0, 'comment' => '');
      foreach($reddit->searchKarmaDecay($post['url']) as $toCheck) {
         $result = $reddit->getTopComment($toCheck['url']);
         if ($result['score'] > $potentialPost['score']) {
            $potentialPost = $result;
            $originalURL = $toCheck['url'];
         }
      }

      if ($potentialPost['score'] <= 250 ||
       $reddit->hasComment($post['url'], $potentialPost['comment'])) {
         continue;
      }

      echo "\nTo:   http://reddit.com{$post['url']}\n";
      echo "From: {$originalURL}\n";
      echo "Had Score: {$potentialPost['score']}\n";
      echo "Text: {$potentialPost['comment']}\n";

      $reddit->addComment($post['name'], $potentialPost['comment']);
      $postsRemaining--;

      sleep(60 * 8);
   }
}

?>
