create sequence userdetails_id_seq
    as integer;

alter sequence userdetails_id_seq owner to admin;

create sequence "User_id_seq"
    as integer;

alter sequence "User_id_seq" owner to admin;

create sequence tagsmarked_id_seq
    as integer;

alter sequence tagsmarked_id_seq owner to admin;

create table user_details
(
    id      integer default nextval('userdetails_id_seq'::regclass) not null
        constraint userdetails_pkey
            primary key,
    name    varchar(255)                                            not null,
    surname varchar(255)                                            not null,
    phone   varchar(20)
);

alter table user_details
    owner to admin;

alter sequence userdetails_id_seq owned by user_details.id;

create table users
(
    id        integer   default nextval('"User_id_seq"'::regclass) not null
        constraint "User_pkey"
            primary key,
    iddetails integer                                              not null
        constraint fk_userdetails
            references user_details
            on delete cascade,
    email     varchar(255)                                         not null
        constraint "User_email_key"
            unique,
    password  varchar(255)                                         not null,
    enabled   boolean   default true                               not null,
    salt      varchar(255)                                         not null,
    createdat timestamp default CURRENT_TIMESTAMP
);

alter table users
    owner to admin;

alter sequence "User_id_seq" owned by users.id;

create table tags
(
    id        serial
        primary key,
    iduser    integer      not null
        constraint fk_user
            references users
            on delete cascade,
    name      varchar(255) not null,
    createdat timestamp default CURRENT_TIMESTAMP
);

alter table tags
    owner to admin;

create table marked
(
    id    integer default nextval('tagsmarked_id_seq'::regclass) not null
        constraint tagsmarked_pkey
            primary key,
    idtag integer                                                not null
        constraint fk_tags
            references tags
            on delete cascade,
    date  date                                                   not null
);

alter table marked
    owner to admin;

alter sequence tagsmarked_id_seq owned by marked.id;

create table todo
(
    id        serial
        primary key,
    iduser    integer not null
        constraint fk_user_todo
            references users
            on delete cascade,
    task      text    not null,
    createdat timestamp default CURRENT_TIMESTAMP
);

alter table todo
    owner to admin;

create view user_tag_marked(idmark, idtag, iduser) as
SELECT m.id AS idmark,
       t.id AS idtag,
       t.iduser
FROM marked m
         JOIN tags t ON m.idtag = t.id;

alter table user_tag_marked
    owner to admin;

create view users_details(id, email, password, salt, createdat, name, surname, phone) as
SELECT u.id,
       u.email,
       u.password,
       u.salt,
       u.createdat,
       ud.name,
       ud.surname,
       ud.phone
FROM users u
         JOIN user_details ud ON u.iddetails = ud.id;

alter table users_details
    owner to admin;

create function capitalize_name_surname() returns trigger
    language plpgsql
as
$$
BEGIN
    NEW.name := INITCAP(NEW.name);
    NEW.surname := INITCAP(NEW.surname);
RETURN NEW;
END;
$$;

alter function capitalize_name_surname() owner to admin;

create trigger capitalize_name_surname_trigger
    before insert or update
                         on user_details
                         for each row
                         execute procedure capitalize_name_surname();

INSERT INTO user_details (id, name, surname, phone) VALUES (1, 'J', 'J', '123456789');
INSERT INTO user_details (id, name, surname, phone) VALUES (2, 'H', 'H', '987654321');

INSERT INTO users (id, iddetails, email, password, enabled, salt, createdat) VALUES (1, 1, 'j@j.j', '05b0c0d06ef0ad548dff2b38fb37b798e9160bd25ecb0219da6ad6f372d784a7', true, '9267a0e7b78a5f0936f25e7e7d9715ca', '2025-01-25 15:43:56.526042');
INSERT INTO users (id, iddetails, email, password, enabled, salt, createdat) VALUES (2, 2, 'h@h.h', 'a14f1064a09ab0a2e0f81ef429380754aee70298606715c5890847007f018669', true, 'c524b9114a74a7aa247bea4b9420b420', '2025-01-25 15:53:01.652804');

INSERT INTO tags (id, iduser, name, createdat) VALUES (1, 1, 'Exercise', '2025-01-25 15:45:26.000000');
INSERT INTO tags (id, iduser, name, createdat) VALUES (2, 1, 'Drink tea', '2025-01-25 15:45:33.000000');
INSERT INTO tags (id, iduser, name, createdat) VALUES (3, 1, 'Survive and thrive', '2025-01-25 15:45:48.000000');
INSERT INTO tags (id, iduser, name, createdat) VALUES (4, 2, 'Touch grass', '2025-01-25 15:53:28.000000');
INSERT INTO tags (id, iduser, name, createdat) VALUES (5, 2, 'Laugh at my teammates', '2025-01-25 15:53:41.000000');

INSERT INTO marked (id, idtag, date) VALUES (1, 1, '2025-01-06');
INSERT INTO marked (id, idtag, date) VALUES (2, 1, '2025-01-07');
INSERT INTO marked (id, idtag, date) VALUES (3, 1, '2025-01-08');
INSERT INTO marked (id, idtag, date) VALUES (4, 1, '2025-01-09');
INSERT INTO marked (id, idtag, date) VALUES (5, 1, '2025-01-10');
INSERT INTO marked (id, idtag, date) VALUES (6, 2, '2025-01-11');
INSERT INTO marked (id, idtag, date) VALUES (7, 2, '2025-01-12');
INSERT INTO marked (id, idtag, date) VALUES (8, 2, '2025-01-10');
INSERT INTO marked (id, idtag, date) VALUES (9, 2, '2025-01-07');
INSERT INTO marked (id, idtag, date) VALUES (10, 3, '2025-01-06');
INSERT INTO marked (id, idtag, date) VALUES (11, 3, '2025-01-07');
INSERT INTO marked (id, idtag, date) VALUES (12, 3, '2025-01-08');
INSERT INTO marked (id, idtag, date) VALUES (13, 3, '2025-01-09');
INSERT INTO marked (id, idtag, date) VALUES (14, 3, '2025-01-11');
INSERT INTO marked (id, idtag, date) VALUES (15, 3, '2025-01-10');
INSERT INTO marked (id, idtag, date) VALUES (16, 3, '2025-01-12');
INSERT INTO marked (id, idtag, date) VALUES (17, 1, '2025-01-15');
INSERT INTO marked (id, idtag, date) VALUES (18, 1, '2025-01-16');
INSERT INTO marked (id, idtag, date) VALUES (19, 1, '2025-01-18');
INSERT INTO marked (id, idtag, date) VALUES (20, 2, '2025-01-13');
INSERT INTO marked (id, idtag, date) VALUES (23, 2, '2025-01-18');
INSERT INTO marked (id, idtag, date) VALUES (24, 2, '2025-01-19');
INSERT INTO marked (id, idtag, date) VALUES (25, 2, '2025-01-16');
INSERT INTO marked (id, idtag, date) VALUES (26, 3, '2025-01-13');
INSERT INTO marked (id, idtag, date) VALUES (27, 3, '2025-01-15');
INSERT INTO marked (id, idtag, date) VALUES (28, 3, '2025-01-16');
INSERT INTO marked (id, idtag, date) VALUES (29, 3, '2025-01-14');
INSERT INTO marked (id, idtag, date) VALUES (30, 3, '2025-01-17');
INSERT INTO marked (id, idtag, date) VALUES (31, 3, '2025-01-18');
INSERT INTO marked (id, idtag, date) VALUES (32, 3, '2025-01-19');
INSERT INTO marked (id, idtag, date) VALUES (33, 3, '2025-01-20');
INSERT INTO marked (id, idtag, date) VALUES (34, 3, '2025-01-21');
INSERT INTO marked (id, idtag, date) VALUES (35, 3, '2025-01-22');
INSERT INTO marked (id, idtag, date) VALUES (36, 2, '2025-01-20');
INSERT INTO marked (id, idtag, date) VALUES (37, 1, '2025-01-22');
INSERT INTO marked (id, idtag, date) VALUES (38, 2, '2025-01-21');
INSERT INTO marked (id, idtag, date) VALUES (39, 5, '2025-01-06');
INSERT INTO marked (id, idtag, date) VALUES (40, 5, '2025-01-08');
INSERT INTO marked (id, idtag, date) VALUES (41, 5, '2025-01-07');
INSERT INTO marked (id, idtag, date) VALUES (42, 5, '2025-01-09');
INSERT INTO marked (id, idtag, date) VALUES (43, 5, '2025-01-10');
INSERT INTO marked (id, idtag, date) VALUES (44, 5, '2025-01-11');
INSERT INTO marked (id, idtag, date) VALUES (45, 5, '2025-01-12');
INSERT INTO marked (id, idtag, date) VALUES (46, 4, '2025-01-10');
INSERT INTO marked (id, idtag, date) VALUES (47, 4, '2025-01-15');
INSERT INTO marked (id, idtag, date) VALUES (48, 5, '2025-01-13');
INSERT INTO marked (id, idtag, date) VALUES (49, 5, '2025-01-14');
INSERT INTO marked (id, idtag, date) VALUES (50, 5, '2025-01-15');
INSERT INTO marked (id, idtag, date) VALUES (51, 5, '2025-01-16');
INSERT INTO marked (id, idtag, date) VALUES (52, 5, '2025-01-18');
INSERT INTO marked (id, idtag, date) VALUES (53, 5, '2025-01-19');
INSERT INTO marked (id, idtag, date) VALUES (54, 5, '2025-01-17');
INSERT INTO marked (id, idtag, date) VALUES (55, 5, '2025-01-20');
INSERT INTO marked (id, idtag, date) VALUES (56, 5, '2025-01-22');
INSERT INTO marked (id, idtag, date) VALUES (57, 5, '2025-01-23');
INSERT INTO marked (id, idtag, date) VALUES (58, 5, '2025-01-21');
INSERT INTO marked (id, idtag, date) VALUES (60, 4, '2025-01-24');

INSERT INTO todo (id, iduser, task, createdat) VALUES (1, 1, 'Check emails', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (2, 1, 'Call the dentist', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (3, 1, 'Water the plants', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (4, 1, 'Fold the laundry', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (5, 1, 'Clean the kitchen counters', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (6, 1, 'Make a grocery list for next week', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (7, 1, 'Declutter the closet', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (8, 1, 'Clean the bathroom', '2025-01-25 15:52:06.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (9, 2, 'Hit platinum', '2025-01-25 15:56:11.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (10, 2, 'Buy the battle pass', '2025-01-25 15:56:51.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (11, 2, 'Nudge David to buy phasmophobia', '2025-01-25 15:58:16.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (12, 2, 'SERVER MAINTENANCE 10PM-4AM, shower maybe?', '2025-01-25 15:59:54.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (13, 2, 'Send that card finally', '2025-01-25 16:00:38.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (14, 2, 'Buy cable', '2025-01-25 16:01:07.000000');
INSERT INTO todo (id, iduser, task, createdat) VALUES (16, 2, 'GRIND RANKED', '2025-01-25 16:02:23.000000');


SELECT setval('userdetails_id_seq', (SELECT MAX(id) FROM user_details), true);
SELECT setval('"User_id_seq"', (SELECT MAX(id) FROM users), true);
SELECT setval('tagsmarked_id_seq', (SELECT MAX(id) FROM marked), true);
SELECT setval('tags_id_seq', (SELECT MAX(id) FROM tags), true);
SELECT setval('todo_id_seq', (SELECT MAX(id) FROM todo), true);
