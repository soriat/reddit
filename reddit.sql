CREATE TABLE parsedPosts (
   Name varchar(25) NOT NULL,
   PRIMARY KEY (Name)
);

CREATE TABLE submittedPosts (
   Comment TINYTEXT NOT NULL,
   RepostURL varchar(255) NOT NULL,
   RepostName varchar(25) NOT NULL,
   OriginalURL varchar(255) NOT NULL,
   OriginalScore int(11) NOT NULL,
   PRIMARY KEY (RepostName)
);

CREATE TABLE potentialPosts (
   Validated BOOL DEFAULT FALSE,
   Comment TINYTEXT NOT NULL,
   RepostURL varchar(255) NOT NULL,
   RepostName varchar(25) NOT NULL,
   OriginalURL varchar(255) NOT NULL,
   OriginalScore int(11) NOT NULL,
   PRIMARY KEY (RepostName)
);
