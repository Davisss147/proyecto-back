/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  kevin
 * Created: 16/07/2020
 */

Create database if not exists enfoque365;
use enfoque365;

Create table users(
id  int(255)auto_increment not null,
name    varchar(50) not null, 
surname varchar(50),
role    varchar(20),
email   varchar(255) not null,
password    varchar(50) not null,
description text,
image   varchar(255),
created_at  datetime DEFAULT NULL,
updated_at  datetime DEFAULT NULL,
remember_token  varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;



Create table categories (
id  int(255)auto_increment not null,
name varchar(100) not null,
created_at  datetime DEFAULT NULL,
updated_at  datetime DEFAULT NULL,
CONSTRAINT pk_categories PRIMARY KEY(id)
)ENGINE=InnoDb;



Create table posts(
id  int(255)auto_increment not null,
user_id  int(255) not null,
category_id int(255) not null,
title   varchar(255) not null,
content text not null,
image   varchar(255),
created_at  datetime DEFAULT NULL,
updated_at  datetime DEFAULT NULL,
CONSTRAINT pk_posts PRIMARY KEY(id),
CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_post_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;