<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>Setting up Forum Database Tables</h1>";

try {
    // Create forum_posts table
    $createForumPosts = $db->prepare("
        CREATE TABLE IF NOT EXISTS `forum_posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `author` int(11) NOT NULL,
            `subforum` int(11) NOT NULL DEFAULT 2,
            `views` int(11) NOT NULL DEFAULT 0,
            `locked` tinyint(1) NOT NULL DEFAULT 0,
            `pinned` tinyint(1) NOT NULL DEFAULT 0,
            `lastposttime` int(11) NOT NULL,
            `lastpostauthor` int(11) NOT NULL,
            `lastpostid` int(11) DEFAULT NULL,
            `timemade` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `subforum` (`subforum`),
            KEY `author` (`author`),
            KEY `lastposttime` (`lastposttime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $createForumPosts->execute();
    echo "<p>✓ Created forum_posts table</p>";

    // Create forum_replies table
    $createForumReplies = $db->prepare("
        CREATE TABLE IF NOT EXISTS `forum_replies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `content` text NOT NULL,
            `author` int(11) NOT NULL,
            `thread` int(11) NOT NULL,
            `timemade` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `thread` (`thread`),
            KEY `author` (`author`),
            KEY `timemade` (`timemade`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $createForumReplies->execute();
    echo "<p>✓ Created forum_replies table</p>";

    // Create read tracking table
    $createReadTable = $db->prepare("
        CREATE TABLE IF NOT EXISTS `read` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `postId` int(11) NOT NULL,
            `timeRead` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_post` (`userId`, `postId`),
            KEY `userId` (`userId`),
            KEY `postId` (`postId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $createReadTable->execute();
    echo "<p>✓ Created read tracking table</p>";

    // Add forum_xp column to users table if it doesn't exist
    $checkForumXP = $db->prepare("SHOW COLUMNS FROM `users` LIKE 'forum_xp'");
    $checkForumXP->execute();
    
    if ($checkForumXP->rowCount() == 0) {
        $addForumXP = $db->prepare("ALTER TABLE `users` ADD `forum_xp` int(11) NOT NULL DEFAULT 0");
        $addForumXP->execute();
        echo "<p>✓ Added forum_xp column to users table</p>";
    } else {
        echo "<p>✓ forum_xp column already exists in users table</p>";
    }

    // Add flood column to users table if it doesn't exist
    $checkFlood = $db->prepare("SHOW COLUMNS FROM `users` LIKE 'flood'");
    $checkFlood->execute();
    
    if ($checkFlood->rowCount() == 0) {
        $addFlood = $db->prepare("ALTER TABLE `users` ADD `flood` int(11) DEFAULT NULL");
        $addFlood->execute();
        echo "<p>✓ Added flood column to users table</p>";
    } else {
        echo "<p>✓ flood column already exists in users table</p>";
    }

    // Add verified column to users table if it doesn't exist
    $checkVerified = $db->prepare("SHOW COLUMNS FROM `users` LIKE 'verified'");
    $checkVerified->execute();
    
    if ($checkVerified->rowCount() == 0) {
        $addVerified = $db->prepare("ALTER TABLE `users` ADD `verified` tinyint(1) NOT NULL DEFAULT 0");
        $addVerified->execute();
        echo "<p>✓ Added verified column to users table</p>";
    } else {
        echo "<p>✓ verified column already exists in users table</p>";
    }

    // Insert some sample forum posts for testing
    $checkSamplePosts = $db->prepare("SELECT COUNT(*) as count FROM forum_posts");
    $checkSamplePosts->execute();
    $postCount = $checkSamplePosts->fetch(PDO::FETCH_OBJ)->count;

    if ($postCount == 0) {
        // Get first user to create sample posts
        $getFirstUser = $db->prepare("SELECT id FROM users ORDER BY id ASC LIMIT 1");
        $getFirstUser->execute();
        
        if ($getFirstUser->rowCount() > 0) {
            $firstUser = $getFirstUser->fetch(PDO::FETCH_OBJ);
            $currentTime = time();
            
            // Sample announcement
            $insertAnnouncement = $db->prepare("
                INSERT INTO forum_posts (title, content, author, subforum, views, lastposttime, lastpostauthor, timemade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertAnnouncement->execute([
                'Welcome to Bricktaria Forums!',
                'Welcome to the official Bricktaria forums! This is where you can discuss the game, share your creations, report bugs, and connect with other players. Please read the forum guidelines and enjoy your stay!',
                $firstUser->id,
                1, // Announcements
                0,
                $currentTime,
                $firstUser->id,
                $currentTime
            ]);

            // Sample general discussion
            $insertGeneral = $db->prepare("
                INSERT INTO forum_posts (title, content, author, subforum, views, lastposttime, lastpostauthor, timemade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertGeneral->execute([
                'What\'s your favorite feature in Bricktaria?',
                'I\'m curious to know what everyone\'s favorite feature is in Bricktaria. For me, I really love the avatar customization system and how you can create unique looks for your character. What about you?',
                $firstUser->id,
                2, // General Discussion
                0,
                $currentTime,
                $firstUser->id,
                $currentTime
            ]);

            echo "<p>✓ Created sample forum posts</p>";
        }
    } else {
        echo "<p>✓ Sample posts already exist</p>";
    }

    echo "<h2>Forum setup complete!</h2>";
    echo "<p>The forum system is now ready to use. You can:</p>";
    echo "<ul>";
    echo "<li><a href='/forum'>Visit the forum</a></li>";
    echo "<li><a href='/forum/topic/2'>View General Discussion</a></li>";
    echo "<li><a href='/forum/topic/1'>View Announcements</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 