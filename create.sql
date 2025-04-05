USE ZOO;

CREATE TABLE
    SPECIES (
        NAME VARCHAR(20),
        CATEGORY VARCHAR(20) NOT NULL,
        DESCRIPTION TEXT NOT NULL,
        PRIMARY KEY (NAME)
    );

CREATE TABLE
    ANIMAL (
        NAME VARCHAR(20) NOT NULL,
        CODE CHAR(8),
        YEAR_OF_BIRTH YEAR (4) NOT NULL,
        SPECIES_NAME VARCHAR(20) NOT NULL,
        PRIMARY KEY (CODE),
        FOREIGN KEY (SPECIES_NAME) REFERENCES SPECIES (NAME)
    );

CREATE TABLE
    CARETAKER (
        NAME VARCHAR(20) NOT NULL,
        ID INT AUTO_INCREMENT,
        LAST_NAME VARCHAR(20) NOT NULL,
        PHONE VARCHAR(15) NOT NULL,
        PRIMARY KEY (ID)
    );

CREATE TABLE
    CARE (
        ID INT,
        CODE CHAR(8),
        PRIMARY KEY (ID, CODE),
        FOREIGN KEY (CODE) REFERENCES ANIMAL (CODE),
        FOREIGN KEY (ID) REFERENCES CARETAKER (ID)
    );

CREATE TABLE
    SUPPLIER (
        AFM CHAR(9),
        NAME VARCHAR(20) NOT NULL,
        LAST_NAME VARCHAR(20) NOT NULL,
        PHONE VARCHAR(15) NOT NULL,
        PRIMARY KEY (AFM)
    );

CREATE TABLE
    FOOD (
        CODE CHAR(5),
        NAME VARCHAR(20) NOT NULL,
        PRICE_KG DECIMAL(6, 2) NOT NULL,
        QUANTITY DECIMAL(6, 2) NOT NULL,
        SUPPLIER_AFM CHAR(9) NOT NULL,
        PRIMARY KEY (CODE),
        FOREIGN KEY (SUPPLIER_AFM) REFERENCES SUPPLIER (AFM)
    );

CREATE TABLE
    CONSUMPTION (
        SPECIES_NAME VARCHAR(20),
        FOOD_CODE CHAR(5),
        PRIMARY KEY (SPECIES_NAME, FOOD_CODE),
        FOREIGN KEY (SPECIES_NAME) REFERENCES SPECIES (NAME),
        FOREIGN KEY (FOOD_CODE) REFERENCES FOOD (CODE)
    );

CREATE TABLE
    VISITOR (
        Email VARCHAR(50),
        NAME VARCHAR(20) NOT NULL,
        LAST_NAME VARCHAR(20) NOT NULL,
        PRIMARY KEY (Email)
    );

CREATE TABLE
    CASHIER (
        ID INT,
        NAME VARCHAR(20) NOT NULL,
        LAST_NAME VARCHAR(20) NOT NULL,
        PRIMARY KEY (ID)
    );

CREATE TABLE
    TICKET (
        CODE INT,
        DATE_OF_ISSUE DATE,
        PRICE Decimal(4, 2) NOT NULL,
        CASHIER_ID INT,
        Email VARCHAR(50) NOT NULL,
        CATEGORY ENUM ('Event', 'No event') NOT NULL,
        PRIMARY KEY (CODE, DATE_OF_ISSUE),
        FOREIGN KEY (CASHIER_ID) REFERENCES CASHIER (ID),
        FOREIGN KEY (Email) REFERENCES VISITOR (Email),
        CONSTRAINT CHECK_IF_SUNDAY CHECK (DAYOFWEEK (DATE_OF_ISSUE) NOT IN (1))
    );

CREATE TABLE
    EVENT (
        TITLE VARCHAR(100),
        EVENT_DATE DATE,
        EVENT_TIME TIME NOT NULL,
        EVENT_SPACE VARCHAR(20) NOT NULL,
        PRIMARY KEY (TITLE, EVENT_DATE),
        CONSTRAINT CHECK_DAY_OF_WEEK CHECK (DAYOFWEEK (EVENT_DATE) IN (2, 4, 6))
    );

CREATE TABLE
    REQUIRES (
        TITLE VARCHAR(100),
        CODE INT,
        DATE_OF_ISSUE DATE,
        EVENT_DATE DATE,
        PRIMARY KEY (TITLE, DATE_OF_ISSUE, CODE, EVENT_DATE),
        FOREIGN KEY (TITLE, EVENT_DATE) REFERENCES EVENT (TITLE, EVENT_DATE),
        FOREIGN KEY (CODE, DATE_OF_ISSUE) REFERENCES TICKET (CODE, DATE_OF_ISSUE)
    );

CREATE TABLE
    PARTICIPATES (
        TITLE VARCHAR(100),
        EVENT_DATE DATE,
        CODE CHAR(8),
        PRIMARY KEY (TITLE, EVENT_DATE, CODE),
        FOREIGN KEY (TITLE, EVENT_DATE) REFERENCES EVENT (TITLE, EVENT_DATE),
        FOREIGN KEY (CODE) REFERENCES ANIMAL (CODE)
    );

DELIMITER / / CREATE TRIGGER EVENT_CHECK BEFORE INSERT ON TICKET FOR EACH ROW BEGIN IF NEW.CATEGORY = 'Event'
AND NOT EXISTS (
    SELECT
        EVENT.EVENT_DATE
    FROM
        EVENT
    WHERE
        EVENT.EVENT_DATE = NEW.DATE_OF_ISSUE
) THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = 'No event on this date';

END IF;

END / / CREATE TRIGGER EVENT_COUNT BEFORE INSERT ON EVENT FOR EACH ROW BEGIN IF (
    SELECT
        COUNT(*)
    FROM
        EVENT
    WHERE
        NEW.EVENT_DATE = EVENT_DATE
) >= 2 THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = '2 events already scheduled for this date';

END IF;

END / / CREATE TRIGGER TICKET_CHECK BEFORE INSERT ON REQUIRES FOR EACH ROW BEGIN IF (
    SELECT
        TICKET.CATEGORY
    FROM
        TICKET
    WHERE
        TICKET.CODE = NEW.CODE
) = 'No event' THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = 'This ticket can not be used for an event';

ELSEIF NEW.EVENT_DATE != NEW.DATE_OF_ISSUE THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = 'This ticket is not valid for the event';

END IF;

END / / DELIMITER;