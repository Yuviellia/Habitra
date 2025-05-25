CREATE TABLE user_details (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    phone VARCHAR(20)
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    iddetails INT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    enabled BOOLEAN NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('ROLE_USER', 'ROLE_ADMIN')) DEFAULT 'ROLE_USER',
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (iddetails) REFERENCES user_details(id) ON DELETE CASCADE
);
ALTER INDEX users_email_key RENAME TO UNIQ_1483A5E9E7927C74;

CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    iduser INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (iduser) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE marked (
    id SERIAL PRIMARY KEY,
    idtag INT NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (idtag) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE todo (
    id SERIAL PRIMARY KEY,
    iduser INT NOT NULL,
    task TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (iduser) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO user_details (name, surname, phone) VALUES ('H', 'H', '987654321');
INSERT INTO users (iddetails, email, password, enabled, role, created_at) VALUES (1, 'h@h.h', '$2y$13$ZWDB2JbNjzAIEEnwnaT4cOdxBTJeXf3zvSRaySo9T5oM9oU.AmyH.', true, 'ROLE_USER', '2025-05-25 15:53:01.652804');

INSERT INTO user_details (name, surname, phone) VALUES ('J', 'J', '123456789');
INSERT INTO users (iddetails, email, password, enabled, role, created_at) VALUES (2, 'j@j.j', '$2a$13$6nD2jqKxAqaZXOaH1.1UouZHjVIBSuF61BDaKY2OACe9XqBuZ6JLS', true, 'ROLE_USER', '2025-05-25 15:43:56.526042');

INSERT INTO tags (iduser, name, created_at) VALUES
    (1, 'Exercise', '2025-05-25 15:45:26'),
    (1, 'Drink tea', '2025-05-25 15:45:33'),
    (1, 'Survive and thrive', '2025-05-25 15:45:48'),
    (2, 'Touch grass', '2025-05-25 15:53:28'),
    (2, 'Laugh at my teammates', '2025-05-25 15:53:41');

INSERT INTO marked (idtag, date) VALUES
    (1, '2025-05-06'), (1, '2025-05-07'), (1, '2025-05-08'), (1, '2025-05-09'), (1, '2025-05-10'),
    (2, '2025-05-11'), (2, '2025-05-12'), (2, '2025-05-10'), (2, '2025-05-07'),
    (3, '2025-05-06'), (3, '2025-05-07'), (3, '2025-05-08'), (3, '2025-05-09'), (3, '2025-05-11'),
    (3, '2025-05-10'), (3, '2025-05-12'), (1, '2025-05-15'), (1, '2025-05-16'),
    (1, '2025-05-18'), (2, '2025-05-13'), (2, '2025-05-18'), (2, '2025-05-19'),
    (2, '2025-05-16'), (3, '2025-05-13'), (3, '2025-05-15'), (3, '2025-05-16'),
    (3, '2025-05-14'), (3, '2025-05-17'), (3, '2025-05-18'), (3, '2025-05-19'),
    (3, '2025-05-20'), (3, '2025-05-21'), (3, '2025-05-22'), (2, '2025-05-20'),
    (1, '2025-05-22'), (2, '2025-05-21'), (5, '2025-05-06'), (5, '2025-05-08'),
    (5, '2025-05-07'), (5, '2025-05-09'), (5, '2025-05-10'), (5, '2025-05-11'),
    (5, '2025-05-12'), (4, '2025-05-10'), (4, '2025-05-15'), (5, '2025-05-13'),
    (5, '2025-05-14'), (5, '2025-05-15'), (5, '2025-05-16'), (5, '2025-05-18'),
    (5, '2025-05-19'), (5, '2025-05-17'), (5, '2025-05-20'), (5, '2025-05-22'),
    (5, '2025-05-23'), (5, '2025-05-21'), (4, '2025-05-24');

INSERT INTO todo (iduser, task, created_at) VALUES
    (1, 'Check emails', '2025-05-25 15:52:06'),
    (1, 'Call the dentist', '2025-05-25 15:52:06'),
    (1, 'Water the plants', '2025-05-25 15:52:06'),
    (1, 'Fold the laundry', '2025-05-25 15:52:06'),
    (1, 'Clean the kitchen counters', '2025-05-25 15:52:06'),
    (1, 'Make a grocery list for next week', '2025-05-25 15:52:06'),
    (1, 'Declutter the closet', '2025-05-25 15:52:06'),
    (1, 'Clean the bathroom', '2025-05-25 15:52:06'),
    (2, 'Hit platinum', '2025-05-25 15:56:11'),
    (2, 'Buy the battle pass', '2025-05-25 15:56:51'),
    (2, 'Nudge David to buy phasmophobia', '2025-05-25 15:58:16'),
    (2, 'SERVER MAINTENANCE 10PM-4AM, shower maybe?', '2025-05-25 15:59:54'),
    (2, 'Send that card finally', '2025-05-25 16:00:38'),
    (2, 'Buy cable', '2025-05-25 16:01:07'),
    (2, 'GRIND RANKED', '2025-05-25 16:02:23');