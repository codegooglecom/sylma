TRUNCATE TABLE `sylma_stepper_user01`;
DELETE FROM `sylma_stepper_group01`;

INSERT INTO `sandbox`.`sylma_stepper_group01` (
`id` ,
`name`
)
VALUES (
1 , 'green'
), (
2 , 'blue'
), (
3 , 'yellow'
);

INSERT INTO `sandbox`.`sylma_stepper_user01` (
`id` ,
`name` ,
`group`
)
VALUES (
1 , 'alexandra', '1'
), (
2 , 'faith', '2'
), (
3 , 'isaac', '3'
);
