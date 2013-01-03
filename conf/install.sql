USE freeshell;
CREATE TABLE shellinfo (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `nodeno` INT(10) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `hostname` VARCHAR(255) NOT NULL,
    `token` CHAR(40),
    `isactive` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY key_nodeno (`nodeno`),
    KEY key_email (`email`)
) AUTO_INCREMENT = 101;