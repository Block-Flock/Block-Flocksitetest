-- Block-Flocksitetest schema-only template for MariaDB / MySQL
-- No real user records, inboxes, transactions, or private configuration secrets.
-- Import into an empty database; configure the application separately.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `ItemSeller` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ItemID` int(11) DEFAULT NULL,
  `InventoryID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `TimeCreated` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ItemSeller_Unique` (`ItemID`,`InventoryID`,`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `ItemSellerHistory` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UTLID` int(11) DEFAULT NULL,
  `ItemID` int(11) DEFAULT NULL,
  `SellerID` int(11) DEFAULT NULL,
  `BuyerID` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `TimeSale` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `UserTrade` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RequesterID` int(11) DEFAULT NULL,
  `ReceiverID` int(11) DEFAULT NULL,
  `Status` int(11) DEFAULT 0,
  `Time` int(11) DEFAULT NULL,
  `Expires` int(11) DEFAULT NULL,
  `GivingOne` int(11) DEFAULT 0,
  `GivingTwo` int(11) DEFAULT 0,
  `GivingThree` int(11) DEFAULT 0,
  `GivingFour` int(11) DEFAULT 0,
  `GivingCredits` int(11) DEFAULT 0,
  `WantOne` int(11) DEFAULT 0,
  `WantTwo` int(11) DEFAULT 0,
  `WantThree` int(11) DEFAULT 0,
  `WantFour` int(11) DEFAULT 0,
  `WantCredits` int(11) DEFAULT 0,
  `UpdatedOn` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `configuration` (
  `maintenance` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true/false-enabled/disabled',
  `maintenanceCode` varchar(255) NOT NULL DEFAULT '',
  `alertText` varchar(255) NOT NULL,
  PRIMARY KEY (`maintenance`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subforum` int(11) NOT NULL DEFAULT 1,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `lastposttime` int(11) NOT NULL,
  `lastpostauthor` int(11) DEFAULT NULL,
  `lastpostid` int(11) DEFAULT NULL,
  `timemade` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_author` (`author`),
  KEY `idx_subforum` (`subforum`),
  KEY `idx_lastpostauthor` (`lastpostauthor`),
  KEY `idx_lastpostid` (`lastpostid`),
  KEY `idx_timemade` (`timemade`),
  KEY `idx_pinned` (`pinned`),
  KEY `idx_locked` (`locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `forum_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `author` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `thread` int(11) NOT NULL,
  `timemade` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_author` (`author`),
  KEY `idx_post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `sender` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `item` int(11) DEFAULT NULL,
  `serial` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ip_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(250) DEFAULT NULL,
  `num_rows` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `item_comments` (
  `id` int(11) NOT NULL,
  `item` int(11) DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `body` varchar(256) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `desc` varchar(1000) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `offsale` int(11) NOT NULL DEFAULT 0,
  `cost` int(11) DEFAULT NULL,
  `sales` int(11) NOT NULL DEFAULT 0,
  `time_created` int(11) DEFAULT NULL,
  `time_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_timestamp` (`ip`,`timestamp`),
  KEY `idx_user_timestamp` (`user_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `promocodes` (
  `code` varchar(255) NOT NULL,
  `itemid` varchar(100) NOT NULL DEFAULT '0',
  `uses` int(11) NOT NULL DEFAULT -1,
  `used` int(11) NOT NULL DEFAULT 0,
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  `expirationdate` timestamp NOT NULL DEFAULT '2037-12-31 00:00:00',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `postId` int(11) NOT NULL,
  `readAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_post` (`userId`,`postId`),
  KEY `idx_userId` (`userId`),
  KEY `idx_postId` (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sitesettings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Maintenance` tinyint(1) NOT NULL DEFAULT 0,
  `Registration` tinyint(4) DEFAULT 1,
  `Upgrades` tinyint(4) DEFAULT 1,
  `CatalogPurchases` tinyint(4) DEFAULT 1,
  `BannerAlertMessage` varchar(1000) DEFAULT NULL,
  `AllowGroups` tinyint(4) DEFAULT 1,
  `AllowEditCharacter` tinyint(4) DEFAULT 1,
  `AllowTrades` tinyint(4) DEFAULT 1,
  `GameMaintenance` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `store_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `comment` text NOT NULL,
  `timecommented` timestamp NOT NULL DEFAULT current_timestamp(),
  `moderation` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `store_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT 'UNNAMEDITEM',
  `about` varchar(250) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `tag1` varchar(50) DEFAULT NULL,
  `tag2` varchar(50) DEFAULT NULL,
  `tag3` varchar(50) DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `tradeable` tinyint(1) NOT NULL DEFAULT 0,
  `offsale` tinyint(1) NOT NULL DEFAULT 1,
  `moderation` tinyint(1) NOT NULL DEFAULT 0,
  `obj` varchar(250) DEFAULT NULL,
  `special` int(11) NOT NULL DEFAULT 0,
  `texture` varchar(250) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `owners` int(11) NOT NULL DEFAULT 0,
  `timeupdated` int(11) DEFAULT NULL,
  `timecreated` int(11) DEFAULT NULL,
  `public` int(11) NOT NULL DEFAULT 1,
  `stock` int(11) NOT NULL DEFAULT -1,
  `allowcomments` int(11) NOT NULL DEFAULT 1,
  `expire` int(11) DEFAULT NULL,
  `isFavorited` int(11) NOT NULL DEFAULT 0,
  `isOnWishlist` int(11) NOT NULL DEFAULT 0,
  `wishlistCount` int(11) NOT NULL DEFAULT 0,
  `favorites` int(11) NOT NULL DEFAULT 0,
  `TradeLock` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` varchar(255) NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_adverts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT 'Advertisement',
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(3000) NOT NULL,
  `budget` int(11) NOT NULL DEFAULT 0,
  `type` int(11) DEFAULT NULL,
  `duration` int(12) DEFAULT 0,
  `moderation` int(11) NOT NULL DEFAULT 0,
  `running` int(11) NOT NULL DEFAULT 1,
  `impressions` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `buid` int(11) DEFAULT NULL,
  `bnam` text DEFAULT NULL,
  `bimg` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `user_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `head` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `torso` varchar(255) NOT NULL DEFAULT '0.033105, 0.313989, 0.708376',
  `left_arm` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `right_arm` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `left_leg` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  `right_leg` varchar(255) NOT NULL DEFAULT '0.723055, 0.791298, 0.806952',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `hat1` int(11) DEFAULT NULL,
  `hat2` int(11) DEFAULT 0,
  `hat3` int(11) DEFAULT 0,
  `face` varchar(11) NOT NULL DEFAULT 'default',
  `accessory` int(11) DEFAULT 0,
  `tshirt` int(11) DEFAULT 0,
  `shirt` int(11) DEFAULT 0,
  `pants` int(11) DEFAULT 0,
  `gear` int(11) DEFAULT 0,
  `head` int(11) DEFAULT 0,
  `torso` int(11) DEFAULT 0,
  `leftarm` int(11) DEFAULT 0,
  `rightarm` int(11) DEFAULT 0,
  `leftleg` int(11) DEFAULT 0,
  `rightleg` int(11) DEFAULT 0,
  `avatar_refresh` int(11) NOT NULL DEFAULT 1640026419,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_friends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `timejoined` int(11) DEFAULT NULL,
  `logo` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_group_ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `groupid` int(11) DEFAULT NULL,
  `indicator` int(11) DEFAULT NULL,
  `members` int(11) NOT NULL DEFAULT 0,
  `allow_edit_comments` tinyint(1) NOT NULL DEFAULT 0,
  `allow_edit_members` tinyint(1) NOT NULL DEFAULT 0,
  `allow_edit_group` tinyint(1) NOT NULL DEFAULT 0,
  `allow_all` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(600) DEFAULT NULL,
  `creator` int(11) DEFAULT NULL,
  `members` int(11) DEFAULT NULL,
  `moderation_status` tinyint(1) NOT NULL DEFAULT 0,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `logo` varchar(250) DEFAULT NULL,
  `timecreated` int(11) DEFAULT NULL,
  `allowcomments` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `action` varchar(1000) DEFAULT NULL,
  `timemoderated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_inboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `recipient` int(11) DEFAULT NULL,
  `subject` varchar(120) NOT NULL DEFAULT 'No Subject Given',
  `content` varchar(4000) NOT NULL DEFAULT 'No Content Given.',
  `archived` int(11) NOT NULL DEFAULT 0,
  `hasread` int(11) NOT NULL DEFAULT 0,
  `timesent` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `item` int(11) DEFAULT NULL,
  `timebought` int(11) DEFAULT NULL,
  `serial` int(11) DEFAULT NULL,
  `serialID` int(11) DEFAULT NULL,
  `CanTrade` int(15) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_profile_customization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme` varchar(50) DEFAULT 'default',
  `background_color` varchar(7) DEFAULT '#1a1a2e',
  `accent_color` varchar(7) DEFAULT '#6c5ce7',
  `text_color` varchar(7) DEFAULT '#ffffff',
  `background_image` text DEFAULT NULL,
  `background_music` text DEFAULT NULL,
  `custom_css` text DEFAULT NULL,
  `profile_font` varchar(50) DEFAULT 'Inter',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_profile_customization_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `user_promocodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user` int(11) NOT NULL,
  `timeredeemed` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_trades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `recipient` int(11) DEFAULT NULL,
  `trade_data` varchar(65000) NOT NULL,
  `add_coins` int(11) DEFAULT 0,
  `add_cash` int(11) NOT NULL DEFAULT 0,
  `UpdatedOn` int(11) DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `trade_date` int(11) DEFAULT NULL,
  `Expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(1000) DEFAULT NULL,
  `item_type` int(11) DEFAULT NULL,
  `buyer_name` varchar(1000) DEFAULT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `date` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `useractionlogs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `Action` text DEFAULT NULL,
  `TimeLog` int(11) DEFAULT NULL,
  `IP` varchar(60) DEFAULT NULL,
  `ArchiveID` int(11) DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `UserActionLog_UserID_FK_idx` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `emailVerifyCode` int(15) DEFAULT 0,
  `ip` varchar(250) DEFAULT NULL,
  `last_ip` varchar(250) DEFAULT NULL,
  `avatar_hash` varchar(250) NOT NULL DEFAULT 'default',
  `next_reward` int(15) DEFAULT NULL,
  `premium_reward` int(15) DEFAULT NULL,
  `premium_expired` int(15) DEFAULT NULL,
  `Currency` int(255) NOT NULL DEFAULT 0,
  `cash` int(255) NOT NULL DEFAULT 0,
  `status` varchar(250) DEFAULT '',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `experiments` tinyint(11) DEFAULT 0,
  `flood` int(11) DEFAULT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `premium` tinyint(1) NOT NULL DEFAULT 0,
  `newexpire` tinyint(250) DEFAULT NULL,
  `forumposts` int(11) NOT NULL DEFAULT 0,
  `profileviews` int(255) DEFAULT 0,
  `expire` int(11) DEFAULT NULL,
  `joined` int(11) DEFAULT NULL,
  `forum_xp` int(11) DEFAULT 0,
  `forumReplies` int(11) NOT NULL DEFAULT 0,
  `prestige` int(11) NOT NULL DEFAULT 0,
  `blurb` varchar(250) DEFAULT '',
  `Theme` varchar(250) DEFAULT NULL,
  `allowcomments` int(11) NOT NULL DEFAULT 1,
  `Gender` varchar(255) DEFAULT '0',
  `enjoy` int(15) DEFAULT NULL,
  `dislike` int(11) DEFAULT NULL,
  `bday` int(11) DEFAULT NULL,
  `bmonth` int(11) DEFAULT NULL,
  `byear` int(11) DEFAULT NULL,
  `emailcodeTime` int(11) DEFAULT NULL,
  `TradeSettings` tinyint(4) NOT NULL DEFAULT 0,
  `NotificationSettingsIncomingTrades` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `userwall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `comment` text NOT NULL,
  `timecommented` timestamp NOT NULL DEFAULT current_timestamp(),
  `moderation` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

SET FOREIGN_KEY_CHECKS = 1;
