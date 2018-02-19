DROP DATABASE IF EXISTS mosaic;

CREATE DATABASE IF NOT EXISTS mosaic 
    CHARSET utf8 COLLATE utf8_unicode_ci;

USE mosaic;

CREATE TABLE tbl_images(
    numero  INTEGER NOT NULL,
    fileExtension VARCHAR(5) NOT NULL,
	num_tbl_colors INTEGER NOT NULL,
    CONSTRAINT PK_tbl_images
        PRIMARY KEY (numero)
);

CREATE TABLE tbl_colors(
    numero INTEGER AUTO_INCREMENT,
    red INTEGER NOT NULL,
    green INTEGER NOT NULL,
    blue INTEGER NOT NULL,
    CONSTRAINT PK_tbl_colors
        PRIMARY KEY (numero),
    CONSTRAINT XU_tbl_colors_rgb
        UNIQUE (red,green,blue)
);