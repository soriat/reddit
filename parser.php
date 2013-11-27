<?php

/* Karma Farmer v4.0 - Parser
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 */

require("reddit.php");
require("docopt.php");
require("helper.php");
require("mysqliWrapper/MysqliDb.php");

$doc = <<<EOT
Usage:
   parser.php (-h | --help)
   parser.php [USERNAME] [PASSWORD]

Arguments:
   USERNAME  - Username of reddit account
   PASSWORD  - Password of reddit account

Options
  -h --help  - Show this screen.
EOT;

$params = array(
    'argv' => array_slice($_SERVER['argv'], 1),
    'help' => true,
    'version' => null,
    'optionsFirst' => false,
);

$handler = new \Docopt\Handler($params);
$args = $handler->handle($doc)->args;
// I so need to update php:
// - $args = (new \Docopt\Handler)->handle($doc);

$postCount = 20;
$username  = $args['USERNAME'];
$password  = $args['PASSWORD'];

$subreddits = getAllSubreddits();
$reddit = new reddit($username, $password);
$db = new Mysqlidb('localhost', 'soriat', '', 'reddit');

while($postCount > 0) {
   if (empty($subreddits)) {
      die;
   }
   $sub = array_rand($subreddits);
   $posts = $reddit->getCleanPosts($sub, $subreddits[$sub]['after']);
   echo "\nParsing $sub... Remaining: $postCount\n";

   if (count($posts) != 100) {
      unset($subreddits[$sub]);
      continue;
   }

   // Time to see if these are reposts
   foreach($posts as $post) {
      $subreddits[$sub]['after'] = $post['name'];
      if ($postCount <= 0) {
         break;
      }

      if (hasBeenParsed($db, $post['name'])) {
         echo ".";
         continue;
      } else {
         insertParsed($db, $post['name']);
      }

      // Only do posts that don't have that many comments
      if ($post['score'] < 50 ||
          $post['comments'] > 50 ||
          $post['comments'] < 5 ||
          !strstr($post['domain'], 'imgur')) {
         echo ",";
         continue;
      }

      $potentialPost = array('score' => 0, 'comment' => '');
      foreach($reddit->searchKarmaDecay($post['url']) as $toCheck) {
         $result = $reddit->getTopComment($toCheck['url']);
         if ($result['score'] > $potentialPost['score']) {
            $potentialPost = $result;
            $originalURL = $toCheck['url'];
         }
      }

      //$reddit->hasComment($post['url'], $potentialPost['comment']);
      if ($potentialPost['score'] <= MIN_SCORE) {
         echo "'";
         continue;
      }

      $repostData = array(
         'Comment' => $potentialPost['comment'],
         'RepostURL' => "http://reddit.com{$post['url']}",
         'RepostName' => $post['name'],
         'OriginalURL' => $originalURL,
         'OriginalScore' => $potentialPost['score']
      );

      echo "\nFound:\n";
      foreach($repostData as $key => $val) {
         echo " - $key: $val\n";
      }

      insertPost($db, $repostData, 'potentialPosts');
      $postCount--;
   }
}
?>
