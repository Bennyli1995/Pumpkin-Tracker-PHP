-- Disable foreign key checks to avoid errors during dropping
BEGIN
   EXECUTE IMMEDIATE 'ALTER SESSION SET "_ORACLE_SCRIPT"=true';
   EXECUTE IMMEDIATE 'ALTER SESSION SET CONSTRAINTS = DISABLED';
EXCEPTION
   WHEN OTHERS THEN
      DBMS_OUTPUT.put_line('Failed to disable constraints: ' || SQLERRM);
END;
/

-- Drop tables and sequences if they exist
DECLARE
   table_not_exist EXCEPTION;
   PRAGMA EXCEPTION_INIT(table_not_exist, -942);
   sequence_not_exist EXCEPTION;
   PRAGMA EXCEPTION_INIT(sequence_not_exist, -2289);
BEGIN
   -- Drop tables in the reverse order of dependencies
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE PumpkinPatch CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE PatchMap CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE PumpkinVariety CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE MapRegion CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE HarvestSchedule CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE PatchTracksVariety CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE EquipmentLog CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE MaintenanceSchedule CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE MarketingPlan CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE SpecialEvent CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE Tickets CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE Activities CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE KidsActivities CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   BEGIN EXECUTE IMMEDIATE 'DROP TABLE AdultActivities CASCADE CONSTRAINTS'; EXCEPTION WHEN table_not_exist THEN NULL; END;
   -- Drop the sequence
   BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE ACTIVITIES_SEQ'; EXCEPTION WHEN sequence_not_exist THEN NULL; END;
END;
/

-- Create Sequence for auto-incrementing IDs
CREATE SEQUENCE ACTIVITIES_SEQ
  MINVALUE 1
  START WITH 19
  INCREMENT BY 1
/

-- create tables
CREATE TABLE PumpkinPatch (
    PatchID INTEGER PRIMARY KEY,
    PatchOwnership VARCHAR2(25),
    PatchSize INTEGER,
    PatchAddress VARCHAR2(25),
    PatchName VARCHAR2(25) NOT NULL UNIQUE
);

CREATE TABLE PumpkinVariety (
    VarietyID INTEGER PRIMARY KEY,
    QuantityPlanted INTEGER,
    PlantedDate DATE,
    VarietyName VARCHAR2(25) UNIQUE NOT NULL
);

-- added UNIQUE constraint to PatchID due to one-to-one relationship
CREATE TABLE PatchMap (
    MapID INTEGER PRIMARY KEY,
    PatchID INTEGER NOT NULL UNIQUE,
    Paths VARCHAR2(25),
    MapDescription VARCHAR2(25),
    FOREIGN KEY (PatchID) REFERENCES PumpkinPatch(PatchID) ON DELETE CASCADE
);

-- updated primary key to include both MapID and RegionID as it is a weak entity
CREATE TABLE MapRegion (
    RegionID INTEGER NOT NULL,
    MapID INTEGER NOT NULL,
    VarietyID INTEGER,
    RegionSize INTEGER,
    PRIMARY KEY (RegionID, MapID),
    FOREIGN KEY (MapID) REFERENCES PatchMap(MapID) ON DELETE CASCADE,
    FOREIGN KEY (VarietyID) REFERENCES PumpkinVariety(VarietyID)
);

CREATE TABLE HarvestSchedule (
    PlantedDate DATE,
    VarietyName VARCHAR2(25),
    HarvestDate DATE,
    PRIMARY KEY (PlantedDate, VarietyName)
);

CREATE TABLE PatchTracksVariety (
    PatchID INTEGER,
    VarietyID INTEGER,
    PRIMARY KEY (PatchID, VarietyID),
    FOREIGN KEY (PatchID) REFERENCES PumpkinPatch(PatchID) ON DELETE CASCADE,
    FOREIGN KEY (VarietyID) REFERENCES PumpkinVariety(VarietyID) ON DELETE CASCADE
);

CREATE TABLE MaintenanceSchedule (
    LastMaintenanceDate DATE PRIMARY KEY,
    NextMaintenanceDate DATE
);

-- added UNIQUE constraint to PatchID due to one-to-one relationship
CREATE TABLE EquipmentLog (
    LogID INTEGER PRIMARY KEY,
    LastMaintenanceDate DATE,
    EquipmentCount INTEGER,
    PatchID INTEGER UNIQUE,
    FOREIGN KEY (PatchID) REFERENCES PumpkinPatch(PatchID) ON DELETE CASCADE
);

CREATE TABLE MarketingPlan (
    PlanName VARCHAR2(25) PRIMARY KEY,
    PlanDescription VARCHAR2(25),
    SocialMediaRecords VARCHAR2(25),
    AdvertisingRecords VARCHAR2(25), 
    PatchID INTEGER,
    FOREIGN KEY (PatchID) REFERENCES PumpkinPatch(PatchID) ON DELETE CASCADE
);

CREATE TABLE SpecialEvent (
    EventID INTEGER PRIMARY KEY,
    EventName VARCHAR2(30) NOT NULL,
    PlanName VARCHAR2(30) NOT NULL,
    FOREIGN KEY (PlanName) REFERENCES MarketingPlan(PlanName) ON DELETE CASCADE
);

-- updated primary key to include both TicketID and EventID as it is a weak entity
CREATE TABLE Tickets (
    TicketID INTEGER NOT NULL,
    EventID INTEGER NOT NULL,
    AdmissionType VARCHAR2(25),
    VisitorType VARCHAR2(25),
    PRIMARY KEY (TicketID, EventID),
    FOREIGN KEY (EventID) REFERENCES SpecialEvent(EventID) ON DELETE CASCADE
);

CREATE TABLE Activities (
    ActivityID INTEGER PRIMARY KEY,
    Duration INTEGER,
    Fee INTEGER,
    ActivityDescription VARCHAR2(30),
    ActivityName VARCHAR2(25) NOT NULL,
    PatchID INTEGER,
    FOREIGN KEY (PatchID) REFERENCES PumpkinPatch(PatchID) ON DELETE CASCADE
);

CREATE TABLE KidsActivities (
    ActivityID INTEGER NOT NULL,
    GuardianRequirement INTEGER,
    PRIMARY KEY (ActivityID),
    FOREIGN KEY (ActivityID) REFERENCES Activities(ActivityID) ON DELETE CASCADE
);

CREATE TABLE AdultActivities (
    ActivityID INTEGER NOT NULL,
    AgeRequirement INTEGER,
    AlcoholInvolvement INTEGER,
    PRIMARY KEY (ActivityID),
    FOREIGN KEY (ActivityID) REFERENCES Activities(ActivityID) ON DELETE CASCADE
);

-- populate tables
-- Inserting into PumpkinPatch table
INSERT ALL
  INTO PumpkinPatch (PatchID, PatchOwnership, PatchSize, PatchAddress, PatchName) VALUES (1, 'Owner1', 100, 'Address1', 'Sunny Farm')
  INTO PumpkinPatch (PatchID, PatchOwnership, PatchSize, PatchAddress, PatchName) VALUES (2, 'Owner2', 200, 'Address2', 'Happy Farm')
  INTO PumpkinPatch (PatchID, PatchOwnership, PatchSize, PatchAddress, PatchName) VALUES (3, 'Owner3', 150, 'Address3', 'Green Farm')
  INTO PumpkinPatch (PatchID, PatchOwnership, PatchSize, PatchAddress, PatchName) VALUES (4, 'Owner4', 300, 'Address4', 'Organic Farm')
  INTO PumpkinPatch (PatchID, PatchOwnership, PatchSize, PatchAddress, PatchName) VALUES (5, 'Owner5', 250, 'Address5', 'Country Farm')
SELECT * FROM dual;

-- Inserting into PumpkinVariety table
INSERT ALL
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (1, 50, TO_DATE('2023-04-01', 'YYYY-MM-DD'), 'Big Max')
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (2, 75, TO_DATE('2023-04-15', 'YYYY-MM-DD'), 'Sugar Pie')
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (3, 65, TO_DATE('2023-04-20', 'YYYY-MM-DD'), 'Cinderella')
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (4, 80, TO_DATE('2023-05-01', 'YYYY-MM-DD'), 'Jarrahdale')
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (5, 100, TO_DATE('2023-05-10', 'YYYY-MM-DD'), 'Kabocha')
  INTO PumpkinVariety (VarietyID, QuantityPlanted, PlantedDate, VarietyName) VALUES (6, 100, TO_DATE('2023-05-10', 'YYYY-MM-DD'), 'Something Else')
SELECT * FROM dual;

-- Inserting into PatchMap table
INSERT ALL
  INTO PatchMap (MapID, PatchID, Paths, MapDescription) VALUES (1, 1, 'Path1', 'Description1')
  INTO PatchMap (MapID, PatchID, Paths, MapDescription) VALUES (2, 2, 'Path2', 'Description2')
  INTO PatchMap (MapID, PatchID, Paths, MapDescription) VALUES (3, 3, 'Path3', 'Description3')
  INTO PatchMap (MapID, PatchID, Paths, MapDescription) VALUES (4, 4, 'Path4', 'Description4')
  INTO PatchMap (MapID, PatchID, Paths, MapDescription) VALUES (5, 5, 'Path5', 'Description5')
SELECT * FROM dual;

-- Inserting into MapRegion table
INSERT ALL
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (1, 1, 1, 20)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (2, 1, 2, 10)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (3, 1, 3, 5)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (4, 1, 4, 2)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (5, 1, 5, 18)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (6, 2, 1, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (7, 2, 2, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (8, 2, 3, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (9, 2, 4, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (10, 2, 5, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (11, 3, 1, 30)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (12, 3, 2, 15)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (14, 3, 3, 25)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (13, 3, 4, 7)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (15, 3, 5, 1)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (16, 4, 1, 50)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (17, 4, 2, 35)  
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (18, 4, 3, 28)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (19, 4, 4, 17)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (20, 4, 5, 44)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (21, 5, 1, 75)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (22, 5, 2, 2)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (23, 5, 3, 60)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (24, 5, 4, 75)
  INTO MapRegion (RegionID, MapID, VarietyID, RegionSize) VALUES (25, 5, 5, 80)
SELECT * FROM dual;

-- Inserting into HarvestSchedule table
INSERT ALL
  INTO HarvestSchedule (PlantedDate, VarietyName, HarvestDate) VALUES (TO_DATE('2023-04-01', 'YYYY-MM-DD'), 'Big Max', TO_DATE('2023-10-01', 'YYYY-MM-DD'))
  INTO HarvestSchedule (PlantedDate, VarietyName, HarvestDate) VALUES (TO_DATE('2023-04-15', 'YYYY-MM-DD'), 'Sugar Pie', TO_DATE('2023-10-15', 'YYYY-MM-DD'))
  INTO HarvestSchedule (PlantedDate, VarietyName, HarvestDate) VALUES (TO_DATE('2023-04-20', 'YYYY-MM-DD'), 'Cinderella', TO_DATE('2023-10-20', 'YYYY-MM-DD'))
  INTO HarvestSchedule (PlantedDate, VarietyName, HarvestDate) VALUES (TO_DATE('2023-05-01', 'YYYY-MM-DD'), 'Jarrahdale', TO_DATE('2023-11-01', 'YYYY-MM-DD'))
  INTO HarvestSchedule (PlantedDate, VarietyName, HarvestDate) VALUES (TO_DATE('2023-05-10', 'YYYY-MM-DD'), 'Kabocha', TO_DATE('2023-11-10', 'YYYY-MM-DD'))
SELECT * FROM dual;

-- Inserting into PatchTracksVariety table
INSERT ALL
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 1)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 2)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 3)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 4)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 5)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (1, 6)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (2, 2)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (2, 3)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (2, 4)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (3, 3)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (4, 4)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 1)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 2)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 3)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 4)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 5)
  INTO PatchTracksVariety (PatchID, VarietyID) VALUES (5, 6)
SELECT * FROM dual;

-- Inserting into MaintenanceSchedule table
INSERT ALL
  INTO MaintenanceSchedule (LastMaintenanceDate, NextMaintenanceDate) VALUES (TO_DATE('2023-03-01', 'YYYY-MM-DD'), TO_DATE('2023-09-01', 'YYYY-MM-DD'))
  INTO MaintenanceSchedule (LastMaintenanceDate, NextMaintenanceDate) VALUES (TO_DATE('2023-03-15', 'YYYY-MM-DD'), TO_DATE('2023-09-15', 'YYYY-MM-DD'))
  INTO MaintenanceSchedule (LastMaintenanceDate, NextMaintenanceDate) VALUES (TO_DATE('2023-04-01', 'YYYY-MM-DD'), TO_DATE('2023-10-01', 'YYYY-MM-DD'))
  INTO MaintenanceSchedule (LastMaintenanceDate, NextMaintenanceDate) VALUES (TO_DATE('2023-04-15', 'YYYY-MM-DD'), TO_DATE('2023-10-15', 'YYYY-MM-DD'))
  INTO MaintenanceSchedule (LastMaintenanceDate, NextMaintenanceDate) VALUES (TO_DATE('2023-05-01', 'YYYY-MM-DD'), TO_DATE('2023-11-01', 'YYYY-MM-DD'))
SELECT * FROM dual;

-- Inserting into EquipmentLog table
INSERT ALL
  INTO EquipmentLog (LogID, LastMaintenanceDate, EquipmentCount, PatchID) VALUES (1, TO_DATE('2023-03-01', 'YYYY-MM-DD'), 5, 1)
  INTO EquipmentLog (LogID, LastMaintenanceDate, EquipmentCount, PatchID) VALUES (2, TO_DATE('2023-03-15', 'YYYY-MM-DD'), 7, 2)
  INTO EquipmentLog (LogID, LastMaintenanceDate, EquipmentCount, PatchID) VALUES (3, TO_DATE('2023-04-01', 'YYYY-MM-DD'), 6, 3)
  INTO EquipmentLog (LogID, LastMaintenanceDate, EquipmentCount, PatchID) VALUES (4, TO_DATE('2023-04-15', 'YYYY-MM-DD'), 8, 4)
  INTO EquipmentLog (LogID, LastMaintenanceDate, EquipmentCount, PatchID) VALUES (5, TO_DATE('2023-05-01', 'YYYY-MM-DD'), 10, 5)
SELECT * FROM dual;

-- Inserting into MarketingPlan table
INSERT ALL
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan1', 'Description1', 'Record1', 'AdRecord1', 1)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan21', 'Description21', 'Record21', 'AdRecord21', 2)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan22', 'Description22', 'Record22', 'AdRecord22', 2)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan31', 'Description31', 'Record31', 'AdRecord31', 3)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan32', 'Description32', 'Record32', 'AdRecord32', 3)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan33', 'Description33', 'Record33', 'AdRecord33', 3)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan4', 'Description4', 'Record4', 'AdRecord4', 4)
  INTO MarketingPlan (PlanName, PlanDescription, SocialMediaRecords, AdvertisingRecords, PatchID) VALUES ('Plan5', 'Description5', 'Record5', 'AdRecord5', 5)
SELECT * FROM dual;

-- Inserting into SpecialEvent table
INSERT ALL
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (1, 'Halloween Bash', 'Plan1')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (211, 'Harvest Festival1,T', 'Plan21')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (212, 'Harvest Festival2', 'Plan21')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (221, 'Harvest Festival3', 'Plan22')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (222, 'Harvest Festival4', 'Plan22')  
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (31, 'Pumpkin Carving Contest1', 'Plan31')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (32, 'Pumpkin Carving Contest2', 'Plan32')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (33, 'Pumpkin Carving Contest3', 'Plan33')
  INTO SpecialEvent (EventID, EventName, PlanName) VALUES (4, 'Thanksgiving Sale', 'Plan4')
SELECT * FROM dual;

-- Inserting into Tickets table
INSERT ALL
  INTO Tickets (TicketID, EventID, AdmissionType, VisitorType) VALUES (1, 1, 'Standard', 'Adult')
  INTO Tickets (TicketID, EventID, AdmissionType, VisitorType) VALUES (2, 211, 'Premium', 'Child')
  INTO Tickets (TicketID, EventID, AdmissionType, VisitorType) VALUES (3, 31, 'Standard', 'Senior')
  INTO Tickets (TicketID, EventID, AdmissionType, VisitorType) VALUES (4, 4, 'Standard', 'Adult')
  INTO Tickets (TicketID, EventID, AdmissionType, VisitorType) VALUES (5, 221, 'Premium', 'Adult')
SELECT * FROM dual;

-- Inserting into Activities table
INSERT ALL
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (1, 30, 5, 'Pumpkin carving activity', 'Carving Class', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (2, 60, 10, 'Pumpkin pie making activity', 'Cooking Class', 2)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (3, 45, 7, 'Hayrides around the patch', 'Hayride', 3)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (4, 120, 15, 'Halloween movie night', 'Movie Night', 4)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (5, 90, 12, 'Pumpkin painting activity', 'Painting Class', 5)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (6, 90, 20, 'Youth art class', 'Youth Art Class', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (7, 120, 50, 'Wine tasting event', 'Winery', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (8, 60, 10, 'Cultural dance showcase', 'Dance Fiesta', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (9, 150, 60, 'Cooking class for adults', 'Culinary Masters', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (10, 120, 25, 'Nighttime nature walk', 'Nature Walk', 2)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (11, 30, 8, 'Pumpkin seed roasting', 'Seed Roasting', 3)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (12, 180, 30, 'Fall photography class', 'Photography', 4)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (13, 75, 18, 'Pumpkin-themed story hour', 'Story Hour', 5)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (14, 50, 10, 'Scarecrow making workshop', 'Scarecrow Workshop', 2)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (15, 60, 15, 'Fall festival preparation', 'Festival Prep', 3)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (16, 45, 5, 'Pumpkin trivia contest', 'Trivia Contest', 1)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (17, 120, 22, 'Farm-to-table cooking demo', 'Cooking Demo', 4)
  INTO Activities (ActivityID, Duration, Fee, ActivityDescription, ActivityName, PatchID) VALUES (18, 30, 6, 'DIY pumpkin spice candles', 'Candle Making', 5)
SELECT * FROM dual;

-- Inserting into KidsActivities table
INSERT ALL
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (1, 1)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (3, 1)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (5, 1)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (6, 0)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (7, 2)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (8, 1)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (9, 0)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (13, 0)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (14, 0)
  INTO KidsActivities (ActivityID, GuardianRequirement) VALUES (18, 1)
SELECT * FROM dual;

-- Inserting into AdultActivities table
INSERT ALL
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (2, 21, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (4, 21, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (6, 0, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (7, 0, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (8, 0, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (9, 0, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (10, 18, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (11, 18, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (12, 20, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (14, 0, 0)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (15, 20, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (16, 22, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (17, 23, 1)
  INTO AdultActivities (ActivityID, AgeRequirement, AlcoholInvolvement) VALUES (18, 0, 0)
SELECT * FROM dual;

-- Enable constraints back
BEGIN
   EXECUTE IMMEDIATE 'ALTER SESSION SET CONSTRAINTS = ENABLED';
EXCEPTION
   WHEN OTHERS THEN
      DBMS_OUTPUT.put_line('Failed to enable constraints: ' || SQLERRM);
END;
/

COMMIT;
/

