DELETE FROM uploader_file04;
DELETE FROM uploader04;

INSERT INTO `uploader04` (`id`, `name`) VALUES
(1, 'user01'),
(2, 'user02'),
(3, 'user03');

INSERT INTO `uploader_file04` (`id`, `name`, `path`, `size`, `extension`, `parent`, `position`) VALUES
(1, 'image01.jpg', 'image01.jpg', '72', 'jpg', 2, 1),
(2, 'image02.png', 'image02.png', '513', 'png', 2, 2),
(3, 'image03.png', 'image03.png', '0', 'png', 3, 1);
