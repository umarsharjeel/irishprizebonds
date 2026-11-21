-- Migration: write-ups ("posts"), used by the monthly jackpot article (Dashboard > Articles).
-- Safe to run more than once (CREATE TABLE IF NOT EXISTS). Nothing else is changed.
-- Until this has been run, the public pages work as before and /news/ simply shows
-- "no write-ups yet"; the dashboard page shows this same SQL instead of publishing.

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `post_type` varchar(20) NOT NULL DEFAULT 'jackpot',
  `draw_id` int unsigned DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `title` varchar(200) NOT NULL,
  `summary` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `editor_note` text,
  `status` varchar(12) NOT NULL DEFAULT 'published',
  `published_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `type_draw` (`post_type`,`draw_id`),
  KEY `status_published` (`status`,`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
