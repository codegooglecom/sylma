DELETE FROM uploader_file01;
DELETE FROM uploader01;

INSERT INTO `uploader01` (`id`, `name`) VALUES
(1, 'user01'),
(2, 'user02');

INSERT INTO `uploader_file01` (`name`, `path`, `size`, `extension`, `user`) VALUES
('image01.jpg', 'image01.jpg', '72', 'jpg', 1),
('image02.png', 'image02.png', '513', 'png', 1),
('image03.png', 'image02.png', '513', 'png', 2);

