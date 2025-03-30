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

