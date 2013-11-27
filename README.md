Reddit Karma Farmer
======

Concept: Find reposts and post the top comment from past versions as your own.

v1.0: Strict minimum functionality
 - Can only handle 100 posts per run.
 - Can't skip ahead of posts that have already been parsed.

v2.0: Safer and Smarter
 - Arguments are passed in instead of hard-coded pass/user.
 - Checks that the comment hasn't already been posted.
 - Will keep parsing until quota has been reached.

v3.0: It Talks!
 - A lot more verbose in its actions.
 - New Docopt argument parsing.
 - Manual mode to screen comments before submitting them.

v4.0: SQL
 - Essentially completely automated now
 - Functionality split up into three parts
  - execute.php : Posts stored comments
  - parser.php  : Writes to SQL potential comments
  - judge.php   : Accept / Deny potential comments

v5.0: Accuracy
 - Comments parsed a lot more thoroughly
 - Includes link to repost in comment (RIP Mobile Users)
 - Automated parsing of most popular subreddits
