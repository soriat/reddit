Reddit Karma Farmer
======

Concept: Find reposts and post the top comment from past versions as your own.

v1.0: Strict minimum functionality
 - Can only handle ~250 posts per run.
 - Can't skip ahead of posts that have already been parsed.

v2.0: Safer and Smarter
 - Arguments are passed in instead of hard-coded pass/user.
 - Checks that the comment hasn't already been posted.
 - Will keep parsing until quota has been reached.

v3.0: It Talks!
 - A lot more verbose in its actions.
 - New Docopt argument parsing.
 - Manual mode to screen comments before submitting them.
