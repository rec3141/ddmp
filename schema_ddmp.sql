# MySQL create table script for importing ddmp database.
# after CHEBI and NCBI have been loaded

SET FOREIGN_KEY_CHECKS=0;
-- ## prepare ddmp
DROP TABLE if exists ddmp_taxa;
CREATE TABLE ddmp_taxa
	(
	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`taxon` VARCHAR(255) NOT NULL,
	`ncbi_id` MEDIUMINT(11) UNSIGNED,
	PRIMARY KEY(`id`)
	) TYPE=InnoDB;

-- table data
DROP TABLE if exists ddmp_data;
CREATE TABLE ddmp_data
	(
-- 	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`src_id` INT unsigned NOT NULL,
	`taxa_id` INT unsigned NOT NULL,
	`char_id` INT unsigned NOT NULL,
	`raw` VARCHAR(255) NOT NULL,
	`data` TINYINT(2),
-- 	`has_fn` CHAR(1) NOT NULL,
-- 	PRIMARY KEY(`id`)
	PRIMARY KEY(`src_id`,`taxa_id`,`char_id`)
	) TYPE=InnoDB;

-- table footnotes
DROP TABLE if exists ddmp_fntext;
CREATE TABLE ddmp_fntext
	(
-- 	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`src_id` INT unsigned NOT NULL,
	`fn` CHAR(2) NOT NULL,
	`text` TEXT NOT NULL,
-- 	PRIMARY KEY(`id`)
	PRIMARY KEY(`src_id`,`fn`)
	) TYPE=InnoDB;

-- table footnotes/data
DROP TABLE if exists ddmp_fnrel;
CREATE TABLE ddmp_fnrel
	(
-- 	`id` INT unsigned AUTO_INCREMENT NOT NULL,
-- 	`fn_id` INT unsigned NOT NULL,
-- 	`data_id` INT unsigned NOT NULL,
	`src_id` INT unsigned NOT NULL,
	`taxa_id` INT unsigned NOT NULL,
	`char_id` INT unsigned NOT NULL,
	`fn` CHAR(2) NOT NULL,
	PRIMARY KEY(`src_id`,`taxa_id`,`char_id`,`fn`)
	) TYPE=InnoDB;

-- table properties
DROP TABLE if exists ddmp_prop;
CREATE TABLE ddmp_prop
	(
	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`property` VARCHAR(255) NOT NULL,
	`chebi_id` INT,
	`type` VARCHAR(12),
	`check` CHAR(1),
	`desc` TEXT,
	PRIMARY KEY(`id`)
	) TYPE=InnoDB;

-- table property class
DROP TABLE if exists ddmp_class;
CREATE TABLE ddmp_class
	(
	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`class` VARCHAR(255) NOT NULL,
	`type` VARCHAR(12),
	`desc` TEXT,
	PRIMARY KEY(`id`)
	) TYPE=InnoDB;

-- table characteristics = class+property
DROP TABLE if exists ddmp_char;
CREATE TABLE ddmp_char
	(
	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`class_id` INT unsigned NOT NULL,
	`prop_id` INT unsigned NOT NULL,
	`desc` TEXT,
	`type` VARCHAR(12),
	PRIMARY KEY(`id`)
	) TYPE=InnoDB;

-- table source
DROP TABLE if exists ddmp_src;
CREATE TABLE ddmp_src
	(
	`id` INT unsigned AUTO_INCREMENT NOT NULL,
	`source` VARCHAR(255) NOT NULL,
	`desc` TEXT,
	PRIMARY KEY(`id`)
	) TYPE=InnoDB;

ALTER TABLE `ddmp_data` ADD INDEX (`src_id`);
ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_SRC`(`src_id`) REFERENCES `ddmp_src`(`id`);

ALTER TABLE `ddmp_data` ADD INDEX (`taxa_id`);
ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_TAXA`(`taxa_id`) REFERENCES `ddmp_taxa`(`id`);

ALTER TABLE `ddmp_data` ADD INDEX (`char_id`);
ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_CHARS`(`char_id`) REFERENCES `ddmp_char`(`id`);

ALTER TABLE `ddmp_fntext` ADD INDEX (`src_id`);
ALTER TABLE `ddmp_fntext` ADD FOREIGN KEY `FK_FNTEXT_TO_SRC`(`src_id`) REFERENCES `ddmp_src`(`id`);

-- ALTER TABLE `ddmp_fnrel` ADD INDEX (`fn_id`);
-- ALTER TABLE `ddmp_fnrel` ADD FOREIGN KEY `FK_FNREL_TO_FNTEXT`(`fn_id`) REFERENCES `ddmp_fn`(`id`);
-- 
-- ALTER TABLE `ddmp_fnrel` ADD INDEX (`data_id`);
-- ALTER TABLE `ddmp_fnrel` ADD FOREIGN KEY `FK_FNREL_TO_DATA`(`data_id`) REFERENCES `ddmp_data`(`id`);

-- ALTER TABLE `ddmp_taxa` ADD INDEX (`taxonid`);
-- ALTER TABLE `ddmp_taxa` ADD FOREIGN KEY `FK_TAXA_TO_NCBI`(`taxonid`) REFERENCES `ncbi_names`(`taxonid`);

-- ALTER TABLE `ddmp_data` ADD INDEX (`txid`);
-- ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_TAXA`(`txid`) REFERENCES `ddmp_taxa`(`id`);

-- ALTER TABLE `ddmp_data` ADD INDEX (`srcid`);
-- ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_SOURCE`(`srcid`) REFERENCES `ddmp_src`(`id`);
-- 
-- ALTER TABLE `ddmp_data` ADD INDEX (`charid`);
-- ALTER TABLE `ddmp_data` ADD FOREIGN KEY `FK_DATA_TO_CHARS`(`charid`) REFERENCES `ddmp_char`(`id`);
-- 

ALTER TABLE `ddmp_fnrel` ADD INDEX (`src_id`);
ALTER TABLE `ddmp_fnrel` ADD FOREIGN KEY `FK_FNREL_TO_SOURCE`(`src_id`) REFERENCES `ddmp_src`(`id`);

ALTER TABLE `ddmp_fnrel` ADD INDEX (`taxa_id`);
ALTER TABLE `ddmp_fnrel` ADD FOREIGN KEY `FK_FNREL_TO_TAXA`(`taxa_id`) REFERENCES `ddmp_taxa`(`id`);

ALTER TABLE `ddmp_fnrel` ADD INDEX (`char_id`);
ALTER TABLE `ddmp_fnrel` ADD FOREIGN KEY `FK_FNREL_TO_CHAR`(`char_id`) REFERENCES `ddmp_char`(`id`);

-- ALTER TABLE `ddmp_fn` ADD INDEX (`srcid`);
-- ALTER TABLE `ddmp_fn` ADD FOREIGN KEY `FK_FOOT_TO_SRC`(`srcid`) REFERENCES `ddmp_src`(`id`);
-- 
ALTER TABLE `ddmp_char` ADD INDEX (`prop_id`);
ALTER TABLE `ddmp_char` ADD FOREIGN KEY `FK_CHAR_TO_PROP`(`prop_id`) REFERENCES `ddmp_prop`(`id`);

ALTER TABLE `ddmp_char` ADD INDEX (`class_id`);
ALTER TABLE `ddmp_char` ADD FOREIGN KEY `FK_CHAR_TO_CLASS`(`class_id`) REFERENCES `ddmp_class`(`id`);

-- ALTER TABLE `ddmp_prop` ADD INDEX (`chebi_id`);
-- ALTER TABLE `ddmp_prop` ADD FOREIGN KEY `FK_PROP_TO_CHEBI`(`chebi_id`) REFERENCES `chebi_compounds`(`id`);

SET FOREIGN_KEY_CHECKS=1;
