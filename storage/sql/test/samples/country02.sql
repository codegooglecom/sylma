TRUNCATE TABLE `sandbox`.`country01`;
TRUNCATE TABLE `sandbox`.`city01`;

INSERT INTO `country01` (`id`, `name`) VALUES
(1, 'Turkey'),
(2, 'India'),
(3, 'Russia');

INSERT INTO `city01` (`id`, `name`, `country`) VALUES
(1, 'Delhi', 2),
(2, 'Istanbul', 1),
(3, 'Iekaterinbourg', 3),
(4, 'Moscow', 3);
