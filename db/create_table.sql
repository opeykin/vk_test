CREATE TABLE items (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    price int NOT NULL,
    description varchar(2048),
    img varchar(1024) NOT NULL,
    PRIMARY KEY (id),
    INDEX price_index USING BTREE (price)
);
