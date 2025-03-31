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