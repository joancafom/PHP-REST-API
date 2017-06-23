DROP TABLE DISPOSITIVO;
DROP TABLE FABRICANTE;
DROP SEQUENCE SEQ_FABRICANTE;

CREATE TABLE FABRICANTE(

	NOMBRE VARCHAR2(100) NOT NULL UNIQUE,
	DIRECCION VARCHAR2(200) NOT NULL,
	TLF VARCHAR2(15) NOT NULL UNIQUE,
	PAIS CHAR(2) NOT NULL,
	F_OID VARCHAR2(8) NOT NULL,
	PASSWORD VARCHAR2(100) NOT NULL,

	PRIMARY KEY(F_OID),
	CONSTRAINT CHECK_NUMERIC_TLF CHECK (REGEXP_LIKE(TLF, '^((\+[0-9][0-9])|(00[0-9][0-9]))?[0-9]{3,15}$')),
	CONSTRAINT CHECK_ALPHABETIC_PAIS CHECK (REGEXP_LIKE(PAIS, '^[A-Z][A-Z]$')),
	CONSTRAINT CHECK_NUMERIC_F_OID CHECK (REGEXP_LIKE(F_OID, '^[0-9]{1,8}$'))

);

CREATE TABLE DISPOSITIVO(

	MARCA VARCHAR2(100) NOT NULL,
	NOMBRE VARCHAR2(100) NOT NULL,
	COLOR VARCHAR2(20) NOT NULL,
	CAPACIDAD NUMBER(5,0) NOT NULL,
	F_OID VARCHAR2(8) NOT NULL,
	REFERENCIA NUMBER(13,0) NOT NULL,

	PRIMARY KEY (REFERENCIA),
	FOREIGN KEY (F_OID) REFERENCES FABRICANTE,
	CONSTRAINT CHECK_CAPACIDAD CHECK (CAPACIDAD > 0),
	CONSTRAINT CHECK_NUMERIC_F_OID2 CHECK (REGEXP_LIKE(F_OID, '^[0-9]{1,8}$'))

);

CREATE SEQUENCE SEQ_FABRICANTE INCREMENT BY 1 MAXVALUE 99999999;

CREATE OR REPLACE TRIGGER CREA_FABRICANTE 
BEFORE INSERT ON FABRICANTE

FOR EACH ROW
BEGIN
  SELECT SEQ_FABRICANTE.NEXTVAL INTO :NEW.F_OID  FROM DUAL;
END;
/

--OAuth 2.0

DROP TABLE oauth_jwt;
DROP TABLE oauth_scopes;
DROP TABLE oauth_users;
DROP TABLE oauth_refresh_tokens;
DROP TABLE oauth_authorization_codes;
DROP TABLE oauth_access_tokens;
DROP TABLE oauth_clients;

CREATE TABLE oauth_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80), redirect_uri VARCHAR(2000) NOT NULL, grant_types VARCHAR(80), scope VARCHAR(100), user_id VARCHAR(80), CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id));
CREATE TABLE oauth_access_tokens (access_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT access_token_pk PRIMARY KEY (access_token));
CREATE TABLE oauth_authorization_codes (authorization_code VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), redirect_uri VARCHAR(2000), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code));
CREATE TABLE oauth_refresh_tokens (refresh_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token));
CREATE TABLE oauth_users (username VARCHAR(255) NOT NULL, password VARCHAR(2000), first_name VARCHAR(255), last_name VARCHAR(255), CONSTRAINT username_pk PRIMARY KEY (username));
CREATE TABLE oauth_scopes (scope VARCHAR(2000), is_default NUMBER(1));
CREATE TABLE oauth_jwt (client_id VARCHAR(80) NOT NULL, subject VARCHAR(80), public_key VARCHAR(2000), CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id));