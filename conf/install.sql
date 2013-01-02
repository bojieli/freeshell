USE freeshell;
CREATE TABLE shellinfo (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `nodeno` INT(10) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `hostname` VARCHAR(255) NOT NULL,
    `isactive` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY key_email (`email`)
);
