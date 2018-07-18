CREATE TABLE IF NOT EXISTS articles (
  id INT AUTO_INCREMENT,
  atname VARCHAR(255),
  atcontents TEXT,
  attime INT,
  atauthor VARCHAR(255),
  PRIMARY KEY(id),
  INDEX(id)
)  DEFAULT CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT,
  ctname VARCHAR(255),
  PRIMARY KEY(id),
  INDEX(id)
)  DEFAULT CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS articlestocategories (
  id INT AUTO_INCREMENT,
  article_id INT,
  category_id INT,
  PRIMARY KEY(id),
  INDEX(article_id),
  INDEX(category_id)
)  DEFAULT CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT,
  usname VARCHAR(255),
  uspassword VARCHAR(255),
  PRIMARY KEY(id)
)  DEFAULT CHARACTER SET utf8;

INSERT INTO settings (usname, uspassword) VALUES ('admin', 'admin');
