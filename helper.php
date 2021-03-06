<?php

define('MIN_SCORE', 50);
define('WAIT_MIN', 3);

function wait($diff = 0) {
   $seconds = WAIT_MIN * 60 - $diff;
   if ($seconds < 0) {
      return;
   }

   $minutes = floor($seconds / 60);
   $seconds -= $minutes * 60;

   echo "Sleeping for {$minutes}min, {$seconds}sec";

   // Minutes
   for ($minute = 0, $second = 0; $minute < $minutes; $second = 0) {
      while($second < 60) {
        sleep(5);
        echo ".";
        $second += 5;
      }
      echo ++$minute;
   }

   // Seconds
   $second = 0;
   while($second < $seconds) {
     sleep(5);
     echo ".";
     $second += 5;
   }

   echo " Completed\n\n";
}

function estTime($posts) {
   $hours = floor($posts * MIN_WAIT / 60);
   $minutes = $posts * MIN_WAIT - $hours * 60;
   $est = $hours ? "$hours Hours and " : '';
   $est .= "$minutes Minutes";

   echo "It will take ~$est to post all $posts comments\n\n";
}

function parseComment($comment, $url) {
   $comment = trim(html_entity_decode($comment, ENT_QUOTES));
   $from = array(
      '/(<p>|<\/(sup|p)>)/', // -------------- Useless
      '/<sup>/', // -------------------------- Supersrcipt
      '/<em>(.*)<\/em>/', // ----------------- Italic
      '/<strong>(.*)<\/strong>/', // --------- Bold
      '/<del>(.*)<\/del>/', // --------------- Strikethrough
      '/<blockquote>(.*)<\/blockquote>/', // - Quote
      '/<a\shref="([^"]+)".*>(.+)<\/a>/' // -- Link
   );
   $to = array('', '^', '*\1*', '**\1**', '~~\1~~', '> \1', '[\2](\1)');
   $comment = trim(strip_tags(preg_replace($from, $to, $comment)));

   $lastChar = substr($comment, -1);
   if (in_array($lastChar, array('.', '?', '!', '"', "'"))) {
      echo substr($comment, 0, -1) . "[$lastChar]($url)\n";
      return substr($comment, 0, -1) . "[$lastChar]($url)";
   }

}

function insertParsed($db, $name) {
   $q_insert = <<<SQL
      INSERT INTO `parsedPosts`
      SET `Name` = ?
SQL;

   $db->rawQuery($q_insert, array($name));
}

function insertPost($db, $data, $table) {
   $data['Comment'] = parseComment($data['Comment'], $data['OriginalURL']);

   $q_insert = <<<SQL
      INSERT INTO `$table`
      SET `Comment` = ?,
          `RepostURL` = ?,
          `RepostName` = ?,
          `OriginalURL` = ?,
          `OriginalScore` = ?
SQL;

   $db->rawQuery($q_insert, array_values($data));
}

function hasBeenParsed($db, $name) {
   $query = <<<SQL
      SELECT COUNT(*)
      FROM `parsedPosts`
      WHERE `Name` = ?
SQL;

   $results = $db->rawQuery($query, array($name));
   return $results[0]['COUNT(*)'];
}

function getPotentialPosts($db, $type = 'validated') {
   switch($type) {
      case 'validated':
         $where = 'WHERE `Validated` = TRUE';
         break;
      case 'unvalidated':
         $where = 'WHERE `Validated` = FALSE';
         break;
      case 'all':
      default:
         $where = '';
   }

   $query = <<<SQL
      SELECT *
      FROM `potentialPosts`
      $where
SQL;

   return $db->rawQuery($query);
}

function deletePotentialPost($db, $name) {
   $db->where('RepostName', $name);
   $db->delete('potentialPosts');
}

function updatePotentialPost($db, $name) {
   $update = array("Validated" => 1);
   $db->where('RepostName', $name);
   $db->update('potentialPosts', $update);
}

function getAllSubreddits() {
   $subreddits = array('pics', 'funny', 'gaming', 'wtf', 'aww', 'gifs',
    'mildlyinteresting', 'woahdude', 'Unexpected', 'reactiongifs',
    'HistoryPorn', 'trees', '4chan', 'atheism', 'facepalm',
    'fffffffuuuuuuuuuuuu', 'firstworldanarchists', 'cringe');

   foreach($subreddits as $sub) {
      $results[$sub] = array('subreddit' => $sub, 'after' => '');
   }

   return $results;
}
