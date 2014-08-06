DELETE FROM uploader_file02;
DELETE FROM uploader02;

INSERT INTO `uploader02` (`id`, `name`) VALUES
(1, 'user01'),
(2, 'user02');

INSERT INTO `uploader_file02` (`name`, `path`, `size`, `extension`, `position`, `user`) VALUES
('image01.jpg', 'image01.jpg', '72', 'jpg', 1, 1),
('image02.png', 'image02.png', '513', 'png', 2, 1),
('image03.png', 'image03.png', '513', 'png', 1, 2);