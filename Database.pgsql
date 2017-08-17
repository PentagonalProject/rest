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
-- RANDOM PASSWORD
--

Create or replace function random_password() returns text as
$$
declare
  chars text[] := '{.,/,0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z}';
  result text := '$2a$08$';
begin
  LOOP
    result := result || chars[1+random()*(array_length(chars, 1)-1)];
    EXIT WHEN length(result) = 60;
  END LOOP;

  return result;
end;
$$ language plpgsql;

-- --------------------------------------------------------
--
-- RANDOM HEX
--

Create or replace function random_hex(length integer) returns text as
$$
declare
  chars text[] := '{0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f}';
  result text := '';
  i integer := 0;
begin
  if length < 0 then
    raise exception 'Given length cannot be less than 0';
  end if;

  for i in 1..length loop
    result := result || chars[1+random()*(array_length(chars, 1)-1)];
  end loop;
  return result;
end;
$$ language plpgsql;

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
  last_name varchar(64) NOT NULL DEFAULT '',
  username varchar(64) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(60) NOT NULL DEFAULT random_password(),
  private_key varchar(128) DEFAULT random_hex(128),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT '1990-01-01 00:00:00.000000'::timestamp without time zone
);

--
-- Indexes for table "users"
--
ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE (username, email);

COMMENT ON COLUMN users.password IS 'sha1 string PasswordHash - (phpass by openwall)';
COMMENT ON COLUMN users.private_key IS 'Private Grant token API';
COMMENT ON COLUMN users.updated_at IS 'use 1990-01-01 00:00:00.000000 to prevent error sql time stamp zero value';

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
  updated_at TIMESTAMP DEFAULT '1990-01-01 00:00:00.000000'::timestamp without time zone
);

COMMENT ON COLUMN recipes.user_id IS 'Relation for "users.id"';
COMMENT ON COLUMN recipes.updated_at IS 'use 1990-01-01 00:00:00.000000 to prevent error sql time stamp zero value';

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
