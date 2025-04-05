INSERT INTO
    SPECIES (NAME, CATEGORY, DESCRIPTION)
VALUES
    (
        'Lion',
        'Mammal',
        'A large carnivorous feline animal'
    ),
    (
        'Elephant',
        'Mammal',
        'A large herbivorous mammal with a trunk'
    ),
    (
        'Crocodile',
        'Reptile',
        'A large aquatic reptile with a long snout'
    ),
    (
        'Parrot',
        'Bird',
        'A colorful bird known for its ability to mimic sounds'
    ),
    (
        'Shark',
        'Fish',
        'A large predatory fish with sharp teeth'
    ),
    (
        'Penguin',
        'Bird',
        'A flightless aquatic bird adapted to cold climates'
    ),
    (
        'Tiger',
        'Mammal',
        'A powerful big cat with distinctive stripes'
    ),
    (
        'Python',
        'Reptile',
        'A large non-venomous snake that constricts its prey'
    ),
    (
        'Giraffe',
        'Mammal',
        'A tall African mammal with a very long neck'
    ),
    (
        'Dolphin',
        'Mammal',
        'An intelligent marine mammal known for its playfulness'
    );

INSERT INTO
    ANIMAL (NAME, CODE, YEAR_OF_BIRTH, SPECIES_NAME)
VALUES
    ('Simba', 'LN000001', 2020, 'Lion'),
    ('Mufasa', 'LN000002', 2015, 'Lion'),
    ('Dumbo', 'EL000001', 2019, 'Elephant'),
    ('Raja', 'EL000002', 2018, 'Elephant'),
    ('Snappy', 'CR000001', 2017, 'Crocodile'),
    ('Rio', 'PR000001', 2021, 'Parrot'),
    ('Blue', 'PR000002', 2022, 'Parrot'),
    ('Jaws', 'SH000001', 2019, 'Shark'),
    ('Happy', 'PN000001', 2021, 'Penguin'),
    ('Waddles', 'PN000002', 2020, 'Penguin'),
    ('Raja', 'TG000001', 2019, 'Tiger'),
    ('Shere', 'TG000002', 2018, 'Tiger'),
    ('Monty', 'PY000001', 2020, 'Python'),
    ('Spots', 'GF000001', 2017, 'Giraffe'),
    ('Tall', 'GF000002', 2019, 'Giraffe'),
    ('Echo', 'DP000001', 2022, 'Dolphin');

INSERT INTO
    CARETAKER (NAME, ID, LAST_NAME, PHONE)
VALUES
    ('John', 1, 'Smith', '555-0101'),
    ('Maria', 2, 'Garcia', '555-0102'),
    ('David', 3, 'Johnson', '555-0103'),
    ('Sarah', 4, 'Wilson', '555-0104'),
    ('Emma', 5, 'Thompson', '555-0105'),
    ('Luis', 6, 'Rodriguez', '555-0106'),
    ('Sophie', 7, 'Martin', '555-0107'),
    ('James', 8, 'Anderson', '555-0108');

INSERT INTO
    CARE (ID, CODE)
VALUES
    (1, 'LN000001'),
    (1, 'LN000002'),
    (2, 'EL000001'),
    (2, 'EL000002'),
    (3, 'CR000001'),
    (4, 'PR000001'),
    (4, 'PR000002'),
    (3, 'SH000001'),
    (5, 'PN000001'),
    (5, 'PN000002'),
    (6, 'TG000001'),
    (6, 'TG000002'),
    (7, 'PY000001'),
    (8, 'GF000001'),
    (8, 'GF000002'),
    (7, 'DP000001');

INSERT INTO
    SUPPLIER (AFM, NAME, LAST_NAME, PHONE)
VALUES
    ('123456789', 'Michael', 'Brown', '555-1001'),
    ('987654321', 'Emma', 'Davis', '555-1002'),
    ('456789123', 'James', 'Wilson', '555-1003'),
    ('234567891', 'Thomas', 'Clark', '555-1004'),
    ('345678912', 'Laura', 'White', '555-1005');

INSERT INTO
    FOOD (CODE, NAME, PRICE_KG, QUANTITY, SUPPLIER_AFM)
VALUES
    ('FD001', 'Meat', 12.50, 100.00, '123456789'),
    ('FD002', 'Fish', 8.75, 150.00, '123456789'),
    ('FD003', 'Hay', 3.25, 200.00, '987654321'),
    ('FD004', 'Seeds', 5.50, 50.00, '456789123'),
    ('FD005', 'Fruit', 4.75, 80.00, '456789123'),
    (
        'FD006',
        'Premium Fish',
        15.50,
        120.00,
        '234567891'
    ),
    (
        'FD007',
        'Fresh Greens',
        6.25,
        180.00,
        '234567891'
    ),
    ('FD008', 'Mixed Diet', 9.75, 90.00, '345678912');

INSERT INTO
    CONSUMPTION (SPECIES_NAME, FOOD_CODE)
VALUES
    ('Lion', 'FD001'),
    ('Elephant', 'FD003'),
    ('Parrot', 'FD004'),
    ('Shark', 'FD002'),
    ('Crocodile', 'FD002'),
    ('Penguin', 'FD006'),
    ('Tiger', 'FD001'),
    ('Python', 'FD001'),
    ('Giraffe', 'FD007'),
    ('Dolphin', 'FD006');

INSERT INTO
    VISITOR (Email, NAME, LAST_NAME)
VALUES
    ('john.doe@email.com', 'John', 'Doe'),
    ('jane.smith@email.com', 'Jane', 'Smith'),
    ('mike.brown@email.com', 'Mike', 'Brown'),
    ('sara.wilson@email.com', 'Sara', 'Wilson'),
    ('tom.davis@email.com', 'Tom', 'Davis'),
    ('alex.white@email.com', 'Alex', 'White'),
    ('emma.clark@email.com', 'Emma', 'Clark'),
    ('james.lee@email.com', 'James', 'Lee'),
    ('sophia.wang@email.com', 'Sophia', 'Wang'),
    ('lucas.martin@email.com', 'Lucas', 'Martin');

INSERT INTO
    CASHIER (ID, NAME, LAST_NAME)
VALUES
    (101, 'Alice', 'Johnson'),
    (102, 'Bob', 'Williams'),
    (103, 'Carol', 'Martinez');

INSERT INTO
    TICKET (
        CODE,
        DATE_OF_ISSUE,
        PRICE,
        CASHIER_ID,
        Email,
        CATEGORY
    )
VALUES
    (
        1001,
        '2025-04-02',
        25.00,
        101,
        'john.doe@email.com',
        'Event'
    ),
    (
        1002,
        '2025-04-02',
        20.00,
        101,
        'jane.smith@email.com',
        'No event'
    ),
    (
        1003,
        '2025-04-04',
        25.00,
        102,
        'mike.brown@email.com',
        'Event'
    ),
    (
        1004,
        '2025-04-04',
        25.00,
        102,
        'sara.wilson@email.com',
        'Event'
    ),
    (
        1005,
        '2025-04-06',
        20.00,
        103,
        'tom.davis@email.com',
        'No event'
    ),
    (
        1006,
        '2025-04-09',
        25.00,
        101,
        'alex.white@email.com',
        'Event'
    ),
    (
        1007,
        '2025-04-09',
        25.00,
        102,
        'emma.clark@email.com',
        'Event'
    ),
    (
        1008,
        '2025-04-11',
        25.00,
        103,
        'james.lee@email.com',
        'Event'
    ),
    (
        1009,
        '2025-04-11',
        20.00,
        101,
        'sophia.wang@email.com',
        'No event'
    ),
    (
        1010,
        '2025-04-16',
        25.00,
        102,
        'lucas.martin@email.com',
        'Event'
    );

INSERT INTO
    EVENT (TITLE, EVENT_DATE, EVENT_TIME, EVENT_SPACE)
VALUES
    (
        'Lion Feeding Show',
        '2025-04-02',
        '14:00:00',
        'Lion Enclosure'
    ),
    (
        'Elephant Bath Time',
        '2025-04-04',
        '11:00:00',
        'Elephant Pool'
    ),
    (
        'Bird Show',
        '2025-04-04',
        '15:00:00',
        'Amphitheater'
    ),
    (
        'Penguin Feeding',
        '2025-04-09',
        '13:00:00',
        'Arctic Zone'
    ),
    (
        'Tiger Training',
        '2025-04-11',
        '14:30:00',
        'Tiger Territory'
    ),
    (
        'Dolphin Show',
        '2025-04-16',
        '15:00:00',
        'Marine Arena'
    );

INSERT INTO
    REQUIRES (TITLE, CODE, DATE_OF_ISSUE, EVENT_DATE)
VALUES
    (
        'Lion Feeding Show',
        1001,
        '2025-04-02',
        '2025-04-02'
    ),
    (
        'Elephant Bath Time',
        1003,
        '2025-04-04',
        '2025-04-04'
    ),
    ('Bird Show', 1004, '2025-04-04', '2025-04-04'),
    (
        'Penguin Feeding',
        1006,
        '2025-04-09',
        '2025-04-09'
    ),
    (
        'Penguin Feeding',
        1007,
        '2025-04-09',
        '2025-04-09'
    ),
    (
        'Tiger Training',
        1008,
        '2025-04-11',
        '2025-04-11'
    ),
    ('Dolphin Show', 1010, '2025-04-16', '2025-04-16');

INSERT INTO
    PARTICIPATES (TITLE, EVENT_DATE, CODE)
VALUES
    ('Lion Feeding Show', '2025-04-02', 'LN000001'),
    ('Lion Feeding Show', '2025-04-02', 'LN000002'),
    ('Elephant Bath Time', '2025-04-04', 'EL000001'),
    ('Bird Show', '2025-04-04', 'PR000001'),
    ('Bird Show', '2025-04-04', 'PR000002'),
    ('Penguin Feeding', '2025-04-09', 'PN000001'),
    ('Penguin Feeding', '2025-04-09', 'PN000002'),
    ('Tiger Training', '2025-04-11', 'TG000001'),
    ('Tiger Training', '2025-04-11', 'TG000002'),
    ('Dolphin Show', '2025-04-16', 'DP000001');