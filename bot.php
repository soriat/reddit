<?php

/* Karma Farmer v1.0
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 *
 * Bot Concept: Find reposts and copy the top rated comment from the OC post
 */

require("reddit.php");

$username = "RepostKarmaFarmer";
$password = "This is my password!";
$reddit = new reddit($username, $password);

// Specify which subreddit and how many posts to scan.
$listing = $reddit->getListing('pics', 1000);

// Make the objects a bit more readable
for($i = 0; $i < count($listing->data->children); $i++) {
   $posts[] = array(
    'url'      => $listing->data->children[$i]->data->permalink,
    'score'    => $listing->data->children[$i]->data->score,
    'name'     => $listing->data->children[$i]->data->name,
    'domain'   => $listing->data->children[$i]->data->domain,
    'comments' => $listing->data->children[$i]->data->num_comments
  );
}

// Time to see if these are reposts
foreach($posts as $post) {
   // Only do posts that don't have that many comments
   if ($post['comments'] > 50) {
      continue;
   }

   // Only do posts which contain an imgur link (for now)
   if (!strstr($post['domain'], 'imgur')) {
      continue;
   }

   // Ask KarmaDecay whether or not this is a repost
   $data = $reddit->getPage("http://karmadecay.com" . $post['url']);

   // Clear / Initialize our results;
   $toSearch = array();

   // Format: title | points | age | /r/ | comments
   $regex = '/(\:--)\|\1\|\1\|\1\|\1(.*?)\*\[S/ms';
   if (preg_match($regex, $data, $matches)) {
      foreach(explode("\n", $matches[2]) as $duplicate) {
         if ($duplicate[0] == '[') {
            $dupData = explode("|", $duplicate);
            if (count($dupData) != 5) {
               continue;
            }

            preg_match('/\]\((.*?)\)/ms', $dupData[0], $matches);
            $toSearch[] = array(
               'url'      => $matches[1],
               'score'    => $dupData[1],
               'comments' => $dupData[4]
            );
         }
      }
   }

   $potentialPost = array('score' => 0, 'comment' => '');
   $originalURL = '';
   foreach($toSearch as $toCheck) {
      $result = $reddit->getTopComment($toCheck['url']);
      if ($result['score'] > $potentialPost['score']) {
         $potentialPost = $result;
         $originalURL = $toCheck['url'];
      }
   }

   if ($potentialPost['score'] >= 250) {
      echo "\nTo:   http://reddit.com{$post['url']}\n";
      echo "From: {$originalURL}\n";
      echo "Had Score: {$potentialPost['score']}\n";
      echo "Text: {$potentialPost['comment']}\n";

      $reddit->addComment($post['name'], $potentialPost['comment']);
      sleep(60 * 8);
   }
}
?>
