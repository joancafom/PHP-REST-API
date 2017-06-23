INSERT INTO FABRICANTES (NOMBRE, DIRECCION, TLF, PAIS) VALUES ('Apple Inc.', '1 Infinite Loop, California', '+1987654320', 'USA');
INSERT INTO FABRICANTES (NOMBRE, DIRECCION, TLF, PAIS) VALUES ('BQ', 'Las Rozas, Madrid', '+34911829384', 'ESP');
INSERT INTO FABRICANTES (NOMBRE, DIRECCION, TLF, PAIS) VALUES ('Huawei', 'Longgang, Shenzhen', '+86987654321', 'CHN');
INSERT INTO FABRICANTES (NOMBRE, DIRECCION, TLF, PAIS) VALUES ('LG Electronics', 'LG Twin Towers, Seul', '+82987654322', 'KOR');
INSERT INTO FABRICANTES (NOMBRE, DIRECCION, TLF, PAIS) VALUES ('Samsung Mobile', 'Samsung Town, Seul', '+82987654323', 'KOR');



INSERT INTO DISPOSITIVOS VALUES ('Apple', 'iPhone 7 Plus', 'Jet Black', 128, 1, 1000000000000);
INSERT INTO DISPOSITIVOS VALUES ('Apple', 'iPad Air 2', 'Space Gray', 64, 1, 1000000000001);
INSERT INTO DISPOSITIVOS VALUES ('BQ', 'Aquaris U', 'Blanco', 8, 2, 2000000000000);
INSERT INTO DISPOSITIVOS VALUES ('BQ', 'Aquaris M', 'Negro', 16, 2, 2000000000001);
INSERT INTO DISPOSITIVOS VALUES ('Huawei', 'P9 Lite', 'Negro', 16, 3, 3000000000000);
INSERT INTO DISPOSITIVOS VALUES ('Honor', '6 Plus', 'Dorado', 16, 3, 3000000000001);
INSERT INTO DISPOSITIVOS VALUES ('LG', 'G6+', 'Mystic White', 64, 4, 4000000000000);
INSERT INTO DISPOSITIVOS VALUES ('Google', 'Nexus 5X', 'Ice Blue', 32, 4, 4000000000001);
INSERT INTO DISPOSITIVOS VALUES ('Samsung', 'Galaxy S8+', 'Orchid Gray', 64, 5, 5000000000000);
INSERT INTO DISPOSITIVOS VALUES ('Google', 'Galaxy Nexus', 'Midnight Black', 16, 5, 5000000000001);

--Oauth 2.0

INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ('testClient', 'testPassword', 'http://localhost/API/oauth2/oauth2callback.php');