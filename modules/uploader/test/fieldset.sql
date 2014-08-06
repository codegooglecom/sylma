DELETE FROM uploader_file03;
DELETE FROM uploader03;

INSERT INTO `uploader03` (`id`, `name`) VALUES
(1, 'user01'),
(2, 'user02'),
(3, 'user03');

INSERT INTO `uploader_file03` (`id`, `name`, `path`, `size`, `extension`, `parent`) VALUES
(1, 'image01.jpg', 'image01.jpg', '72', 'jpg', 2),
(2, 'image02.png', 'image02.png', '513', 'png', 2),
(3, 'image03.png', 'image03.png', '0', 'png', 3);
