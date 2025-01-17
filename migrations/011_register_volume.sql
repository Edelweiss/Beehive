CREATE TABLE `beehive`.`volume` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,  `ddb` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,  `title` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,  `sort` INT UNSIGNED NOT NULL ,    PRIMARY KEY  (`id`)) ENGINE = InnoDB;

INSERT INTO `volume` (`id`, `ddb`, `title`, `sort`) VALUES ('1', 'test;1;', 'Test 1', '100');

ALTER TABLE `register` ADD `volume_id` INT UNSIGNED NOT NULL AFTER `id`;

UPDATE `register` set volume_id = 1;

ALTER TABLE `register` ADD  CONSTRAINT `register_volume` FOREIGN KEY (`volume_id`) REFERENCES `volume`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;



SELECT * from register r JOIN correction_register cr ON cr.register_id = r.id WHERE ddb LIKE '%cpl%' OR ddb = 'p.ups;;8' OR ddb LIKE '%ddbdp%' OR ddb LIKE '%sosol%';

DELETE FROM register WHERE ddb LIKE '%cpl%';
DELETE FROM register WHERE ddb = 'p.ups;;8';
DELETE FROM register WHERE ddb LIKE '%sosol%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2002;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2013;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2015;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2016;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2017;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2021;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2022;%';
DELETE FROM register WHERE ddb LIKE '%ddbdp;2023;%';



