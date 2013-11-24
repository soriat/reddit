<?php

/* Karma Farmer v3.0
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 *
 * Bot Concept: Find reposts and copy the top rated comment from the OC post
 */

require("reddit.php");
require("docopt.php");
require("helper.php");

$doc = <<<EOT
Usage:
   bot.php (-h | --help)
   bot.php [USERNAME] [PASSWORD] [SUBREDDIT] [POSTCOUNT] [--manual]

Arguments:
   USERNAME  - Username of reddit account
   PASSWORD  - Password of reddit account
   SUBREDDIT - Subreddit to be parsed for reposts
   POSTCOUNT - How many comments to post

Options
  -h --help  - Show this screen.
  --manual   - Get confirmation before posting. (Put in queue afterwards)
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
// - $args = (new \Docopt\Handler)->handle($sdoc);

$manual    = $args['--manual'];
$username  = $args['USERNAME'];
$password  = $args['PASSWORD'];
$subreddit = $args['SUBREDDIT'];
$postCount = $args['POSTCOUNT'];

$after = '';
$queue = array();
$queueCount = 0;
$reddit = new reddit($username, $password);

if (!$manual) {
   estTime($postCount);
}

while($postCount > 0) {
   $posts = $reddit->getCleanPosts($subreddit, $after);

   echo "Parsing for reposts\n";
   // Time to see if these are reposts
   foreach($posts as $post) {
      if ($postCount <= 0) {
         break;
      }
      echo ".";

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

      if ($potentialPost['score'] <= MIN_SCORE ||
       $reddit->hasComment($post['url'], $potentialPost['comment'])) {
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

      if ($manual) {
         echo "Accept y/n\n";
         while(1) {
            if (trim(fgets(STDIN)) == 'y') {
               echo "Comment Accepted\n\n";
               $postCount--;
               $queue[] = $repostData;
               break;
            }
            if (trim(fgets(STDIN)) == 'n') {
               echo "Comment Denied\n\n";
               break;
            }
         }
      } else {
         $reddit->addComment($repostData['RepostName'], $repostData['Comment']);
         $postCount--;
         wait(8);
      }
   }
}

if ($manual) {
   $postCount = count($queue);
   echo "Done parsing $subreddit\n";
   estTime($postCount);
   foreach($queue as $post) {
      echo "There are $postCount comments left in the queue\n\n";

      foreach($post as $key => $val) {
         echo "$key: $val\n";
      }

      $reddit->addComment($post['RepostName'], $post['Comment']);
      $postCount--;
      wait(8);
   }
}
?>
