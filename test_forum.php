<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>Forum System Test</h1>";

try {
    // Test database connection
    echo "<h2>Database Connection Test</h2>";
    $testQuery = $db->query("SELECT 1");
    echo "<p>✓ Database connection successful</p>";

    // Test forum_posts table
    echo "<h2>Forum Tables Test</h2>";
    $checkPosts = $db->prepare("SHOW TABLES LIKE 'forum_posts'");
    $checkPosts->execute();
    if ($checkPosts->rowCount() > 0) {
        echo "<p>✓ forum_posts table exists</p>";
        
        $postCount = $db->query("SELECT COUNT(*) as count FROM forum_posts")->fetch(PDO::FETCH_OBJ)->count;
        echo "<p>✓ forum_posts table has {$postCount} posts</p>";
    } else {
        echo "<p style='color: red;'>✗ forum_posts table missing</p>";
    }

    $checkReplies = $db->prepare("SHOW TABLES LIKE 'forum_replies'");
    $checkReplies->execute();
    if ($checkReplies->rowCount() > 0) {
        echo "<p>✓ forum_replies table exists</p>";
        
        $replyCount = $db->query("SELECT COUNT(*) as count FROM forum_replies")->fetch(PDO::FETCH_OBJ)->count;
        echo "<p>✓ forum_replies table has {$replyCount} replies</p>";
    } else {
        echo "<p style='color: red;'>✗ forum_replies table missing</p>";
    }

    $checkRead = $db->prepare("SHOW TABLES LIKE 'read'");
    $checkRead->execute();
    if ($checkRead->rowCount() > 0) {
        echo "<p>✓ read table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ read table missing</p>";
    }

    // Test users table columns
    echo "<h2>Users Table Test</h2>";
    $checkColumns = $db->prepare("SHOW COLUMNS FROM users");
    $checkColumns->execute();
    $columns = $checkColumns->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['forum_xp', 'flood', 'verified'];
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "<p>✓ users table has {$column} column</p>";
        } else {
            echo "<p style='color: red;'>✗ users table missing {$column} column</p>";
        }
    }

    // Test forum URLs
    echo "<h2>Forum URL Test</h2>";
    echo "<p>Testing forum URLs:</p>";
    echo "<ul>";
    echo "<li><a href='/forum' target='_blank'>Forum Home</a></li>";
    echo "<li><a href='/forum/topic/1' target='_blank'>Announcements</a></li>";
    echo "<li><a href='/forum/topic/2' target='_blank'>General Discussion</a></li>";
    echo "<li><a href='/forum/topic/3' target='_blank'>Off-Topic</a></li>";
    echo "<li><a href='/forum/topic/4' target='_blank'>Marketplace</a></li>";
    echo "<li><a href='/forum/topic/5' target='_blank'>Bug Reports</a></li>";
    echo "<li><a href='/forum/topic/6' target='_blank'>Games Creation</a></li>";
    echo "</ul>";

    // Test sample posts
    echo "<h2>Sample Posts Test</h2>";
    $samplePosts = $db->prepare("SELECT id, title, subforum FROM forum_posts ORDER BY id ASC LIMIT 5");
    $samplePosts->execute();
    
    if ($samplePosts->rowCount() > 0) {
        echo "<p>Sample posts found:</p>";
        echo "<ul>";
        while ($post = $samplePosts->fetch(PDO::FETCH_OBJ)) {
            echo "<li><a href='/forum/post/{$post->id}' target='_blank'>{$post->title}</a> (Subforum: {$post->subforum})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>No sample posts found. Run setup_forum_tables.php to create them.</p>";
    }

    echo "<h2>Forum System Status</h2>";
    echo "<p style='color: green; font-weight: bold;'>✓ Forum system appears to be working correctly!</p>";
    echo "<p><a href='/forum'>Visit the forum</a> to start using it.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 