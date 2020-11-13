SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `zuni`.`users` 
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - não autenticado\n1 - autenticado\n2 - suspens' /* comment truncated */ /*
3 - banido*/ ;

ALTER TABLE `zuni`.`phones` 
CHANGE COLUMN `type` `type` TINYINT(1) NULL DEFAULT NULL COMMENT '1 - Residencial\n2 - Comercial\n3 - Celular\n4 - Recado\n5 - Fax' /* comment truncated */ /*6 - Outros*/ ;

ALTER TABLE `zuni`.`images` 
CHANGE COLUMN `visibility` `visibility` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - privado (visível somente para o criado do álbum)\n1 - público (disponível para outros usuários da plataf' /* comment truncated */ /*rma)
2 - externo (disponível para usuários não logados)*/ ;


DELIMITER $$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`users_insert` $$

USE `zuni`$$
CREATE TRIGGER `users_insert` BEFORE INSERT ON `users` FOR EACH ROW
SET NEW.`created_at` = NOW()
$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`users_update` $$

USE `zuni`$$
CREATE TRIGGER `users_update` BEFORE UPDATE ON `users` FOR EACH ROW
SET NEW.`updated_at` = NOW();$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`albums_insert` $$

USE `zuni`$$
CREATE TRIGGER `albums_insert` BEFORE INSERT ON `albums` FOR EACH ROW
BEGIN
	SET NEW.`created_at` = NOW();
END;$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`albums_update` $$

USE `zuni`$$
CREATE TRIGGER `albums_update` BEFORE UPDATE ON `albums` FOR EACH ROW
SET NEW.`updated_at` = NOW();$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`images_insert` $$

USE `zuni`$$
CREATE TRIGGER `images_insert` BEFORE INSERT ON `images` FOR EACH ROW
BEGIN
	SET NEW.`created_at` = NOW();
	IF NEW.`id_owner` IS NULL AND NEW.`id_user` IS NOT NULL
	THEN
		SET NEW.`id_owner` = NEW.`id_user`;
	END IF;
END;$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`images_update` $$

USE `zuni`$$
CREATE TRIGGER `images_update` BEFORE UPDATE ON `images` FOR EACH ROW
SET NEW.`updated_at` = NOW();$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`share_insert` $$

USE `zuni`$$
CREATE TRIGGER `share_insert` BEFORE INSERT ON `share` FOR EACH ROW
SET NEW.`created_at` = NOW();$$

USE `zuni`$$
DROP TRIGGER IF EXISTS `zuni`.`share_update` $$

USE `zuni`$$
CREATE TRIGGER `share_update` BEFORE UPDATE ON `share` FOR EACH ROW
SET NEW.`updated_at` = NOW();$$


DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
