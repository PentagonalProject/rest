SET timezone = "+00:00";

-- DROP TABLE IF EXISTS options;
-- DROP TABLE IF EXISTS users;
-- DROP TABLE IF EXISTS recipes;

-- --------------------------------------------------------
--
-- Functions timestamp update for "updated_at"
--

CREATE OR REPLACE FUNCTION update_change_updated_at_column()
  RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ language 'plpgsql';


-- --------------------------------------------------------
--
-- Table structure for table options
--

CREATE TABLE options (
  id BIGSERIAL NOT NULL,
  option_name varchar(255) NOT NULL,
  option_value TEXT NOT NULL
);

--
-- Indexes for table "options"
--
ALTER TABLE options
  ADD PRIMARY KEY (id),
  ADD UNIQUE (option_name);

COMMENT ON COLUMN options.option_name IS 'Unique Option Name';

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE TABLE users (
  id BIGSERIAL NOT NULL,
  first_name varchar(64) NOT NULL,
  last_name varchar(64) NOT NULL,
  username varchar(64) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(60) DEFAULT NULL,
  private_key varchar(128) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT '1990-01-01 00:00:00'
);

--
-- Indexes for table "users"
--
ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE (username, email);

COMMENT ON COLUMN users.password IS 'sha1 string PasswordHash - (phpass by openwall)';
COMMENT ON COLUMN users.private_key IS 'Private Grant token API';
COMMENT ON COLUMN users.updated_at IS 'use 1990-01-01 00:00:00 to prevent error sql time stamp zero value';

--
-- Triggers for table "users"
--

DROP TRIGGER IF EXISTS update_change_updated_at_users ON users;
CREATE TRIGGER update_change_updated_at_users BEFORE UPDATE
  ON users FOR EACH ROW EXECUTE PROCEDURE
  update_change_updated_at_column();

-- --------------------------------------------------------

--
-- Table structure for table recipes
--

CREATE TABLE recipes (
  id BIGSERIAL NOT NULL,
  user_id BIGINT NOT NULL,
  name varchar(60) NOT NULL,
  instructions text NOT NULL,
  status BIGINT NOT NULL DEFAULT '1',
  published_at timestamp NULL DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NULL DEFAULT '1990-01-01 00:00:00'
);

COMMENT ON COLUMN recipes.user_id IS 'Relation for "users.id"';
COMMENT ON COLUMN recipes.updated_at IS 'use 1990-01-01 00:00:00 to prevent error sql time stamp zero value';

--
-- Indexes for table recipes
--
ALTER TABLE recipes
  ADD PRIMARY KEY (id);

--
-- Triggers for table "recipes"
--

DROP TRIGGER IF EXISTS update_change_updated_at_recipes ON recipes;
CREATE TRIGGER update_change_updated_at_recipes BEFORE UPDATE
  ON recipes FOR EACH ROW EXECUTE PROCEDURE
  update_change_updated_at_column();
