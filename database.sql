CREATE DATABASE IF NOT EXISTS api_rest_laravel;
USE api_rest_laravel;

CREATE TABLE users(
    id int auto_increment NOT NULL,
    name VARCHAR(50) NOT NULL,
    surename VARCHAR(100),
    role VARCHAR(20),
    email varchar(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    description text,
    image varchar(255),
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    remember_token varchar(255),
    CONSTRAINT pk_users PRIMARY KEY (id)
)ENGINNE=InnoDb;

CREATE TABLE categories(
    id int auto_increment NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    CONSTRAINT pk_categories PRIMARY KEY (id)
)ENGINNE=InnoDb;

CREATE TABLE posts(
    id int auto_increment NOT NULL,
    user_id int NOT NULL,
    category_id int NOT NOT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    CONSTRAINT pk_posts PRIMARY KEY (id),
    CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINNE=InnoDb;