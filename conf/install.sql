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
    `http_subdomain` VARCHAR(50) NOT NULL,
    `diskspace_softlimit` VARCHAR(20) NOT NULL DEFAULT '5G',
    `diskspace_hardlimit` VARCHAR(20) NOT NULL DEFAULT '7G',
    `distribution` VARCHAR(255) NOT NULL,
    `http_cname` TEXT,
    `40x_page` TEXT,
    `50x_page` TEXT,
    PRIMARY KEY (`id`),
    KEY key_nodeno (`nodeno`),
    KEY key_email (`email`)
) AUTO_INCREMENT = 101;

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
);

CREATE TABLE ssh_log (
    `nodeno` INT(10) NOT NULL,
    `action` VARCHAR(200) NOT NULL,
    `cmd` TEXT,
    `output` TEXT,
    `log_time` INT(10),
    `return_status` INT(10),
    `elapsed_time` FLOAT,
    KEY key_nodeno (`nodeno`)
);

CREATE TABLE endpoint (
    `id` INT(10) NOT NULL,
    `public_endpoint` INT(5) NOT NULL,
    `private_endpoint` INT(5) NOT NULL,
    UNIQUE KEY key_public_endpoint (`public_endpoint`),
    FOREIGN KEY (`id`) REFERENCES shellinfo (`id`)
);
