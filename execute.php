<?php

/* Karma Farmer v4.0 - Executer
 * Author:  Thomas Soria
 * Contact: thomas.soria@gmail.com
 *
 * Bot Concept: Find reposts and copy the top rated comment from the OC post
 */

require("reddit.php");
require("docopt.php");
require("helper.php");
require("mysqliWrapper/MysqliDb.php");

$doc = <<<EOT
Usage:
   bot.php (-h | --help)
   bot.php [USERNAME] [PASSWORD] [VALIDATED]

Arguments:
   USERNAME  - Username of reddit account
   PASSWORD  - Password of reddit account
   VALIDATED - Specify validation requirement: ('validated' | 'all')

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
// - $args = (new \Docopt\Handler)->handle($sdoc);

$username  = $args['USERNAME'];
$password  = $args['PASSWORD'];
$validated = $args['VALIDATED'];

$reddit = new reddit($username, $password);
$db = new Mysqlidb('localhost', 'soriat', '', 'reddit');
$posts = getPotentialPosts($db, $validated);

estTime(count($posts));

foreach($posts as $post) {
   array_shift($post);
   foreach($post as $key => $val) {
      echo " - $key: $val\n";
   }  echo "\n";

   insertPost($db, $post, 'submittedPosts');
   deletePotentialPost($db, $post['RepostName']);

   $reddit->addComment($post['RepostName'], $post['Comment']);

   wait();
}
?>
