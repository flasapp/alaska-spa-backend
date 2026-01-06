-- Ensure an admin user exists
-- This ignores the insert if the email already exists, but you might want to manually update the role if the user exists but is not an admin.
-- Password '123456' hashed with MD5 (as used in the project) is 'e10adc3949ba59abbe56e057f20f883e'

INSERT INTO `usuarios` (`nomUsuario`, `apellido`, `tel`, `mail`, `pass`, `fechaAlta`, `rol`, `estado`) 
VALUES ('Admin', 'User', '000000000', 'admin@alaska.com', MD5('123456'), NOW(), 1, 1)
ON DUPLICATE KEY UPDATE `rol` = 1;
