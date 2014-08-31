USE freeshell;
CREATE TABLE shellinfo (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `nodeno` INT(10) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `hostname` VARCHAR(255) NOT NULL,
    `token` CHAR(40),
    `isactive` BOOL NOT NULL DEFAULT FALSE,
    `isadmin` BOOL NOT NULL DEFAULT FALSE,
    `locked` BOOL NOT NULL DEFAULT FALSE,
    `lock_time` INT(10),
    `blocked` BOOL NOT NULL DEFAULT FALSE,
    `http_subdomain` VARCHAR(50) NOT NULL,
    `diskspace_softlimit` VARCHAR(20) NOT NULL DEFAULT '5G',
    `diskspace_hardlimit` VARCHAR(20) NOT NULL DEFAULT '7G',
    `distribution` VARCHAR(255) NOT NULL,
    `storage_base` VARCHAR(255) NOT NULL,
    `40x_page` TEXT,
    `50x_page` TEXT,
    `is_public` BOOL NOT NULL DEFAULT FALSE,
    `public_name` VARCHAR(255),
    `public_description` TEXT,
    PRIMARY KEY (`id`),
    KEY key_nodeno (`nodeno`),
    KEY key_email (`email`),
    KEY key_is_public (`is_public`)
) AUTO_INCREMENT = 101 ENGINE = InnoDB;

CREATE TABLE tickets (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `shellid` INT(10) NOT NULL,
    `token` CHAR(40) NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    `param` TEXT,
    `create_time` DATETIME,
    `used_time` DATETIME,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`shellid`) REFERENCES shellinfo (`id`)
) ENGINE = InnoDB;

CREATE TABLE ssh_log (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `nodeno` INT(10) NOT NULL,
    `action` VARCHAR(200) NOT NULL,
    `cmd` TEXT,
    `output` TEXT,
    `log_time` INT(10),
    `return_status` INT(10),
    `elapsed_time` FLOAT,
    PRIMARY KEY (`id`),
    KEY key_nodeno (`nodeno`)
) ENGINE = MyISAM;

CREATE TABLE operation_log (
    `id` INT(10) NOT NULL,
    `action` VARCHAR(200) NOT NULL,
    `data` VARCHAR(200) NOT NULL,
    `log_time` INT(10) NOT NULL,
    KEY key_id (`id`)
) ENGINE = MyISAM;

CREATE TABLE endpoint (
    `id` INT(10) NOT NULL,
    `public_endpoint` INT(5) NOT NULL,
    `private_endpoint` INT(5) NOT NULL,
    `protocol` VARCHAR(10) NOT NULL DEFAULT 'tcp',
    UNIQUE KEY key_public_endpoint (`public_endpoint`,`protocol`),
    FOREIGN KEY (`id`) REFERENCES shellinfo (`id`)
) ENGINE = InnoDB;

CREATE TABLE cname (
    `id` INT(10) NOT NULL,
    `domain` VARCHAR(255) NOT NULL,
    `is_ssl` BOOL NOT NULL DEFAULT FALSE,
    `force_ssl` BOOL NOT NULL DEFAULT FALSE,
    KEY key_id (`id`),
    UNIQUE KEY key_domain (`domain`),
    FOREIGN KEY (`id`) REFERENCES shellinfo (`id`)
) ENGINE = InnoDB;
